# Konvensi Kode

Dokumen terkait: [README](README.md) · [Git workflow](GIT_WORKFLOW.md) · [Pemetaan rubrik](PEMETAAN_RUBRIK.md)

Agar kode empat orang terlihat seperti satu tangan, ikuti aturan ini. Berlaku juga sebagai nilai di rubrik "kerapian".

## PHP

| Aturan | Contoh |
|---|---|
| Setiap berkas dimulai `<?php` lalu `declare(strict_types=1);` | — |
| Satu kelas satu berkas; nama berkas sama dengan nama kelas | `Ebook` → `Ebook.php` |
| Namespace sesuai folder di bawah `src/` | `src/Models/Ebook.php` → `namespace App\Models;` |
| Kelas: `PascalCase`; method dan variabel: `camelCase`; konstanta: `UPPER_SNAKE` | `RentalService`, `getMaxLoans()`, `LATE_FEE_PER_DAY` |
| Beri tipe pada parameter dan nilai kembali | `public function rent(User $u, string $id, int $days): Transaction` |
| Properti `private` atau `protected`; akses lewat getter | jangan `public $stock` |
| Aturan bisnis di `Services/`, bukan di controller atau view | — |
| Baca/tulis data hanya lewat `Repositories/` | — |
| Pelanggaran aturan: lempar exception khusus | `throw new RentalException('...')` |
| Pesan untuk pengguna dalam bahasa Indonesia, jelas, dan memberi tahu cara memperbaiki | "Lama sewa harus 1-14 hari." |
| Indentasi 4 spasi; satu `use` per kelas, urut abjad | — |
| Jangan `echo`/`var_dump` sisa debugging di kode yang di-commit | — |

## View (berkas di `views/`)

- Tidak ada logika bisnis di view. Hanya `if`, `foreach`, dan menampilkan data.
- **Semua** nilai dinamis lewat `$this->e(...)` (anti-XSS).
- Setiap form POST memuat `<?= $this->csrf() ?>`.
- Format uang dengan `$this->rupiah(...)`.
- Variabel yang tersedia: `$user`, `$flash`, dan data dari controller.

## HTML

- Pakai elemen semantik (`header`, `nav`, `main`, `section`, `article`, `footer`) sebelum `div`.
- Setiap `input` punya `label`.
- Tabel punya `th scope` dan `caption` (boleh `sr-only`).
- Tombol aksi memakai `button`, bukan `a`, jika mengubah data.

## CSS

- Warna dan ukuran dasar lewat variabel di `:root`.
- Mulai dari tampilan HP, perlebar dengan `@media` bila perlu.
- Jangan hapus `:focus-visible`.
- Nama kelas dalam bahasa Inggris, huruf kecil, kebab-case: `.card`, `.btn-small`.

## JavaScript

- `'use strict';` di awal berkas.
- Satu fungsi `initXxx` per fitur, dipanggil dari `DOMContentLoaded`.
- Pakai `const`/`let`, bukan `var`.
- Jangan percaya validasi klien: setiap aturan juga harus dicek di PHP.
- Rumus harga di JS harus sama dengan `getRentalPrice()` di PHP.

## Data JSON

- Nama field persis seperti di [PEMBAGIAN_TUGAS.md](PEMBAGIAN_TUGAS.md) bagian 3: `id, type, title, author, basePrice, stock, description, extra`.
- Jika menambah field baru, beri tahu A dan B.

## Komentar

- Komentar menjelaskan **kenapa**, bukan mengulang apa yang sudah jelas dari kode.
- Setiap kelas punya satu baris docblock tentang tanggung jawabnya.
