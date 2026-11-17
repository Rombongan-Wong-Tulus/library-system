'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initNavToggle();
    initToasts();
    initCatalogFilterAndSort();
    initRentDialog();
    initFormValidation();
    initConfirmForms();
    initAdminExtraLabel();
    initPasswordStrength();
    initReadPreviewModal();
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

/* ---------- Notifikasi Toast ---------- */
function showToast(type, message) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type === 'error' ? 'error' : 'success'}`;
    toast.setAttribute('role', 'status');

    const textSpan = document.createElement('span');
    textSpan.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-dismiss';
    closeBtn.innerHTML = '&times;';
    closeBtn.setAttribute('aria-label', 'Tutup');
    closeBtn.onclick = () => toast.remove();

    toast.appendChild(textSpan);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}

function initToasts() {
    // Ubah pesan flash server menjadi toast mengambang yang bersih
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(flash => {
        const isError = flash.classList.contains('flash-error');
        showToast(isError ? 'error' : 'success', flash.textContent.trim());
        flash.remove();
    });
}

/* ---------- Cari, saring & sortir katalog (sisi klien) ---------- */
function initCatalogFilterAndSort() {
    const search = document.getElementById('search');
    const type = document.getElementById('filter-type');
    const onlyAvail = document.getElementById('only-available');
    const sortSelect = document.getElementById('sort-select');
    const catalog = document.getElementById('catalog');
    if (!catalog) return;

    const cards = [...catalog.querySelectorAll('.card')];
    if (cards.length === 0) return;

    const count = document.getElementById('result-count');
    const empty = document.getElementById('empty');

    const sortCards = () => {
        if (!sortSelect) return;
        const val = sortSelect.value;
        const sorted = [...cards].sort((a, b) => {
            const titleA = a.querySelector('h2')?.textContent.trim() || '';
            const titleB = b.querySelector('h2')?.textContent.trim() || '';
            const priceA = parseFloat(a.querySelector('.js-rent')?.dataset.price || '0');
            const priceB = parseFloat(b.querySelector('.js-rent')?.dataset.price || '0');

            if (val === 'title_desc') return titleB.localeCompare(titleA);
            if (val === 'price_asc') return priceA - priceB;
            if (val === 'price_desc') return priceB - priceA;
            return titleA.localeCompare(titleB);
        });
        sorted.forEach(c => catalog.appendChild(c));
    };

    const apply = () => {
        const q = search ? search.value.trim().toLowerCase() : '';
        const t = type ? type.value : '';
        const availOnly = onlyAvail ? onlyAvail.checked : false;

        let shown = 0;
        cards.forEach(card => {
            const match =
                card.dataset.title.includes(q) &&
                (t === '' || card.dataset.type === t) &&
                (!availOnly || card.dataset.available === '1');
            card.hidden = !match;
            if (match) shown++;
        });

        if (count) count.textContent = `Menampilkan ${shown} dari ${cards.length} media`;
        if (empty) empty.hidden = shown !== 0;
    };

    if (search) search.addEventListener('input', apply);
    if (type) type.addEventListener('change', apply);
    if (onlyAvail) onlyAvail.addEventListener('change', apply);
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            sortCards();
            apply();
        });
    }

    apply();
}

/* ---------- Dialog sewa + estimasi harga + AJAX Fetch ---------- */
const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

const calculateRentalPrice = (scheme, price, days, threshold, discount) => {
    if (scheme === 'flat') return price;
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
    let activeCard = null;

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
            activeCard = btn.closest('.card');
            current = {
                id: btn.dataset.id,
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
    document.getElementById('rent-cancel')?.addEventListener('click', () => dialog.close());

    // Sewa via Fetch dengan Fallback form biasa
    form.addEventListener('submit', e => {
        if (!update()) {
            e.preventDefault();
            return;
        }

        // Jika browser mendukung fetch, kirim via fetch
        if (window.fetch) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(form);
            fetch('index.php?r=rent', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(res => res.json())
            .then(data => {
                dialog.close();
                if (data.success) {
                    showToast('success', data.message);
                    if (activeCard) {
                        const stockEl = activeCard.querySelector('.stock');
                        if (stockEl) {
                            if (data.remainingStock <= 0) {
                                stockEl.innerHTML = '<span class="badge badge-late">Stok habis</span>';
                                activeCard.dataset.available = '0';
                                const rentBtn = activeCard.querySelector('.js-rent');
                                if (rentBtn) rentBtn.remove();
                            } else {
                                stockEl.innerHTML = `<span class="badge badge-active">Tersedia (${data.remainingStock})</span>`;
                            }
                        }
                    }
                    setTimeout(() => {
                        window.location.href = 'index.php?r=rentals';
                    }, 1200);
                } else {
                    showToast('error', data.message || 'Gagal menyewa media.');
                }
            })
            .catch(() => {
                // Fallback: submit biasa jika fetch gagal
                form.submit();
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        }
    });
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
    type.addEventListener('change', () => { label.textContent = labels[type.value] || 'Info Tambahan'; });
}

/* ---------- Indikator Kekuatan Password ---------- */
function initPasswordStrength() {
    const pwInput = document.getElementById('register-password');
    const fill = document.getElementById('meter-fill');
    const text = document.getElementById('meter-text');
    if (!pwInput || !fill || !text) return;

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        if (!val) {
            fill.className = 'meter-fill';
            text.textContent = 'Kekuatan password';
            return;
        }

        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        fill.className = 'meter-fill';
        if (score <= 2) {
            fill.classList.add('weak');
            text.textContent = 'Lemah (tambahkan kombinasi huruf, angka, atau simbol)';
        } else if (score <= 4) {
            fill.classList.add('medium');
            text.textContent = 'Sedang (cukup aman)';
        } else {
            fill.classList.add('strong');
            text.textContent = 'Kuat (sangat baik)';
        }
    });
}

/* ---------- Modal Mock Baca Cuplikan ---------- */
function initReadPreviewModal() {
    const btn = document.getElementById('btn-read-preview');
    const dialog = document.getElementById('read-dialog');
    if (!btn || !dialog || typeof dialog.showModal !== 'function') return;

    btn.addEventListener('click', () => dialog.showModal());
    document.getElementById('btn-close-read')?.addEventListener('click', () => dialog.close());
    document.getElementById('btn-close-read-footer')?.addEventListener('click', () => dialog.close());
}
