# Pengujian

Dokumen terkait: [README](README.md) · [FLOW](FLOW.md) · [Pembagian tugas](PEMBAGIAN_TUGAS.md) · [Pemetaan rubrik](PEMETAAN_RUBRIK.md)

Kerangka kode belum pernah dijalankan oleh pembuatnya, jadi pengujian ini adalah langkah pertama kelompok. Kerjakan berurutan; bagian 1 harus lulus sebelum yang lain.

## Persiapan

1. Jalankan `php -S localhost:8000 -t public` dari folder `ebook-rental`.
2. Aktifkan tampilan error PHP saat uji. Tambahkan di baris awal `public/index.php` (hapus lagi sebelum dikumpulkan jika tidak diperlukan):
   ```php
   ini_set('display_errors', '1');
   error_reporting(E_ALL);
   ```
3. Untuk mengulang dari nol, hentikan server lalu hapus `data/users.json` dan `data/transactions.json`, dan kembalikan `data/media.json` dari repo. Admin dibuat ulang otomatis.
4. Siapkan akun uji: satu **Member**, satu **Premium**, dan admin (`admin@perpus.test` / `admin123`).

Data awal katalog:

| ID | Judul | Jenis | Harga dasar | Stok |
|---|---|---|---|---|
| M0001 | Laskar Pelangi | E-book | 2.000 | 3 |
| M0002 | Pemrograman PHP Modern | E-book | 3.000 | 5 |
| M0003 | Dasar Basis Data | E-book | 2.500 | 2 |
| M0004 | Si Juki Anak Kampus | Komik | 1.500 | 4 |
| M0005 | Garudayana Saga | Komik | 1.800 | 0 |
| M0006 | Jurnal Teknologi Informasi Vol. 12 | Jurnal | 5.000 | 3 |

## 1. Tes asap

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 1.1 | Buka `/` | Katalog tampil dengan 6 kartu, tanpa error/warning PHP | [ ] |
| 1.2 | Buka `index.php?r=login` dan `?r=register` | Form tampil | [ ] |
| 1.3 | Buka `index.php?r=tidak-ada` | Halaman "Halaman tidak ditemukan" (kode 404) | [ ] |
| 1.4 | Cek folder `data/` setelah langkah 1.1 | `users.json` terisi satu akun admin | [ ] |
| 1.5 | Lihat isi `users.json` | Password berupa hash (`$2y$...`), bukan teks asli | [ ] |

## 2. Akun

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 2.1 | Daftar dengan data valid (Member) | Pesan sukses, diarahkan ke login | [ ] |
| 2.2 | Daftar dengan email yang sama | "Email sudah terdaftar." | [ ] |
| 2.3 | Daftar dengan password 5 karakter | Ditolak (pesan di form; di server: "Password minimal 6 karakter.") | [ ] |
| 2.4 | Daftar dengan konfirmasi password berbeda | "Konfirmasi password tidak sama." | [ ] |
| 2.5 | Daftar dengan nama 2 karakter | "Nama minimal 3 karakter." | [ ] |
| 2.6 | Daftar dengan email `abc` | "Format email tidak valid." | [ ] |
| 2.7 | Login dengan password salah | "Email atau password salah." | [ ] |
| 2.8 | Login benar sebagai Member | Masuk ke katalog; navigasi menampilkan "Sewaan saya" dan "Keluar (nama)" | [ ] |
| 2.9 | Login sebagai admin | Masuk ke halaman Kelola | [ ] |
| 2.10 | Klik Keluar | Kembali ke katalog sebagai tamu | [ ] |

Tip: untuk menguji validasi server saat JS menghalangi, nonaktifkan JavaScript di browser atau kirim form dengan `curl` (lihat bagian 8).

## 3. Harga sewa (polimorfisme)

Login sebagai **Member**, sewa lalu lihat pesan "Total ..." dan halaman Sewaan saya. Bandingkan juga dengan estimasi di dialog.

| # | Media | Hari | Harga yang diharapkan (Member) | Lulus |
|---|---|---|---|---|
| 3.1 | Laskar Pelangi (e-book, 2.000) | 3 | Rp 6.000 | [ ] |
| 3.2 | Si Juki (komik, 1.500) | 3 | Rp 4.500 | [ ] |
| 3.3 | Si Juki (komik, 1.500) | 7 | Rp 8.925 (diskon 15%: 10.500 × 0,85) | [ ] |
| 3.4 | Jurnal Teknologi (5.000) | 1 | Rp 5.000 | [ ] |
| 3.5 | Jurnal Teknologi (5.000) | 14 | Rp 5.000 (flat) | [ ] |

Login sebagai **Premium** (diskon 20%):

| # | Media | Hari | Harga yang diharapkan | Lulus |
|---|---|---|---|---|
| 3.6 | Pemrograman PHP Modern (3.000) | 2 | Rp 4.800 (6.000 × 0,8) | [ ] |
| 3.7 | Si Juki (1.500) | 7 | Rp 7.140 (8.925 × 0,8) | [ ] |
| 3.8 | Jurnal Teknologi (5.000) | 5 | Rp 4.000 | [ ] |

Estimasi di dialog (JS) dan total dari server harus **sama** untuk semua baris di atas. Jika berbeda, rumus di `app.js` tidak sinkron dengan `getRentalPrice()`.

## 4. Aturan sewa

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 4.1 | Member menyewa 3 media berbeda, lalu mencoba yang ke-4 | "Batas pinjaman aktif (3) sudah tercapai." | [ ] |
| 4.2 | Premium menyewa 5 media berbeda | Semua berhasil; yang ke-6 ditolak dengan batas 5 (tambah media lewat admin jika perlu) | [ ] |
| 4.3 | Menyewa media yang sama dua kali selagi aktif | "Kamu masih menyewa media ini." | [ ] |
| 4.4 | Garudayana Saga (stok 0) | Tidak ada tombol Sewa, tertulis "Stok habis" | [ ] |
| 4.5 | Dasar Basis Data (stok 2): dua akun menyewa, akun ketiga mencoba | Akun ketiga: tombol hilang setelah stok 0, atau "Stok ... sedang habis." jika dikirim langsung | [ ] |
| 4.6 | Setelah menyewa | Stok di katalog berkurang 1 | [ ] |
| 4.7 | Dialog sewa: isi 0, 15, kosong | Pesan "Isi 1 sampai 14 hari."; form tidak terkirim | [ ] |
| 4.8 | Kirim `days=0` langsung ke server (bagian 8) | "Lama sewa harus 1-14 hari." | [ ] |
| 4.9 | Admin membuka katalog | Tidak ada tombol Sewa | [ ] |
| 4.10 | Kirim `POST rent` sebagai admin (bagian 8) | "Admin tidak dapat menyewa media." | [ ] |

## 5. Pengembalian dan denda

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 5.1 | Klik Kembalikan, setujui konfirmasi | "Media dikembalikan."; status jadi Dikembalikan; stok di katalog bertambah 1 | [ ] |
| 5.2 | Klik Kembalikan, batalkan konfirmasi | Tidak terjadi apa-apa | [ ] |
| 5.3 | Ubah `dueAt` satu transaksi aktif di `data/transactions.json` menjadi 36 jam yang lalu, muat ulang Sewaan saya | Status "Terlambat" | [ ] |
| 5.4 | Kembalikan transaksi itu | "Denda keterlambatan Rp 2.000." (1,5 hari dibulatkan ke 2 hari × 1.000); denda tampil di baris | [ ] |
| 5.5 | Kembalikan transaksi yang sama lagi (kirim ulang form) | "Media ini sudah dikembalikan." | [ ] |
| 5.6 | Setelah mengembalikan, sewa lagi media itu | Berhasil (tidak lagi dianggap duplikat) | [ ] |
| 5.7 | Member A mengirim `trx_id` milik Member B (bagian 8) | "Transaksi tidak ditemukan." | [ ] |

## 6. Hak akses dan keamanan

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 6.1 | Tamu membuka `?r=rentals` | Diarahkan ke login, "Silakan login terlebih dahulu." | [ ] |
| 6.2 | Tamu membuka `?r=admin` | Diarahkan ke login | [ ] |
| 6.3 | Member membuka `?r=admin` | Diarahkan ke katalog, "Halaman ini khusus admin." | [ ] |
| 6.4 | Member mengirim `POST admin-add` / `admin-delete` | Ditolak (diarahkan ke katalog), data tidak berubah | [ ] |
| 6.5 | Kirim POST tanpa token CSRF | Pesan "Token CSRF tidak valid..." (kode 419) | [ ] |
| 6.6 | Admin menambah media berjudul `<script>alert(1)</script>` | Tampil sebagai teks biasa di katalog, tidak ada popup | [ ] |
| 6.7 | Buka `http://localhost:8000/../data/users.json` atau langsung ke `data/` | Tidak bisa diakses lewat web (karena hanya `public/` yang menjadi root) | [ ] |
| 6.8 | Login berhasil lalu cek cookie sesi | ID sesi berganti setelah login | [ ] |

## 7. Admin

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 7.1 | Tambah e-book valid | Masuk ke katalog dan `media.json` | [ ] |
| 7.2 | Tambah komik (kolom "Nomor volume") | Tampil sebagai "Volume N" | [ ] |
| 7.3 | Tambah jurnal (kolom "Penerbit") | Tampil dengan nama penerbit | [ ] |
| 7.4 | Ganti jenis di form | Label kolom tambahan berubah | [ ] |
| 7.5 | Tambah media dengan harga 0 | Ditolak ("Judul, penulis, harga (> 0), dan stok wajib diisi dengan benar.") | [ ] |
| 7.6 | Hapus media (setujui konfirmasi) | Hilang dari katalog | [ ] |
| 7.7 | Lihat daftar transaksi | Semua transaksi tampil dengan nama pengguna, judul, status | [ ] |

## 8. Menguji server tanpa browser (curl)

Berguna untuk melewati validasi JS. Token CSRF dan sesi dibutuhkan, jadi ambil dulu halaman login untuk mendapatkan cookie dan token.

```bash
# simpan cookie, ambil token dari form login
curl -s -c cj.txt 'http://localhost:8000/index.php?r=login' | grep -o 'name="_csrf" value="[^"]*"'

# login (ganti TOKEN dan akun)
curl -s -b cj.txt -c cj.txt -X POST 'http://localhost:8000/index.php?r=login' \
  -d '_csrf=TOKEN' -d 'email=member@contoh.id' -d 'password=rahasia1' -L | grep -i 'flash'

# sewa dengan hari tidak valid
curl -s -b cj.txt -c cj.txt -X POST 'http://localhost:8000/index.php?r=rent' \
  -d '_csrf=TOKEN' -d 'media_id=M0001' -d 'days=0' -L | grep -i 'flash'

# tanpa token CSRF -> harus 419
curl -s -o /dev/null -w '%{http_code}\n' -X POST 'http://localhost:8000/index.php?r=login' -d 'email=a&password=b'
```

Token CSRF bisa berganti setelah login (sesi baru); ambil ulang dari halaman katalog jika perlu.

## 9. Tampilan dan JavaScript

| # | Langkah | Hasil yang diharapkan | Lulus |
|---|---|---|---|
| 9.1 | Ketik di kolom cari ("laskar") | Hanya kartu cocok yang tampil; jumlah hasil ikut berubah | [ ] |
| 9.2 | Filter Jenis = Komik | Hanya komik tampil | [ ] |
| 9.3 | Centang "Hanya yang tersedia" | Garudayana Saga hilang | [ ] |
| 9.4 | Cari kata yang tidak ada | Pesan "Tidak ada media yang cocok..." | [ ] |
| 9.5 | Lebar layar ±375 px (DevTools mode HP) | Satu kolom, tidak ada scroll horizontal di halaman, menu jadi tombol "Menu" yang bisa dibuka | [ ] |
| 9.6 | Tabel Sewaan/Admin di layar sempit | Tabel bisa digeser horizontal di dalam kotaknya | [ ] |
| 9.7 | Navigasi hanya dengan keyboard (Tab, Enter, Esc) | Semua tombol/link terjangkau, fokus terlihat, Esc menutup dialog sewa | [ ] |
| 9.8 | Isi form daftar salah lalu pindah kolom | Pesan error muncul per kolom, kolom bergaris merah | [ ] |
| 9.9 | Setelah server menolak form (mis. email sama) | Nama dan email yang tadi diisi tetap ada di form | [ ] |

## 10. Skrip tes harga

Simpan sebagai `tests/PriceTest.php` di folder `ebook-rental` (sejajar dengan `src/`), lalu jalankan `php tests/PriceTest.php`.

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Models\Comic;
use App\Models\Ebook;
use App\Models\Journal;
use App\Models\Member;
use App\Models\PremiumMember;

$fail = 0;

function check(string $name, float|int $actual, float|int $expected): void
{
    global $fail;
    $ok = abs($actual - $expected) < 0.01;
    echo ($ok ? '[OK]    ' : '[GAGAL] ') . "$name (hasil $actual, harapan $expected)\n";
    if (!$ok) {
        $fail++;
    }
}

$ebook = new Ebook('t1', 'Tes', 'X', 2000.0, 1);
check('Ebook 3 hari', $ebook->getRentalPrice(3), 6000);

$comic = new Comic('t2', 'Tes', 'X', 1500.0, 1);
check('Komik 6 hari (tanpa diskon)', $comic->getRentalPrice(6), 9000);
check('Komik 7 hari (diskon 15%)', $comic->getRentalPrice(7), 8925);

$journal = new Journal('t3', 'Tes', 'X', 5000.0, 1);
check('Jurnal 1 hari', $journal->getRentalPrice(1), 5000);
check('Jurnal 14 hari', $journal->getRentalPrice(14), 5000);

$member = new Member('u1', 'A', 'a@a.id', 'x');
$premium = new PremiumMember('u2', 'B', 'b@b.id', 'x');
check('Member maks pinjam', $member->getMaxLoans(), 3);
check('Member diskon', $member->getDiscount(), 0);
check('Premium maks pinjam', $premium->getMaxLoans(), 5);
check('Premium diskon', $premium->getDiscount(), 0.2);

echo $fail === 0 ? "\nSemua tes lulus.\n" : "\n$fail tes gagal.\n";
exit($fail === 0 ? 0 : 1);
```

Tambahkan baris tes untuk jenis media baru yang dibuat anggota A.

## 11. Format laporan bug

Tulis di grup agar pemilik berkas bisa memperbaiki cepat:

```
Halaman / langkah : (mis. Sewa Si Juki 7 hari sebagai Premium)
Yang terjadi      : (salin pesan error persis)
Yang diharapkan   : (mis. Rp 7.140)
Berkas dicurigai  : (mis. src/Services/RentalService.php)
Tangkapan layar   : (jika ada)
```

## 12. Ringkasan lulus

Aplikasi siap dikumpulkan bila:

- [ ] Bagian 1 sampai 7 dan 9 lulus semua
- [ ] Skrip tes harga lulus
- [ ] Tidak ada error atau warning PHP di halaman mana pun
- [ ] `display_errors` yang ditambahkan untuk uji sudah dikembalikan sesuai kebutuhan
