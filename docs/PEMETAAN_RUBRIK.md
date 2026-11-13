# Pemetaan Rubrik Penilaian

Dokumen terkait: [README](README.md) · [FLOW](FLOW.md) · [Pembagian tugas](PEMBAGIAN_TUGAS.md)

Dokumen ini menunjukkan di mana tiap poin rubrik UTS terpenuhi, dan apa yang perlu bisa dijelaskan saat demo.

## Rubrik dari soal

| Komponen | Indikator | Poin |
|---|---|---|
| Front-End (HTML/CSS/JS) | Form validasi, elemen semantik, layout responsif, interaktivitas JS | 30 |
| Penerapan PHP OOP | Class, Object, Encapsulation, Inheritance, Polymorphism, Namespace | 40 |
| Kerapian & Struktur Berkas | Folder `src/`, modularitas, eksekusi tanpa error | 30 |

Catatan soal: penyimpanan e-book boleh XML/JSON atau Object Class PHP. Proyek ini memakai JSON.

## 1. PHP OOP (40 poin)

| Konsep | Lokasi | Contoh konkret |
|---|---|---|
| Class & Object | seluruh `src/` | `new Ebook(...)`, `new Member(...)` dibuat lewat `MediaFactory` dan `UserFactory` |
| Encapsulation | `Models/Media.php`, `Models/User.php`, `Models/Transaction.php` | properti `protected`/`private`/`readonly`; `passwordHash` private dan hanya bisa dicek lewat `verifyPassword()`; stok hanya berubah lewat `decreaseStock()`/`increaseStock()` |
| Inheritance | `Models/` | `Media` → `Ebook`, `Comic`, `Journal`; `User` → `Member` → `PremiumMember`; `User` → `Admin`; semua controller → `BaseController`; `BaseRepository` → `Media/User/TransactionRepository` |
| Polymorphism | `Models/` + `Services/RentalService.php` | `$media->getRentalPrice($days)` menghasilkan harga berbeda per jenis; `$user->getMaxLoans()` dan `getDiscount()` berbeda per role; `RentalService` tidak memakai `if ($type == ...)` |
| Abstract class | `Media`, `User`, `BaseRepository` | method abstrak wajib diimplementasi subclass (`mapToEntity()`, `getIdPrefix()`) |
| Interface | `Contracts/Rentable.php`, `Contracts/StorageInterface.php` | kontrak `getRentalPrice()`, `isAvailable()`, `read()`, `write()`, `transaction()` |
| Enum | `Enums/MediaType.php`, `Enums/Role.php` | backed string enum PHP 8.1: `MediaType::EBOOK->value`, `Role::PREMIUM->label()` |
| Trait | `Traits/Timestampable.php` | `createdAt`/`updatedAt` pada `Media`, `User`, `Transaction`; method `touch()`, `initTimestamps()` |
| Readonly | `Media::$id`, `User::$id`, `Transaction::$id/userId/mediaId` | properti `readonly` yang tidak bisa diubah setelah di-set di constructor |
| Namespace | semua berkas `src/` | `App\Models`, `App\Enums`, `App\Traits`, dst., dimuat autoloader PSR-4 di `src/autoload.php` |
| Exception | `Exceptions/` | `RentalException` untuk pelanggaran aturan sewa, `ValidationException` untuk input salah |
| Pola desain | `Factories/`, `Repositories/` | Factory membuat objek sesuai tipe; Repository+BaseRepository memisahkan penyimpanan dari logika |
| Konstanta kelas | `Comic::DISCOUNT_THRESHOLD_DAYS`, `Comic::LONG_TERM_DISCOUNT_RATE`, `Member::MAX_ACTIVE_LOANS`, `PremiumMember::DISCOUNT_RATE` | Angka diskon tidak hardcoded tersebar; semua ada di satu tempat |
| `__toString()` | `Media`, `User` | `echo $media` menampilkan "Laskar Pelangi (E-book) oleh Andrea Hirata" |

### Pertanyaan yang mungkin ditanyakan dosen

| Pertanyaan | Jawaban singkat |
|---|---|
| Apa beda abstract class dan interface? | Abstract class boleh punya properti dan method berisi (`Media` punya stok dan getter); interface hanya kontrak (`Rentable`). Satu kelas bisa implement banyak interface tetapi hanya extends satu kelas. |
| Di mana polimorfismenya? | `RentalService::rent()` memanggil `getRentalPrice()` pada `Media` tanpa tahu jenis aslinya; hasilnya mengikuti `Ebook`/`Comic`/`Journal`. |
| Kenapa properti `private`/`protected`/`readonly`? | Mencegah data diubah sembarangan dari luar; `readonly` memastikan ID tidak pernah berubah; perubahan hanya lewat method yang menjaga aturan. |
| Kenapa ada enum? | Menggantikan string mentah untuk tipe media dan role; IDE bisa autocomplete, typo langsung error saat compile, dan method helper seperti `label()` melekat pada nilainya. |
| Kenapa ada trait? | Trait `Timestampable` menambahkan field `createdAt`/`updatedAt` ke tiga kelas berbeda tanpa duplikasi kode dan tanpa memaksa hierarki inheritance. |
| Kenapa ada `MediaFactory`? | Data JSON hanya berisi teks `type`; factory memutuskan kelas mana yang dibuat, jadi tempat lain tidak perlu `new Ebook`/`new Comic` sendiri. |
| Kenapa Repository dan Service dipisah? | Repository hanya baca/tulis data; Service berisi aturan bisnis. Mengganti JSON ke database cukup mengubah Repository. |
| Apa fungsi namespace? | Mencegah bentrok nama kelas dan membuat autoload otomatis dari struktur folder. |
| Kenapa `declare(strict_types=1)`? | Memaksa tipe argumen sesuai deklarasi sehingga bug tipe cepat ketahuan. |
| Kenapa `index.php` bukan HTML? | Itu front controller: titik masuk tunggal yang menjalankan router. HTML ada di `views/`. |
| Kenapa StorageInterface? | Agar repository tidak terikat pada implementasi penyimpanan tertentu (saat ini JSON); mudah diganti ke database cukup dengan implementasi baru. |

## 2. Front-end (30 poin)

| Indikator | Lokasi | Contoh konkret |
|---|---|---|
| Form validasi | `views/auth/*`, `views/admin/index.php`, `app.js` (`initFormValidation`) | pesan error per kolom di bawah input; cek wajib isi, email, panjang minimum, konfirmasi password. Validasi ulang di server (`AuthService`, `AdminController`). Validasi `extra` per jenis media di server. |
| Elemen semantik | `views/layouts/main.php`, semua view | `header`, `nav`, `main`, `footer`, `article`, `section`, `fieldset/legend`, `label`, `table` dengan `caption` dan `scope`, `dialog` |
| Layout responsif | `assets/css/style.css` | 3 breakpoint: mobile (<640px), tablet (640-1024px), desktop (>1024px). CSS grid `auto-fill`, `meta viewport`, menu hamburger. Dark mode via `prefers-color-scheme`. |
| Interaktivitas JS | `assets/js/app.js` | filter & sort katalog JS, sewa via `fetch` + fallback form, toast notification, indikator kekuatan password, dialog baca cuplikan, konfirmasi aksi, label form admin berubah per jenis |
| Aksesibilitas | CSS dan view | link "Lewati ke konten", fokus keyboard terlihat, `aria-live` untuk jumlah hasil, `prefers-reduced-motion`, `aria-label` pada navigasi dan bagian |

## 3. Kerapian & struktur (30 poin)

| Indikator | Bukti |
|---|---|
| Folder `src/` | semua kode PHP ada di `src/`, dipisah per lapisan |
| Modularitas | satu kelas satu berkas; controller, service, repository, model terpisah |
| Proteksi direktori internal | Hanya `index.php` dan `assets/` yang diakses web; folder `src/`, `views/`, `data/`, `tests/`, `docs/`, `.git/` serta berkas `*.md`, `*.json`, `*.lock` diblokir oleh `.htaccess` (Apache/Laragon) dan `router.php` (PHP CLI) |
| Eksekusi tanpa error | lihat [PENGUJIAN.md](PENGUJIAN.md); pastikan tidak ada warning/notice di semua halaman |
| Dokumentasi | folder `docs/` ini |
| Keamanan | Cookie sesi `httponly` + `SameSite=Lax`, CSRF token, XSS escape, file JSON ditulis atomik (`rename`) dengan `flock` |
| Tes otomatis | `tests/PriceTest.php` — harga, enum, trait, denda, `__toString()` |
| Seed data | Data awal di `data/media.seed.json`, `media.json` otomatis di-copy saat pertama kali |

## 4. Checklist sebelum demo

- [ ] Tidak ada error/warning PHP di halaman mana pun (aktifkan `display_errors` saat uji)
- [ ] Ketiga jenis media bisa disewa dengan harga benar
- [ ] Setiap anggota bisa menunjuk lokasi tiap konsep OOP di tabel bagian 1
- [ ] Setiap anggota bisa menjelaskan alur sewa dari klik tombol sampai data tersimpan (lihat [FLOW.md](FLOW.md) bagian 4.4)
- [ ] `php tests/PriceTest.php` → "Semua tes lulus."
- [ ] Tampilan responsif di mobile, tablet, dan desktop
- [ ] Dark mode berfungsi bila OS/browser diatur ke mode gelap
