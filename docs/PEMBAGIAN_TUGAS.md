# Pembagian Tugas — Kelompok 4 Orang

Dokumen terkait: [README](README.md) · [FLOW](FLOW.md) · [Git workflow](GIT_WORKFLOW.md) · [Pengujian](PENGUJIAN.md)

Ganti nama anggota di tabel sesuai kelompok kalian.

| Kode | Nama | Peran |
|---|---|---|
| A | _________ | Model OOP |
| B | _________ | Logika bisnis & data |
| C | _________ | Controller & routing |
| D | _________ | Front-end |

Kelompok 3 orang: gabungkan A dan B.

## 1. Ringkasan area

| | A: Model OOP | B: Logika & data | C: Controller & routing | D: Front-end |
|---|---|---|---|---|
| Rubrik utama | PHP OOP (40) | PHP OOP (40) + eksekusi (30) | eksekusi & struktur (30) | Front-end (30) |
| Folder | `src/Contracts`, `src/Models`, `src/Factories` | `src/Storage`, `src/Repositories`, `src/Services`, `src/Exceptions` | `src/Controllers`, `src/Core`, `index.php` | `views/`, `assets/` |
| Ketergantungan | tidak bergantung siapa pun | memakai Model dari A | memakai Service dari B, View dari D | memakai data dari Controller C |

Urutan yang aman: **A dan B mulai lebih dulu**, lalu C dan D menyusul. Selama A dan B belum selesai, C dan D bisa memakai kode yang sudah ada.

## 2. Tugas per anggota

### A — Model OOP

Berkas: `src/Contracts/Rentable.php`, `src/Models/*`, `src/Factories/*`

Sudah ada: `Media` (abstrak), `Ebook`, `Comic`, `Journal`, `User` (abstrak), `Member`, `PremiumMember`, `Admin`, `Transaction`, `MediaFactory`, `UserFactory`.

Yang dikerjakan:
1. Baca dan pahami semua model, terutama `Media`, `User`, dan `Transaction`.
2. Tambah **satu jenis media baru** yang kalian putuskan bersama (mis. `AudioBook` dengan durasi). Wajib: extends `Media`, override `getType()`, `getRentalPrice()`, `getExtraInfo()`, `toArray()`, daftarkan di `MediaFactory`.
3. Buat trait `Timestampable` (mis. `createdAt`) dan pakai di minimal satu model.
4. Tambah komentar singkat di tiap kelas: apa tanggung jawabnya.
5. Siapkan penjelasan lisan: enkapsulasi, pewarisan, polimorfisme, abstract vs interface, kenapa pakai factory.
6. Gambar **diagram kelas** sederhana untuk laporan.

Selesai jika:
- [ ] Jenis media baru muncul di katalog dan bisa disewa
- [ ] `getRentalPrice()` jenis baru terbukti benar lewat skrip tes ([PENGUJIAN.md](PENGUJIAN.md))
- [ ] Diagram kelas selesai

### B — Logika bisnis & data

Berkas: `src/Storage/*`, `src/Repositories/*`, `src/Services/*`, `src/Exceptions/*`

Sudah ada: `JsonStorage`, `MediaRepository`, `UserRepository`, `TransactionRepository`, `AuthService`, `RentalService`.

Yang dikerjakan:
1. Jalankan dan uji seluruh skenario di [PENGUJIAN.md](PENGUJIAN.md) bagian logika (harga, batas pinjam, stok, denda). Laporkan bug ke pemilik berkas.
2. Tambah **perpanjang sewa**: `RentalService::extend(User, string $trxId, int $days)` dengan aturan sendiri (mis. maksimal 1 kali, total tidak lebih dari 14 hari).
3. Tambah **ubah password**: `AuthService::changePassword(User, string $old, string $new)`.
4. Buat skrip tes harga `tests/PriceTest.php` (contoh ada di PENGUJIAN.md).
5. Rapikan pesan error agar jelas dan konsisten.
6. Siapkan penjelasan lisan: alur sewa, kenapa ada Repository dan Service, kenapa memakai Exception.

Selesai jika:
- [ ] Semua skenario logika di PENGUJIAN.md lulus
- [ ] Perpanjang sewa dan ubah password berfungsi (setelah C menyambungkan route)
- [ ] `tests/PriceTest.php` bisa dijalankan dan lulus

### C — Controller & routing

Berkas: `src/Controllers/*`, `src/Core/*`, `index.php`

Sudah ada: `Application` (router + CSRF), `Session`, `View`, `BaseController`, `CatalogController`, `AuthController`, `RentalController`, `AdminController`.

Yang dikerjakan:
1. Jalankan aplikasi pertama kali dan perbaiki error PHP yang muncul (syntax, typo, namespace).
2. Uji semua 12 route di [FLOW.md](FLOW.md) bagian 3 dan hak aksesnya (member tidak boleh membuka `admin`; tamu tidak boleh membuka `rentals`).
3. Tambah route **detail media** (`GET media&id=...`) dan **edit media** untuk admin (`GET/POST admin-edit`).
4. Tambah route **perpanjang sewa** dan **ubah password** setelah B selesai.
5. Tambah daftar pengguna di halaman admin (nama, email, role, jumlah pinjaman aktif).
6. Pastikan setiap route baru tercatat di tabel route di FLOW.md.

Selesai jika:
- [ ] Tidak ada error/warning PHP di semua halaman
- [ ] Semua tes hak akses di PENGUJIAN.md lulus
- [ ] Route baru berfungsi dan tercatat di FLOW.md

### D — Front-end

Berkas: `views/*`, `assets/css/style.css`, `assets/js/app.js`

Sudah ada: layout utama, 7 halaman, CSS responsif, JS (filter katalog, dialog sewa, validasi form, konfirmasi).

Yang dikerjakan:
1. Uji tampilan di layar HP (±375 px), tablet, dan laptop. Perbaiki yang pecah.
2. Buat halaman **detail media** (`views/catalog/show.php`) dan form **edit media** (`views/admin/edit.php`); sambungkan dengan C.
3. Tambah **sortir** katalog (judul A–Z, harga) atau **paginasi** di sisi JS.
4. Ubah **identitas visual** agar tidak sama dengan kelompok lain (nama, warna di `:root`, tipografi, logo teks).
5. Cek aksesibilitas dasar: label di semua input, fokus keyboard terlihat, kontras teks cukup.
6. Ambil **tangkapan layar** semua halaman untuk laporan.
7. Siapkan penjelasan lisan: kenapa elemen semantik, cara kerja validasi klien vs server, cara estimasi harga di JS.

Selesai jika:
- [ ] Semua halaman rapi di HP dan laptop
- [ ] Halaman detail dan edit tampil dan terhubung
- [ ] Validasi klien menampilkan pesan di semua form
- [ ] Tangkapan layar lengkap

## 3. Kontrak antar-anggota

Ini nama method dan data yang dipakai anggota lain. **Jangan ubah tanpa memberi tahu grup.**

| Pemilik | Yang diandalkan | Dipakai oleh |
|---|---|---|
| A | `Media::getRentalPrice(int $days): float`, `getType()`, `getExtraInfo()`, `toArray()` | B, D |
| A | `User::getMaxLoans()`, `getDiscount()`, `getRole()`, `isAdmin()` | B, C, D |
| A | `MediaFactory::fromArray(array)`, `UserFactory::fromArray(array)` | B, C |
| A | Nama field JSON: `id, type, title, author, basePrice, stock, description, extra` | semua |
| B | `MediaRepository::all() / find($id) / save($media) / delete($id) / nextId()` | C |
| B | `RentalService::rent(User, string $mediaId, int $days): Transaction` | C |
| B | `RentalService::returnMedia(User, string $trxId): Transaction` | C |
| B | `AuthService::register / login / logout / currentUser` | C |
| C | `BaseController::render($view, array $data)` | D |
| C | Variabel yang tersedia di view: `$user`, `$flash`, dan data dari controller | D |
| D | Helper view: `$this->e()`, `$this->rupiah()`, `$this->csrf()` | semua view |

Jika A menambah jenis media baru, kabari D agar label jenis di filter katalog dan di form admin ikut diperbarui, dan kabari C agar form admin menerimanya.

## 4. Tugas bersama

1. **Tes asap hari pertama** (semua): jalankan aplikasi, ikuti PENGUJIAN.md bagian 1. Catat temuan di grup.
2. **Keputusan bersama** sebelum mulai: nama aplikasi, jenis media baru, perubahan aturan harga agar berbeda dari kelompok lain.
3. **Latihan tanya-jawab** sebelum demo: tiap orang menjelaskan bagiannya, lalu saling bertanya bagian orang lain.
4. **Laporan dan demo**: lihat bagian 6.

## 5. Jadwal

Sesuaikan dengan tenggat dari dosen. Hitung mundur dari hari pengumpulan.

| Tahap | Isi | Siapa |
|---|---|---|
| 1. Setup | Repo Git, jalankan aplikasi, tes asap, perbaiki error | semua (C memimpin perbaikan error) |
| 2. Fitur inti | Jenis media baru, perpanjang sewa, ubah password, halaman detail | A, B, C, D sesuai bagian |
| 3. Penyambungan | Route baru, form edit, tampilan fitur baru | C dan D |
| 4. Penyempurnaan | Identitas visual, sortir/paginasi, rapikan pesan error | D, B |
| 5. Pengujian akhir | Semua skenario PENGUJIAN.md, uji di HP | semua |
| 6. Dokumen & latihan | Laporan, tangkapan layar, diagram, latihan tanya-jawab | semua |

## 6. Laporan dan demo

Isi laporan yang disarankan:

| Bagian | Penanggung jawab |
|---|---|
| Deskripsi aplikasi dan aturan bisnis | B |
| Diagram kelas + penjelasan OOP | A |
| Struktur folder dan alur (ringkas dari FLOW.md) | C |
| Tangkapan layar dan penjelasan front-end | D |
| Penyatuan, cek kelengkapan, ejaan | rotasi |

Skenario demo (5–7 menit): daftar akun → login → cari di katalog → sewa tiap jenis media → lihat sewaan → kembalikan → login admin → tambah media → lihat transaksi.

## 7. Jika waktu mepet

Urutan prioritas:
1. Aplikasi jalan tanpa error (rubrik eksekusi 30 poin).
2. Semua alur yang sudah ada lulus uji.
3. Aplikasi dibedakan dari kelompok lain.
4. Fitur tambahan (detail, edit, perpanjang, paginasi) hanya jika sempat.

## 8. Aturan kerja tim

- Edit hanya berkas di areamu. Perlu mengubah berkas orang lain: minta di grup atau buat pull request.
- Kabari grup sebelum mengubah nama method atau field JSON (bagian 3).
- Commit kecil dan sering; ikuti [GIT_WORKFLOW.md](GIT_WORKFLOW.md) dan [KONVENSI_KODE.md](KONVENSI_KODE.md).
- Semua anggota harus bisa menjelaskan **seluruh** alur aplikasi, bukan hanya bagiannya.
