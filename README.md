# KawalAman - Platform Edukasi & Pelaporan Bullying

**KawalAman** adalah sistem informasi berbasis web untuk pencegahan dan pelaporan kasus perundungan (*bullying*) secara terpadu, aman, dan berkeadilan. Dibuat menggunakan **PHP Native (8.x)** dan **MySQL** dengan arsitektur modular yang aman dan responsif.

---

## 🌟 Fitur Utama

### 1. Panel Publik (Landing Page)
- **Edukasi Anti-Bullying**:
  - Definisi & pemahaman mendalam tentang perundungan.
  - 4 Pilar jenis perundungan: **Verbal**, **Fisik**, **Relasional / Sosial**, dan **Cyberbullying** disertai contoh kasus konkret.
  - Alur dan SOP penanganan laporan yang transparan.
  - Saluran bantuan dan kontak darurat bebas pulsa (SAPA 129, Polisi 110, Sejiwa 119 ext 8, dan Satgas Institusi).
- **Modal Formulir "Laporkan Pembullyan"**:
  - Nama Pelapor dengan opsi centang **"Rahasiakan Nama Saya (Anonim)"** untuk perlindungan identitas korban.
  - Email pelapor untuk korespondensi status penanganan.
  - Pilihan jenis perundungan, tanggal, dan lokasi/platform kejadian.
  - Deskripsi kronologi peristiwa secara detail.
  - Unggah berkas bukti berupa **Foto (JPG, PNG, WEBP)** atau **Video (MP4, WEBM, MOV)** dengan batas ukuran hingga 30MB dan pratinjau langsung (*live preview*).
  - Validasi keamanan ganda (Client-side & Server-side), proteksi CSRF, dan sanitasi XSS.
- **Penerbitan Kode Tiket Unik**:
  - Setelah laporan dikirim, sistem secara otomatis menerbitkan kode tiket (contoh: `KWL-20261011-8F3B`) dengan fitur satu klik salin (*copy to clipboard*).
- **Pelacakan Status Laporan (`cek-status.php`)**:
  - Pelapor dapat memantau progres penanganan secara transparan (Menunggu Verifikasi, Sedang Diproses, Selesai, Ditolak).
  - Menampilkan riwayat log penanganan serta catatan resmi dari tim Satgas.

---

### 2. Panel Satgas (`/satgas`)
- **Otentikasi Petugas**:
  - Halaman login aman dengan proteksi session fixation dan verifikasi sandi Bcrypt.
- **Dashboard Ringkasan & Monitoring Kasus**:
  - Kartu metrik: *Total Laporan*, *Menunggu Verifikasi*, *Sedang Ditangani*, dan *Kasus Selesai*.
  - Filter interaktif berdasarkan status dan kategori bullying.
  - Kolom pencarian cepat berdasarkan kode tiket, nama pelapor, atau lokasi kejadian.
  - Tabel kasus lengkap dengan indikator badge status dan bukti yang dilampirkan.
- **Pemeriksaan & Manajemen Kasus (`detail.php`)**:
  - Pratinjau identitas pelapor dan kronologi kejadian.
  - **Media Viewer Terintegrasi**: Pemutar video HTML5 untuk bukti video dan viewer gambar beresolusi penuh untuk bukti foto beserta tombol unduh berkas.
  - Formulir pembaruan status penanganan (*Menunggu*, *Diproses*, *Selesai*, *Ditolak*).
  - Kolom pencatatan tindak lanjut resmi yang otomatis tersinkronisasi ke halaman cek status pelapor.
  - Riwayat audit log perubahan status kasus.
- **Cetak Berita Acara (`cetak.php`)**:
  - Template resmi berita acara penanganan laporan bullying dengan kop surat, tabel ringkasan kasus, kronologi, catatan rekomendasi, dan kolom tanda tangan tim satgas.
- **Manajemen Akun Anggota Satgas (`users.php`)**:
  - Menampilkan seluruh petugas terdaftar.
  - Menambahkan anggota satgas atau admin baru dengan validasi username unik dan hashing password.

---

## 🗄️ Kredensial Database & Akun Bawaan

### Database MySQL
- **Host**: `127.0.0.1` (Port: `3306`)
- **Database**: `db_kawalaman`
- **User**: `root`
- **Password**: `KitfyaDev@142808`

### Akun Bawaan Satgas (Login di `/satgas/login.php`)
| Peran | Username | Kata Sandi |
| :--- | :--- | :--- |
| **Petugas Satgas** | `satgas` | `satgas123` |
| **Administrator** | `admin` | `admin123` |

---

## 🚀 Cara Menjalankan Aplikasi

1. **Inisialisasi Database** (Hanya jika belum dijalankan):
   ```bash
   php database/setup.php
   ```

2. **Jalankan PHP Development Server**:
   ```bash
   php -S localhost:8000
   ```

3. **Akses di Browser**:
   - Landing Page Publik: [http://localhost:8000/index.php](http://localhost:8000/index.php)
   - Cek Status Laporan: [http://localhost:8000/cek-status.php](http://localhost:8000/cek-status.php)
   - Panel Masuk Satgas: [http://localhost:8000/satgas/login.php](http://localhost:8000/satgas/login.php)
