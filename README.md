# Lembar — Prototipe Sewa E-book (UTS Pemrograman Web 2026/2027)

Aplikasi web tanpa framework: front-end HTML/CSS/JS + backend PHP 8.x OOP.
Penyimpanan data memakai file JSON di folder `data/`.

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

(Jika memakai XAMPP, taruh folder di `htdocs` lalu buka `http://localhost/ebook-rental/public/`.)

## Struktur folder

```
ebook-rental/
├── public/                  # satu-satunya folder yang diakses browser
│   ├── index.php            # front controller
│   └── assets/{css,js}/     # style.css, app.js
├── src/                     # semua kode PHP (namespace App\)
│   ├── autoload.php         # PSR-4 autoloader
│   ├── Contracts/Rentable.php          # interface
│   ├── Models/              # Media (abstract) -> Ebook, Comic, Journal
│   │                        # User (abstract) -> Member -> PremiumMember, Admin
│   │                        # Transaction
│   ├── Factories/           # MediaFactory, UserFactory
│   ├── Storage/JsonStorage.php         # baca/tulis JSON
│   ├── Repositories/        # Media/User/Transaction repository
│   ├── Services/            # AuthService, RentalService (aturan bisnis)
│   ├── Controllers/         # BaseController + Catalog/Auth/Rental/Admin
│   ├── Core/                # Application (router), View, Session
│   └── Exceptions/          # RentalException, ValidationException
├── views/                   # template HTML (layouts, catalog, auth, rental, admin)
├── data/                    # media.json, users.json, transactions.json
└── docs/
```

## Pemetaan ke rubrik

| Rubrik | Di mana |
|---|---|
| Class & Object | semua folder `src/` |
| Encapsulation | properti `protected/private` + getter (`Media`, `User`, `Transaction`) |
| Inheritance | `Media` → `Ebook/Comic/Journal`; `User` → `Member` → `PremiumMember`; `User` → `Admin` |
| Polymorphism | `getRentalPrice()` (harga beda per tipe), `getMaxLoans()` & `getDiscount()` (beda per role) |
| Interface / abstract | `Rentable`, `Media`, `User` |
| Namespace | `App\...` sesuai struktur folder (PSR-4) |
| Form validasi | `views/auth/*`, `views/admin/index.php` + `public/assets/js/app.js` (klien) + `AuthService` (server) |
| Semantik & responsif | `header/nav/main/footer`, `article`, `table` dengan `scope`, CSS grid + media query |
| Interaktivitas JS | cari/filter katalog, dialog sewa dengan estimasi harga, konfirmasi aksi, menu mobile |

## Aturan bisnis

| Tipe | Harga |
|---|---|
| E-book | harga × hari |
| Komik | harga × hari, diskon 15% jika ≥ 7 hari |
| Jurnal | flat, berapa pun lama sewa |

| Role | Maks. pinjaman | Diskon |
|---|---|---|
| Member | 3 | 0% |
| Premium | 5 | 20% |
| Admin | tidak menyewa | - |

Lama sewa 1–14 hari. Denda keterlambatan Rp 1.000 per hari.

## Pembagian tugas (kelompok 4 orang)

| Anggota | Fokus | Berkas utama |
|---|---|---|
| A — Backend model | OOP inti | `src/Contracts`, `src/Models`, `src/Factories` |
| B — Backend logika | Penyimpanan & aturan bisnis | `src/Storage`, `src/Repositories`, `src/Services`, `src/Exceptions` |
| C — Controller & routing | Alur halaman | `src/Controllers`, `src/Core`, `public/index.php` |
| D — Front-end | Tampilan & JS | `views/`, `public/assets/` |

Kelompok 3 orang: gabungkan A dan B.

## Alur kerja Git yang disarankan

1. Satu orang membuat repo dan push struktur ini ke branch `main`.
2. Tiap anggota membuat branch `fitur/nama-fitur`, lalu pull request ke `main`.
3. Jangan edit file yang sama bersamaan; ikuti tabel pembagian tugas.
4. `data/*.json` jangan di-commit setelah dipakai uji coba (sudah ada di `.gitignore`).

## Ide pengembangan (nilai tambah)

- Pencarian di sisi server dan paginasi.
- Trait `Timestampable` untuk `created_at`.
- Unit test sederhana untuk `getRentalPrice()`.
- Halaman detail media dan tombol "baca" (mock).
