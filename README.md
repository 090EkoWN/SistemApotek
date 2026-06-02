# SistemApotek# 💊 Apotek Sehat — Sistem Manajemen Apotek

Aplikasi web **Sistem Informasi Apotek** berbasis PHP Native, MySQL, HTML, CSS, dan JavaScript. Dibangun untuk memudahkan pengelolaan data obat, pasien, dan transaksi pemberian obat secara efisien dan aman.

---

## 📸 Tampilan Aplikasi

| Halaman | Keterangan |
|---------|------------|
| Login | Halaman autentikasi dengan validasi session |
| Dashboard | Statistik real-time, transaksi terbaru, alert expired |
| Data Obat | Tabel CRUD lengkap dengan badge stok & expired |
| Data Pasien | Manajemen pasien + akun login pasien |
| Pemberian Obat | Transaksi dengan auto-update stok |
| Portal Pasien | Dashboard, riwayat obat, dan profil pasien |

---

## ⚙️ Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.2 (Native, tanpa framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Server | Apache (XAMPP / Hosting) |
| Autentikasi | PHP Session |
| Keamanan | Prepared Statements, `password_hash()` |

---

## 📁 Struktur Folder

```
sistemapotek/
│
├── 📄 index.php              → Halaman login
├── 📄 dashboard.php          → Dashboard admin
├── 📄 koneksi.php            → Konfigurasi database
├── 📄 auth.php               → Proteksi session (include di semua halaman)
├── 📄 sidebar.php            → Komponen navigasi sidebar
├── 📄 logout.php             → Proses logout
│
├── 📄 pasien_dashboard.php   → Dashboard khusus pasien
├── 📄 pasien_riwayat.php     → Riwayat pemberian obat (pasien)
├── 📄 pasien_profil.php      → Profil & edit data diri pasien
│
├── 📁 css/
│   └── style.css             → Stylesheet utama (1100+ baris)
│
├── 📁 obat/
│   ├── index.php             → Daftar semua obat
│   ├── tambah.php            → Form tambah obat baru
│   ├── edit.php              → Form edit data obat
│   └── hapus.php             → Proses hapus obat
│
├── 📁 pasien/
│   ├── index.php             → Daftar semua pasien + status akun
│   ├── tambah.php            → Form tambah pasien + buat akun login
│   ├── edit.php              → Form edit data pasien
│   └── hapus.php             → Proses hapus pasien
│
├── 📁 transaksi/
│   ├── index.php             → Daftar transaksi pemberian obat
│   ├── tambah.php            → Form catat pemberian obat baru
│   ├── edit.php              → Form edit transaksi + auto-adjust stok
│   └── hapus.php             → Proses hapus + kembalikan stok
│
├── 📁 users/
│   └── create_admin.php      → Utility tambah akun admin baru
│
└── 📁 sql/
    ├── db_apotek.sql         → File database utama (import ini)
    └── update_db.sql         → Script update: tambah kolom role & id_user
```

---

## 🗄️ Struktur Database

**Database:** `db_apotek`

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_user` | INT, PK, Auto Increment | ID user |
| `username` | VARCHAR(50), UNIQUE | Username login |
| `password` | VARCHAR(255) | Hash bcrypt |
| `role` | ENUM('admin','pasien') | Peran user |
| `nama_lengkap` | VARCHAR(100) | Nama lengkap |

### Tabel `obat`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_obat` | INT, PK, Auto Increment | ID obat |
| `nama_obat` | VARCHAR(100) | Nama obat |
| `kategori` | VARCHAR(50) | Kategori obat |
| `stok` | INT | Jumlah stok tersedia |
| `harga` | DECIMAL(10,2) | Harga per satuan |
| `tanggal_expired` | DATE | Tanggal kadaluarsa |

### Tabel `pasien`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_pasien` | INT, PK, Auto Increment | ID pasien |
| `id_user` | INT, FK → users | Terhubung ke akun login (nullable) |
| `nama_pasien` | VARCHAR(100) | Nama lengkap pasien |
| `alamat` | TEXT | Alamat pasien |
| `no_hp` | VARCHAR(20) | Nomor HP |
| `tanggal_lahir` | DATE | Tanggal lahir |

### Tabel `pemberian_obat`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_transaksi` | INT, PK, Auto Increment | ID transaksi |
| `id_pasien` | INT, FK → pasien | Pasien yang menerima |
| `id_obat` | INT, FK → obat | Obat yang diberikan |
| `tanggal_pemberian` | DATE | Tanggal pemberian |
| `jumlah` | INT | Jumlah yang diberikan |
| `dosis` | VARCHAR(100) | Aturan pakai |
| `keterangan` | TEXT | Catatan tambahan |

---

## 🚀 Instalasi di XAMPP (Localhost)

### Langkah 1 — Persiapan
1. Download & install **XAMPP** → https://www.apachefriends.org/
2. Extract folder `sistemapotek` ke:
   ```
   C:\xampp\htdocs\sistemapotek\
   ```

### Langkah 2 — Jalankan XAMPP
1. Buka **XAMPP Control Panel**
2. Klik **Start** pada **Apache**
3. Klik **Start** pada **MySQL**
4. Pastikan keduanya berwarna **hijau**

### Langkah 3 — Import Database
1. Buka browser → `http://localhost/phpmyadmin`
2. Klik tombol **"New"** → buat database bernama `db_apotek`
3. Pilih database `db_apotek` → klik tab **Import**
4. Klik **Choose File** → pilih `sql/db_apotek.sql`
5. Klik **Go** / **Import**

> Jika ingin fitur role pasien dan portal pasien, jalankan juga `sql/update_db.sql` dengan cara yang sama.

### Langkah 4 — Konfigurasi Koneksi
Buka file `koneksi.php` dan sesuaikan:
```php
define('DB_HOST', 'localhost');   // host database
define('DB_USER', 'root');        // username (default XAMPP: root)
define('DB_PASS', '');            // password (default XAMPP: kosong)
define('DB_NAME', 'db_apotek');   // nama database
define('APP_NAME', 'Apotek Sehat'); // nama aplikasi
```

### Langkah 5 — Akses Aplikasi
Buka browser → `http://localhost/sistemapotek/`

---

## 🌐 Instalasi di Hosting (InfinityFree / cPanel)

### Langkah 1 — Upload File
1. Upload seluruh isi folder `sistemapotek/` ke direktori `public_html/` atau subfolder sesuai keinginan
2. Pastikan struktur folder terjaga (jangan campur dengan file lain)

### Langkah 2 — Buat Database di cPanel
1. Login ke **cPanel** hosting
2. Masuk ke **MySQL Databases** → buat database baru
3. Buat **MySQL User** → assign ke database tersebut (pilih "All Privileges")

### Langkah 3 — Import Database
1. Buka **phpMyAdmin** dari cPanel
2. Pilih database yang baru dibuat
3. Klik tab **Import** → pilih file `sql/db_apotek.sql` → klik **Go**
4. Ulangi untuk file `sql/update_db.sql`

### Langkah 4 — Update Koneksi
Edit `koneksi.php` sesuai data dari hosting:
```php
define('DB_HOST', 'sql211.infinityfree.com'); // host dari cPanel
define('DB_USER', 'if0_XXXXXXX');             // username database
define('DB_PASS', 'passwordmu');              // password database
define('DB_NAME', 'if0_XXXXXXX_db_apotek');  // nama database
```

### Langkah 5 — Akses Aplikasi
Buka `https://namadomain.com/` atau `https://namadomain.com/sistemapotek/`

---

## 🔐 Akun Default

| Role | Username | Password | Keterangan |
|------|----------|----------|------------|
| Admin | `eko` | `1234` | Akun admin pertama |
| Admin | `admin` | `admin123` | Akun admin kedua |

> ⚠️ **Segera ganti password** setelah pertama kali login untuk keamanan!

### Cara Tambah Admin Baru
Akses: `http://localhost/sistemapotek/users/create_admin.php`

---

## ✅ Fitur Lengkap

### 👨‍💼 Role Admin
- **Login & Logout** dengan session PHP
- **Dashboard** — statistik total obat, pasien, transaksi, stok habis; transaksi terbaru; alert obat hampir expired (30 hari)
- **CRUD Data Obat** — tambah, lihat, edit, hapus; badge stok & expired berwarna; search & filter
- **CRUD Data Pasien** — tambah pasien sekaligus buat akun login pasien; tampil status akun (ada/belum); search
- **CRUD Pemberian Obat** — catat transaksi dengan auto-kurangi stok; edit dengan auto-adjust stok; hapus dengan kembalikan stok; search
- **Manajemen Stok Otomatis** — stok berkurang saat transaksi, dikembalikan saat hapus/edit

### 🧑‍🤒 Role Pasien
- **Portal Pasien** — dashboard pribadi dengan statistik kunjungan dan total obat diterima
- **Riwayat Obat** — lihat semua riwayat pemberian obat dengan search
- **Profil Saya** — edit nomor HP dan alamat

### 🎨 Desain & UX
- Responsive design (desktop & mobile)
- Sidebar collapsible dengan hamburger menu
- Toast notification / alert berwarna
- Empty state dengan ilustrasi
- Loading states
- Badge stok berwarna (hijau/kuning/merah)
- Badge expired berwarna (hijau/kuning/merah)
- Hover effect pada semua tombol dan link

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|-------------|
| SQL Injection | Prepared Statements (`bind_param`) di semua query |
| Password | `password_hash()` + `password_verify()` dengan bcrypt |
| Session | Session timeout otomatis 2 jam tidak aktif |
| XSS | `htmlspecialchars()` di semua output |
| Akses | `auth.php` di-include di setiap halaman yang butuh login |
| Redirect | Auto redirect ke login jika session habis |

---

## 🛠️ Troubleshooting

### ❌ Error "Not Found" / 404
- Pastikan nama folder sesuai URL yang diakses
- Akses dengan: `http://localhost/sistemapotek/` (ada slash di akhir)
- Pastikan **Apache** sudah running (hijau di XAMPP)

### ❌ Error "Connection refused" / Database Error
- Pastikan **MySQL** sudah running (hijau di XAMPP)
- Pastikan database `db_apotek` sudah di-import
- Cek konfigurasi di `koneksi.php` (host, user, pass, nama DB)

### ❌ Tampilan CSS tidak muncul
- Clear cache browser: **Ctrl + F5**
- Pastikan file `css/style.css` ada dan tidak rusak
- Cek path link CSS di file HTML

### ❌ Stok tidak berkurang setelah transaksi
- Pastikan tabel `obat` memiliki data stok awal > 0
- Cek `transaksi/tambah.php` — query UPDATE stok harus ada

### ❌ Login pasien tidak bisa masuk portal
- Pastikan sudah menjalankan `sql/update_db.sql` (tambah kolom `role`)
- Pastikan waktu tambah pasien, kolom username & password diisi
- Cek tabel `users` di phpMyAdmin — kolom `role` harus berisi `pasien`

### ❌ Session logout otomatis terlalu cepat
Ubah batas timeout di `auth.php`:
```php
if ((time() - $_SESSION['last_activity']) > 7200) { // ganti 7200
```

---

## 📋 Panduan Penggunaan

### Alur Admin

```
Login → Dashboard
           ├── Data Obat    → Tambah / Edit / Hapus
           ├── Data Pasien  → Tambah (+ buat akun) / Edit / Hapus
           └── Pemberian Obat → Catat / Edit / Hapus (stok otomatis)
```

### Alur Pasien

```
Login (username/password dari admin) → Portal Pasien
                                           ├── Riwayat Obat
                                           └── Profil Saya
```

### Cara Memberi Akun Login ke Pasien
1. Admin masuk ke **Data Pasien** → klik **Tambah Pasien**
2. Isi data pasien (nama, HP, alamat, tanggal lahir)
3. Di bagian bawah form, isi **Username** dan **Password**
4. Klik **Simpan Pasien**
5. Pasien sekarang bisa login ke portal pasien dengan username tersebut

---

## 📝 Identitas Proyek

| | |
|---|---|
| **Nama Aplikasi** | Apotek Sehat |
| **Versi** | 1.1.0 |
| **Kategori** | Sistem Informasi Manajemen Apotek |
| **Teknologi** | PHP Native, MySQL, HTML, CSS, JavaScript |
| **Server** | Apache (XAMPP / InfinityFree) |
| **PHP** | 8.2+ |
| **Database** | MariaDB 10.4+ / MySQL 5.7+ |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **tugas akademik / pembelajaran**.
Bebas digunakan dan dimodifikasi untuk keperluan edukasi.