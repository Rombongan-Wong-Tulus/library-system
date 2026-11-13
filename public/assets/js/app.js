'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initNavToggle();
    initCatalogFilter();
    initRentDialog();
    initFormValidation();
    initConfirmForms();
    initAdminExtraLabel();
});

/* ---------- Navigasi mobile ---------- */
function initNavToggle() {
    const btn = document.querySelector('.nav-toggle');
    const menu = document.getElementById('menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', () => {
        const open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(open));
    });
}

/* ---------- Cari & saring katalog (tanpa reload) ---------- */
function initCatalogFilter() {
    const search = document.getElementById('search');
    const type = document.getElementById('filter-type');
    const onlyAvail = document.getElementById('only-available');
    const cards = [...document.querySelectorAll('#catalog .card')];
    if (!search || cards.length === 0) return;

    const count = document.getElementById('result-count');
    const empty = document.getElementById('empty');

    const apply = () => {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        cards.forEach(card => {
            const match =
                card.dataset.title.includes(q) &&
                (type.value === '' || card.dataset.type === type.value) &&
                (!onlyAvail.checked || card.dataset.available === '1');
            card.hidden = !match;
            if (match) shown++;
        });
        count.textContent = `${shown} dari ${cards.length} media`;
        empty.hidden = shown !== 0;
    };

    [search, type, onlyAvail].forEach(el => el.addEventListener('input', apply));
    apply();
}

/* ---------- Dialog sewa + estimasi harga (menggunakan aturan data-* dari server) ---------- */
const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

const calculateRentalPrice = (scheme, price, days, threshold, discount) => {
    if (scheme === 'flat') {
        return price;
    }
    let total = price * days;
    if (scheme === 'daily_discount' && days >= threshold) {
        total *= (1 - discount);
    }
    return total;
};

function initRentDialog() {
    const dialog = document.getElementById('rent-dialog');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const form = document.getElementById('rent-form');
    const days = document.getElementById('rent-days');
    const total = document.getElementById('rent-total');
    const error = document.getElementById('rent-error');
    const discount = parseFloat(dialog.dataset.discount || '0');
    let current = null;

    const update = () => {
        const d = parseInt(days.value, 10);
        const valid = Number.isInteger(d) && d >= 1 && d <= 14;
        error.hidden = valid;
        days.classList.toggle('invalid', !valid);
        if (valid && current) {
            const base = calculateRentalPrice(current.scheme, current.price, d, current.threshold, current.discount);
            total.textContent = rupiah(base * (1 - discount));
        } else {
            total.textContent = '-';
        }
        return valid;
    };

    document.querySelectorAll('.js-rent').forEach(btn => {
        btn.addEventListener('click', () => {
            current = {
                price: parseFloat(btn.dataset.price || '0'),
                scheme: btn.dataset.scheme || 'daily',
                threshold: parseInt(btn.dataset.threshold || '0', 10),
                discount: parseFloat(btn.dataset.discount || '0'),
            };
            document.getElementById('rent-media-id').value = btn.dataset.id;
            document.getElementById('rent-title').textContent = btn.dataset.title;
            days.value = 3;
            update();
            dialog.showModal();
            days.focus();
        });
    });

    days.addEventListener('input', update);
    document.getElementById('rent-cancel').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', e => { if (!update()) e.preventDefault(); });
}

/* ---------- Validasi form sisi klien ---------- */
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        const fields = [...form.querySelectorAll('input, select, textarea')]
            .filter(el => el.type !== 'hidden');

        const check = el => {
            const msg = el.closest('.field')?.querySelector('.error');
            let text = '';
            const v = el.value.trim();

            if (el.required && v === '') text = 'Kolom ini wajib diisi.';
            else if (el.type === 'email' && v !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) text = 'Format email tidak valid.';
            else if (el.minLength > 0 && v.length > 0 && v.length < el.minLength) text = `Minimal ${el.minLength} karakter.`;
            else if (el.type === 'number' && v !== '' && el.min !== '' && Number(v) < Number(el.min)) text = `Nilai minimal ${el.min}.`;
            else if (el.name === 'confirm') {
                const pw = form.querySelector('[name="password"]');
                if (pw && v !== pw.value) text = 'Konfirmasi password tidak sama.';
            }

            el.classList.toggle('invalid', text !== '');
            if (msg) { msg.textContent = text; msg.hidden = text === ''; }
            return text === '';
        };

        fields.forEach(el => {
            el.addEventListener('blur', () => check(el));
            el.addEventListener('input', () => { if (el.classList.contains('invalid')) check(el); });
        });

        form.addEventListener('submit', e => {
            const results = fields.map(check);
            if (results.includes(false)) {
                e.preventDefault();
                fields[results.indexOf(false)].focus();
            }
        });
    });
}

/* ---------- Konfirmasi sebelum aksi penting ---------- */
function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
}

/* ---------- Label "extra" menyesuaikan jenis media (admin) ---------- */
function initAdminExtraLabel() {
    const type = document.getElementById('admin-type');
    const label = document.getElementById('extra-label');
    if (!type || !label) return;
    const labels = { ebook: 'Ukuran file (MB)', comic: 'Nomor volume', journal: 'Penerbit' };
    type.addEventListener('change', () => { label.textContent = labels[type.value]; });
}
