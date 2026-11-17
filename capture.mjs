// capture.mjs — ambil 12 screenshot README untuk library-system.
//
// Pakai:   node capture.mjs <path-repo>          (default: folder saat ini)
// Syarat:  Node 18+, PHP di PATH, `npm i playwright` di folder skrip ini.
// Env opsional:
//   PHP_BIN      path php (default "php")
//   PORT         port server demo (default 8765)
//   CHROME_PATH  pakai Chrome/Edge terpasang, bukan Chromium bawaan Playwright
//   NO_EXTERNAL  "1" = blokir request ke luar (font CDN) — hanya untuk uji offline
//
// Repo disalin ke folder temp; data/*.json milikmu tidak disentuh.

import { chromium } from 'playwright';
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const repo = path.resolve(process.argv[2] ?? process.cwd());
const outDir = path.join(repo, 'docs', 'screenshots');
const PORT = process.env.PORT ?? '8765';
const PHP = process.env.PHP_BIN ?? 'php';
const base = `http://127.0.0.1:${PORT}`;

const DEMO = { name: 'Demo Member', email: 'demo@example.com', password: 'Demo12345' };
const ADMIN = { email: 'admin@perpus.test', password: 'admin123' };

if (!fs.existsSync(path.join(repo, 'router.php'))) {
  console.error(`router.php tidak ada di ${repo}. Berikan path root repo.`);
  process.exit(1);
}

// ---------- 1. salin repo ke temp (tanpa .git dan data runtime) ----------
const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ls-shot-'));
fs.cpSync(repo, tmp, {
  recursive: true,
  filter: (src) => {
    const rel = path.relative(repo, src).split(path.sep).join('/');
    if (rel === '.git' || rel.startsWith('.git/')) return false;
    if (rel.startsWith('node_modules')) return false;
    if (rel.startsWith('docs/screenshots')) return false;
    if (rel.startsWith('data/') && rel !== 'data/media.seed.json' && rel !== 'data/.htaccess') return false;
    return true;
  },
});

// ---------- 2. jalankan server PHP dari salinan ----------
const server = spawn(PHP, ['-S', `127.0.0.1:${PORT}`, 'router.php'], { cwd: tmp, stdio: 'ignore' });
server.on('error', (e) => {
  console.error(`Gagal menjalankan PHP (${PHP}): ${e.message}`);
  process.exit(1);
});

let browser;
const cleanup = () => {
  try { server.kill(); } catch {}
  try { fs.rmSync(tmp, { recursive: true, force: true }); } catch {}
};
process.on('exit', cleanup);
process.on('SIGINT', () => process.exit(1));

async function waitServer() {
  for (let i = 0; i < 50; i++) {
    try {
      const r = await fetch(`${base}/?r=login`);
      if (r.ok) return;
    } catch {}
    await new Promise((r) => setTimeout(r, 200));
  }
  throw new Error('Server PHP tidak merespons.');
}

// ---------- 3. helper ----------
async function newContext(opts = {}) {
  const ctx = await browser.newContext({
    viewport: { width: 1280, height: 800 },
    deviceScaleFactor: 1,
    colorScheme: 'light',
    locale: 'id-ID',
    ...opts,
  });
  if (process.env.NO_EXTERNAL === '1') {
    await ctx.route(/^https?:\/\/(?!127\.0\.0\.1)/, (route) => route.abort());
  }
  return ctx;
}

async function go(page, query) {
  const resp = await page.goto(`${base}/${query}`, { waitUntil: 'domcontentloaded' });
  if (!resp || resp.status() !== 200) {
    throw new Error(`${query} -> HTTP ${resp?.status()}`);
  }
}

async function settle(page) {
  await page.waitForLoadState('networkidle', { timeout: 6000 }).catch(() => {});
  await page.evaluate(() => document.fonts?.ready).catch(() => {});
  await page.waitForTimeout(300);
}

async function guard(page, name) {
  const bad = await page.locator('text=/Token CSRF|Fatal error|Warning:|Parse error|Stack trace/').count();
  if (bad > 0) throw new Error(`${name}: halaman berisi pesan error`);
}

const results = [];
async function shot(page, name, { fullPage = false, maxHeight } = {}) {
  await settle(page);
  await guard(page, name);
  const file = path.join(outDir, name);
  const opts = { path: file, type: 'png' };
  if (fullPage) {
    opts.fullPage = true;
    if (maxHeight) {
      const vp = page.viewportSize();
      const h = await page.evaluate(() => document.documentElement.scrollHeight);
      opts.clip = { x: 0, y: 0, width: vp.width, height: Math.min(h, maxHeight) };
    }
  }
  await page.screenshot(opts);
  const size = fs.statSync(file).size;
  if (size < 3000) throw new Error(`${name}: file terlalu kecil (${size} B)`);
  results.push([name, size]);
  console.log(`  ok  ${name}  (${Math.round(size / 1024)} KB)`);
}

async function login(page, { email, password }) {
  await go(page, '?r=login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL((u) => u.searchParams.get('r') !== 'login', { timeout: 10000 }),
    page.click('form[action$="r=login"] button[type="submit"]'),
  ]);
}

async function openRentDialog(page, index = 0) {
  await go(page, '?r=catalog');
  await page.locator('.js-rent').nth(index).click();
  await page.locator('#rent-dialog[open]').waitFor({ timeout: 5000 });
}

async function submitRent(page) {
  const done = page.waitForResponse(
    (r) => r.url().includes('r=rent') && r.request().method() === 'POST',
    { timeout: 10000 },
  );
  await page.click('#rent-form button[type="submit"]');
  const resp = await done;
  if (resp.status() >= 400) throw new Error(`POST rent -> HTTP ${resp.status()}`);
  await page.waitForTimeout(800);
}

// ---------- 4. alur utama ----------
try {
  await waitServer();
  fs.mkdirSync(outDir, { recursive: true });

  const launch = { headless: true };
  if (process.env.CHROME_PATH) launch.executablePath = process.env.CHROME_PATH;
  if (typeof process.getuid === 'function' && process.getuid() === 0) launch.args = ['--no-sandbox'];
  try {
    browser = await chromium.launch(launch);
  } catch (e) {
    console.log('Chromium bawaan tidak ada, mencoba Chrome terpasang...');
    browser = await chromium.launch({ ...launch, channel: 'chrome' });
  }

  console.log('Desktop — pengunjung & member');
  const ctx = await newContext();
  const page = await ctx.newPage();

  await go(page, '?r=login');
  await shot(page, '06-login.png');

  await go(page, '?r=register');
  await page.fill('input[name="name"]', DEMO.name);
  await page.fill('input[name="email"]', DEMO.email);
  await page.fill('#register-password', DEMO.password);
  await page.fill('input[name="confirm"]', DEMO.password);
  await shot(page, '05-register.png');
  await Promise.all([
    page.waitForURL((u) => u.searchParams.get('r') === 'login', { timeout: 10000 }),
    page.click('#register-form button[type="submit"]'),
  ]);

  await login(page, DEMO);
  await go(page, '?r=catalog');
  await shot(page, '01-katalog.png', { fullPage: true, maxHeight: 1200 });

  await page.emulateMedia({ colorScheme: 'dark' });
  await page.waitForTimeout(300);
  const lum = await page.evaluate(() => {
    const m = getComputedStyle(document.body).backgroundColor.match(/\d+(\.\d+)?/g).map(Number);
    return (0.2126 * m[0] + 0.7152 * m[1] + 0.0722 * m[2]) / 255;
  });
  if (lum > 0.35) throw new Error(`02-katalog-dark: latar belum gelap (luminance ${lum.toFixed(2)})`);
  await shot(page, '02-katalog-dark.png', { fullPage: true, maxHeight: 1200 });
  await page.emulateMedia({ colorScheme: 'light' });

  await go(page, '?r=media&id=M0001');
  await shot(page, '03-detail-media.png');

  await openRentDialog(page, 0);
  await page.fill('#rent-days', '7');
  await page.waitForFunction(() => {
    const t = document.getElementById('rent-total')?.textContent ?? '';
    return t !== '' && t !== 'Rp 0' && t !== '-';
  }, null, { timeout: 5000 });
  await shot(page, '04-dialog-sewa.png');
  await submitRent(page);

  await openRentDialog(page, 1);
  await submitRent(page);

  await go(page, '?r=rentals');
  const rows = await page.locator('table tbody tr').count();
  if (rows < 1) throw new Error('07-sewa-saya: tabel sewa kosong');
  await shot(page, '07-sewa-saya.png', { fullPage: true });

  await go(page, '?r=profile');
  await shot(page, '08-profil.png', { fullPage: true });
  await ctx.close();

  console.log('Mobile');
  const mctx = await newContext({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
  });
  const mpage = await mctx.newPage();
  await login(mpage, DEMO);
  await go(mpage, '?r=catalog');
  await shot(mpage, '12-mobile-katalog.png', { fullPage: true, maxHeight: 1400 });
  await mctx.close();

  console.log('Desktop — admin');
  const actx = await newContext();
  const apage = await actx.newPage();
  await login(apage, ADMIN);
  await go(apage, '?r=admin');
  await shot(apage, '09-admin-dashboard.png', { fullPage: true, maxHeight: 1500 });
  await go(apage, '?r=admin-edit&id=M0001');
  await shot(apage, '10-admin-edit.png', { fullPage: true });
  await go(apage, '?r=admin-members');
  await shot(apage, '11-admin-member.png', { fullPage: true, maxHeight: 1200 });
  await actx.close();

  console.log(`\nSelesai: ${results.length}/12 file di ${outDir}`);
  if (results.length !== 12) process.exitCode = 1;
} catch (e) {
  console.error(`\nGAGAL: ${e.message}`);
  process.exitCode = 1;
} finally {
  await browser?.close().catch(() => {});
  cleanup();
}
