# Lembar — Prototipe Sewa E-book

UTS Pemrograman Web Ganjil 2026/2027 · Informatika UMM · Kelas C/D/E

Aplikasi web tanpa framework: front-end HTML/CSS/JS + backend PHP 8.x OOP.
Pengguna bisa melihat katalog (e-book, komik, jurnal), mendaftar, menyewa, mengembalikan, dan admin mengelola katalog. Data disimpan di file JSON.

## Peta dokumentasi

| Dokumen | Isi | Untuk siapa |
|---|---|---|
| [README.md](README.md) | Cara menjalankan, struktur folder, ringkasan (file ini) | semua |
| [FLOW.md](FLOW.md) | Alur request, route, alur tiap fitur, alur data | semua, terutama yang baru membaca kode |
| [PEMBAGIAN_TUGAS.md](PEMBAGIAN_TUGAS.md) | Tugas 4 anggota, kontrak antar-anggota, jadwal | semua |
| [PEMETAAN_RUBRIK.md](PEMETAAN_RUBRIK.md) | Kode mana yang memenuhi tiap poin rubrik + bahan presentasi | semua, untuk persiapan demo |
| [GIT_WORKFLOW.md](GIT_WORKFLOW.md) | Cara kerja Git berempat, aturan commit | semua |
| [KONVENSI_KODE.md](KONVENSI_KODE.md) | Aturan penulisan kode agar seragam | semua |
| [PENGUJIAN.md](PENGUJIAN.md) | Skenario uji dengan hasil yang diharapkan | semua, terutama B |

## Cara menjalankan

Syarat: PHP 8.1 atau lebih baru.

```bash
cd ebook-rental
php -S localhost:8000 -t public
```

Buka http://localhost:8000

Akun admin otomatis dibuat saat pertama dijalankan:

- Email: `admin@perpus.test`
- Password: `admin123`

Jika memakai XAMPP, taruh folder di `htdocs` lalu buka `http://localhost/ebook-rental/public/`.

## Struktur folder

```
ebook-rental/
├── public/                  # satu-satunya folder yang diakses browser
│   ├── index.php            # front controller (pintu masuk, bukan HTML)
│   └── assets/{css,js}/     # style.css, app.js
├── src/                     # semua kode PHP (namespace App\)
│   ├── autoload.php         # PSR-4 autoloader
│   ├── Contracts/           # interface Rentable
│   ├── Models/              # Media (abstract) -> Ebook, Comic, Journal
│   │                        # User (abstract) -> Member -> PremiumMember, Admin
│   │                        # Transaction
│   ├── Factories/           # MediaFactory, UserFactory
│   ├── Storage/             # JsonStorage
│   ├── Repositories/        # Media/User/Transaction repository
│   ├── Services/            # AuthService, RentalService (aturan bisnis)
│   ├── Controllers/         # BaseController + Catalog/Auth/Rental/Admin
│   ├── Core/                # Application (router), View, Session
│   └── Exceptions/          # RentalException, ValidationException
├── views/                   # template HTML (berekstensi .php)
│   ├── layouts/main.php     # kerangka: head, header, nav, main, footer
│   └── catalog/ auth/ rental/ admin/ errors/
├── data/                    # media.json, users.json, transactions.json
└── docs/                    # dokumentasi ini
```

Catatan: HTML ada di `views/` (berekstensi `.php` karena menyisipkan data dari PHP). `public/index.php` hanya 7 baris PHP yang menjalankan `Application`; tidak ada HTML di dalamnya.

## Aturan bisnis

| Tipe media | Harga |
|---|---|
| E-book | harga × hari |
| Komik | harga × hari, diskon 15% jika ≥ 7 hari |
| Jurnal | flat, berapa pun lama sewa |

| Role | Maks. pinjaman aktif | Diskon |
|---|---|---|
| Member | 3 | 0% |
| Premium | 5 | 20% |
| Admin | tidak menyewa | - |

- Lama sewa 1–14 hari.
- Denda keterlambatan Rp 1.000 per hari (dibulatkan ke atas).
- Satu pengguna tidak bisa menyewa media yang sama dua kali selagi masih aktif.

Rumus harga di `public/assets/js/app.js` (estimasi di dialog) harus sama dengan `getRentalPrice()` di `src/Models/*.php`. Jika aturan harga diubah, ubah di kedua tempat.

## Status saat ini

Kerangka kode mencakup semua poin rubrik, tetapi **belum pernah dijalankan** oleh pembuatnya. Langkah pertama kelompok adalah menjalankan dan menguji; lihat [PENGUJIAN.md](PENGUJIAN.md).
