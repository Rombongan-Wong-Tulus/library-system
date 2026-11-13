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
| Encapsulation | `Models/Media.php`, `Models/User.php`, `Models/Transaction.php` | properti `protected`/`private`; `passwordHash` private dan hanya bisa dicek lewat `verifyPassword()`; stok hanya berubah lewat `decreaseStock()`/`increaseStock()` |
| Inheritance | `Models/` | `Media` → `Ebook`, `Comic`, `Journal`; `User` → `Member` → `PremiumMember`; `User` → `Admin`; semua controller → `BaseController` |
| Polymorphism | `Models/` + `Services/RentalService.php` | `$media->getRentalPrice($days)` menghasilkan harga berbeda per jenis; `$user->getMaxLoans()` dan `getDiscount()` berbeda per role; `RentalService` tidak memakai `if ($type == ...)` |
| Abstract class | `Media`, `User` | method abstrak wajib diimplementasi subclass |
| Interface | `Contracts/Rentable.php` | kontrak `getRentalPrice()` dan `isAvailable()` |
| Namespace | semua berkas `src/` | `App\Models`, `App\Services`, dst., dimuat autoloader PSR-4 di `src/autoload.php` |
| Exception | `Exceptions/` | `RentalException` untuk pelanggaran aturan sewa, `ValidationException` untuk input salah |
| Pola desain | `Factories/`, `Repositories/` | Factory membuat objek sesuai tipe; Repository memisahkan penyimpanan dari logika |

### Pertanyaan yang mungkin ditanyakan dosen

| Pertanyaan | Jawaban singkat |
|---|---|
| Apa beda abstract class dan interface? | Abstract class boleh punya properti dan method berisi (`Media` punya stok dan getter); interface hanya kontrak (`Rentable`). Satu kelas bisa implement banyak interface tetapi hanya extends satu kelas. |
| Di mana polimorfismenya? | `RentalService::rent()` memanggil `getRentalPrice()` pada `Media` tanpa tahu jenis aslinya; hasilnya mengikuti `Ebook`/`Comic`/`Journal`. |
| Kenapa properti `private`/`protected`? | Mencegah data diubah sembarangan dari luar; perubahan hanya lewat method yang menjaga aturan. |
| Kenapa ada `MediaFactory`? | Data JSON hanya berisi teks `type`; factory memutuskan kelas mana yang dibuat, jadi tempat lain tidak perlu `new Ebook`/`new Comic` sendiri. |
| Kenapa Repository dan Service dipisah? | Repository hanya baca/tulis data; Service berisi aturan bisnis. Mengganti JSON ke database cukup mengubah Repository. |
| Apa fungsi namespace? | Mencegah bentrok nama kelas dan membuat autoload otomatis dari struktur folder. |
| Kenapa `declare(strict_types=1)`? | Memaksa tipe argumen sesuai deklarasi sehingga bug tipe cepat ketahuan. |
| Kenapa `public/index.php` bukan HTML? | Itu front controller: titik masuk tunggal yang menjalankan router. HTML ada di `views/`. |

## 2. Front-end (30 poin)

| Indikator | Lokasi | Contoh konkret |
|---|---|---|
| Form validasi | `views/auth/*`, `views/admin/index.php`, `public/assets/js/app.js` (`initFormValidation`) | pesan error per kolom di bawah input; cek wajib isi, email, panjang minimum, konfirmasi password. Validasi ulang di server (`AuthService`, `RentalService`). |
| Elemen semantik | `views/layouts/main.php`, semua view | `header`, `nav`, `main`, `footer`, `article`, `section`, `fieldset/legend`, `label`, `table` dengan `caption` dan `scope`, `dialog` |
| Layout responsif | `public/assets/css/style.css` | CSS grid `auto-fill`, `meta viewport`, `@media (max-width: 640px)`, tabel scroll horizontal, menu hamburger |
| Interaktivitas JS | `public/assets/js/app.js` | filter katalog tanpa reload, dialog sewa dengan estimasi harga, konfirmasi aksi, label form admin berubah per jenis |
| Aksesibilitas | CSS dan view | link "Lewati ke konten", fokus keyboard terlihat, `aria-live` untuk jumlah hasil, `prefers-reduced-motion` |

## 3. Kerapian & struktur (30 poin)

| Indikator | Bukti |
|---|---|
| Folder `src/` | semua kode PHP ada di `src/`, dipisah per lapisan |
| Modularitas | satu kelas satu berkas; controller, service, repository, model terpisah |
| Hanya `public/` yang diakses web | `src/`, `views/`, `data/` di luar `public/`; `data/.htaccess` menolak akses langsung |
| Eksekusi tanpa error | lihat [PENGUJIAN.md](PENGUJIAN.md); pastikan tidak ada warning/notice di semua halaman |
| Dokumentasi | folder `docs/` ini |

## 4. Checklist sebelum demo

- [ ] Tidak ada error/warning PHP di halaman mana pun (aktifkan `display_errors` saat uji)
- [ ] Ketiga jenis media bisa disewa dengan harga benar
- [ ] Setiap anggota bisa menunjuk lokasi tiap konsep OOP di tabel bagian 1
- [ ] Setiap anggota bisa menjelaskan alur sewa dari klik tombol sampai data tersimpan (lihat [FLOW.md](FLOW.md) bagian 4.4)
- [ ] Diagram kelas siap
- [ ] Tampilan sudah dicek di layar HP
