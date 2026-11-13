# Alur Kerja Git (4 Orang)

Dokumen terkait: [README](README.md) · [Pembagian tugas](PEMBAGIAN_TUGAS.md) · [Konvensi kode](KONVENSI_KODE.md)

## 1. Setup awal (sekali)

Satu orang (mis. C) menyiapkan repo:

```bash
cd ebook-rental
git init
git add .
git commit -m "chore: struktur awal proyek"
git branch -M main
git remote add origin <URL-repo>
git push -u origin main
```

Anggota lain:

```bash
git clone <URL-repo>
cd ebook-rental
```

Tambahkan semua anggota sebagai collaborator di pengaturan repo.

## 2. Branch

| Branch | Fungsi |
|---|---|
| `main` | selalu dalam keadaan bisa dijalankan; tidak ada yang commit langsung |
| `fitur/<nama>` | satu fitur atau perbaikan, dibuat dari `main` |

Contoh nama: `fitur/audiobook`, `fitur/perpanjang-sewa`, `fitur/halaman-detail`, `perbaikan/validasi-register`.

## 3. Siklus kerja

```bash
git checkout main
git pull
git checkout -b fitur/perpanjang-sewa

# ...edit, uji...

git add src/Services/RentalService.php
git commit -m "feat: tambah perpanjang sewa di RentalService"
git push -u origin fitur/perpanjang-sewa
```

Lalu buka **pull request** ke `main` dan minta minimal satu anggota lain meninjau sebelum digabung.

Sebelum membuka pull request:

1. `git pull origin main` ke branch kamu agar tidak ketinggalan.
2. Jalankan aplikasi dan uji bagian yang kamu ubah.
3. Pastikan halaman lain tidak rusak.

## 4. Format pesan commit

`jenis: ringkasan singkat`

| Jenis | Dipakai untuk |
|---|---|
| `feat` | fitur baru |
| `fix` | perbaikan bug |
| `style` | perubahan tampilan/CSS tanpa mengubah logika |
| `refactor` | merapikan kode tanpa mengubah perilaku |
| `docs` | dokumentasi |
| `chore` | hal teknis lain |

Contoh: `fix: denda dihitung dari tanggal jatuh tempo`.

## 5. Mengurangi konflik

- Tiap orang mengedit berkas di areanya (lihat [PEMBAGIAN_TUGAS.md](PEMBAGIAN_TUGAS.md)).
- Berkas yang sering disentuh banyak orang: `src/Core/Application.php` (tabel route) dan `docs/FLOW.md`. Beri tahu grup sebelum mengubahnya, dan commit perubahan kecil.
- Jangan commit data uji. `data/users.json` dan `data/transactions.json` masuk `.gitignore`.

Isi `.gitignore` yang disarankan:

```
data/users.json
data/transactions.json
.DS_Store
.vscode/
.idea/
```

Catatan: berkas `data/users.json` dan `data/transactions.json` dibuat otomatis saat aplikasi dijalankan, jadi tidak perlu ada di repo.

## 6. Jika terjadi konflik

1. `git pull origin main` ke branch kamu.
2. Buka berkas yang bertanda konflik (`<<<<<<<`, `=======`, `>>>>>>>`).
3. Pilih atau gabungkan isi yang benar, hapus tanda konflik.
4. `git add <berkas>` lalu `git commit`.
5. Jalankan aplikasi untuk memastikan masih jalan, lalu push.

Jika ragu, tanyakan di grup sebelum menimpa pekerjaan orang lain.

## 7. Aturan sederhana

- Jangan `git push --force` ke `main`.
- Jangan menggabungkan pull request milik sendiri tanpa tinjauan, kecuali tinggal sangat sedikit waktu.
- `main` harus selalu bisa dijalankan; ini yang akan dikumpulkan.
