# 💊 Apotek Sehat — Sistem Manajemen Apotek

Aplikasi web **Sistem Informasi Apotek** berbasis PHP Native, MySQL, HTML, CSS (Inter + Font Awesome 6), dan JavaScript. Dibangun untuk memudahkan pengelolaan data obat, pasien, dan transaksi pemberian obat secara efisien dan aman.

---

## 🎨 Tampilan UI

| Halaman | Keterangan |
|---------|------------|
| **Login** | Full background foto rak obat, hero kiri + card form kanan |
| **Dashboard Admin** | Sidebar dark + foto, stat card, transaksi terbaru, akses cepat |
| **Data Obat** | Tabel CRUD lengkap dengan badge stok & expired |
| **Data Pasien** | CRUD + buat akun login pasien + ubah password |
| **Pemberian Obat** | Transaksi dengan auto-update stok otomatis |
| **Portal Pasien** | Dashboard pribadi, riwayat obat, profil |

---

## ⚙️ Teknologi

| Komponen | Detail |
|----------|--------|
| Backend | PHP 8.2 Native (tanpa framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3 (Inter), JavaScript Vanilla |
| Icon | **Font Awesome 6.5** |
| Font | **Inter** (Google Fonts) |
| Server | Apache (XAMPP / InfinityFree / cPanel) |
| Auth | PHP Session |
| Keamanan | Prepared Statements + `password_hash()` bcrypt |

---

## 📁 Struktur Folder

```
apotek_final/
│
├── 📄 index.php                 → Halaman login
├── 📄 dashboard.php             → Dashboard admin
├── 📄 koneksi.php               → Konfigurasi database
├── 📄 auth.php                  → Proteksi session semua halaman
├── 📄 sidebar.php               → Komponen sidebar (admin)
├── 📄 logout.php                → Proses logout
│
├── 📄 pasien_dashboard.php      → Dashboard portal pasien
├── 📄 pasien_riwayat.php        → Riwayat pemberian obat (pasien)
├── 📄 pasien_profil.php         → Edit profil pasien
│
├── 📁 css/
│   ├── style.css                → Stylesheet utama (749 baris)
│   └── sidebar_extra.css        → Sidebar dark + foto background
│
├── 📁 obat/
│   ├── index.php                → Daftar obat + stat stok
│   ├── tambah.php               → Form tambah obat
│   ├── edit.php                 → Form edit obat
│   └── hapus.php                → Proses hapus obat
│
├── 📁 pasien/
│   ├── index.php                → Daftar pasien + status akun
│   ├── tambah.php               → Form tambah pasien + buat akun
│   ├── edit.php                 → Form edit + ubah password login
│   └── hapus.php                → Proses hapus pasien
│
├── 📁 transaksi/
│   ├── index.php                → Daftar transaksi pemberian obat
│   ├── tambah.php               → Form catat pemberian + cek stok
│   ├── edit.php                 → Form edit + auto-adjust stok
│   └── hapus.php                → Proses hapus + kembalikan stok
│
├── 📁 users/
│   └── create_admin.php         → Utility tambah akun admin baru
│
└── 📁 sql/
    └── db_apotek.sql            → File database lengkap
```

---

## 🗄️ Struktur Database

**Database:** `db_apotek`

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_user` | INT(11) PK AI | ID user |
| `username` | VARCHAR(50) UNIQUE | Username login |
| `password` | VARCHAR(255) | Hash bcrypt |
| `role` | ENUM('admin','pasien') | Peran user |
| `nama_lengkap` | VARCHAR(100) | Nama lengkap |

### Tabel `obat`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_obat` | INT(11) PK AI | ID obat |
| `nama_obat` | VARCHAR(100) | Nama obat |
| `kategori` | VARCHAR(50) | Kategori obat |
| `stok` | INT(11) | Jumlah stok |
| `harga` | DECIMAL(10,2) | Harga per satuan |
| `tanggal_expired` | DATE | Tanggal kadaluarsa |

### Tabel `pasien`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_pasien` | INT(11) PK AI | ID pasien |
| `id_user` | INT(11) FK nullable | Link ke akun login |
| `nama_pasien` | VARCHAR(100) | Nama pasien |
| `alamat` | TEXT | Alamat |
| `no_hp` | VARCHAR(20) | Nomor HP |
| `tanggal_lahir` | DATE | Tanggal lahir |

### Tabel `pemberian_obat`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_transaksi` | INT(11) PK AI | ID transaksi |
| `id_pasien` | INT(11) FK | Pasien penerima |
| `id_obat` | INT(11) FK | Obat yang diberikan |
| `tanggal_pemberian` | DATE | Tanggal transaksi |
| `jumlah` | INT(11) | Jumlah obat |
| `dosis` | VARCHAR(100) | Aturan pakai |
| `keterangan` | TEXT nullable | Catatan tambahan |

---

## 🚀 Instalasi di XAMPP (Localhost)

### Langkah 1 — Letakkan folder
Extract `apotek_final/` ke:
```
C:\xampp\htdocs\apotek_final\
```

### Langkah 2 — Jalankan XAMPP
1. Buka **XAMPP Control Panel**
2. Klik **Start** pada **Apache**
3. Klik **Start** pada **MySQL**
4. Pastikan keduanya **hijau**

### Langkah 3 — Import Database
1. Buka browser → `http://localhost/phpmyadmin`
2. Klik **New** → buat database `db_apotek`
3. Pilih database `db_apotek` → tab **Import**
4. Klik **Choose File** → pilih `sql/db_apotek.sql`
5. Klik **Go** → tunggu selesai

### Langkah 4 — Konfigurasi Koneksi
Buka `koneksi.php`, sesuaikan jika perlu:
```php
define('DB_HOST',  'localhost');   // host database
define('DB_USER',  'root');        // username (XAMPP default: root)
define('DB_PASS',  '');            // password (XAMPP default: kosong)
define('DB_NAME',  'db_apotek');   // nama database
define('APP_NAME', 'Apotek Sehat'); // nama aplikasi
```

### Langkah 5 — Akses Aplikasi
Buka browser → `http://localhost/apotek_final/`

---

## 🌐 Instalasi di Hosting (InfinityFree / cPanel)

### Langkah 1 — Upload File
Upload seluruh isi folder `apotek_final/` ke `public_html/`

### Langkah 2 — Buat Database di cPanel
1. Login cPanel → **MySQL Databases**
2. Buat database baru (catat namanya)
3. Buat MySQL user → assign ke database (All Privileges)

### Langkah 3 — Import Database
1. Buka **phpMyAdmin** dari cPanel
2. Pilih database yang baru dibuat
3. Tab **Import** → pilih `sql/db_apotek.sql` → klik **Go**

### Langkah 4 — Update Koneksi
Edit `koneksi.php` sesuai data hosting:
```php
define('DB_HOST',  'sql123.infinityfree.com'); // host dari cPanel
define('DB_USER',  'if0_XXXXXXX');             // username database
define('DB_PASS',  'passwordmu');              // password database
define('DB_NAME',  'if0_XXXXXXX_db_apotek');  // nama database lengkap
```

---

## 🔐 Akun Default

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Admin | `eko` | `1234` |

> ⚠️ **Segera ganti password** setelah pertama kali login!

### Cara Tambah Admin Baru
Akses: `http://localhost/apotek_final/users/create_admin.php`

---

## ✅ Fitur Lengkap

### 👨‍💼 Role Admin
- Login & Logout dengan session PHP
- Dashboard statistik: total obat, pasien, transaksi, stok habis
- Transaksi terbaru & alert obat hampir expired (30 hari)
- **CRUD Data Obat** — tambah, lihat, edit, hapus + badge stok berwarna
- **CRUD Data Pasien** — tambah sekaligus buat akun login pasien
- **Edit Pasien** — ubah data + **ubah password login** dengan validasi real-time
- **CRUD Pemberian Obat** — stok otomatis berkurang/bertambah
- Search & filter di semua modul
- Akses Cepat (Quick Links) di dashboard

### 🧑‍🤒 Role Pasien
- Portal pasien dengan sidebar dark terpisah
- Dashboard: statistik kunjungan & obat diterima
- Informasi pribadi (nama, TTL, HP, alamat)
- Riwayat pemberian obat dengan search
- Edit profil (HP & alamat)

### 🎨 UI / UX
- **Font Awesome 6.5** — semua icon profesional
- **Inter** — Google Fonts modern
- Sidebar **dark navy + foto background apotek** (Unsplash)
- Login page **full background foto rak obat**
- Tombol **pill radius** (border-radius penuh)
- Stat card dengan **icon berwarna**
- Warna aksen **Emerald Green #10b981**
- Responsive design (desktop & mobile)
- Animasi `fadeUp` halus saat load
- Toast / alert berwarna (success, danger, warning)
- Empty state dengan icon

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|-------------|
| SQL Injection | Prepared Statements `bind_param()` di semua query |
| Password | `password_hash()` bcrypt + `password_verify()` |
| Session | Timeout otomatis 2 jam tidak aktif |
| XSS | `htmlspecialchars()` di semua output HTML |
| Akses | `auth.php` di-include di setiap halaman |
| Role | Redirect otomatis sesuai role (admin/pasien) |

---

## 🛠️ Troubleshooting

| Error | Penyebab | Solusi |
|-------|----------|--------|
| **404 Not Found** | Folder salah / Apache tidak jalan | Pastikan nama folder benar & Apache running (hijau) |
| **Database Error** | MySQL tidak jalan / DB belum import | Start MySQL di XAMPP, import `sql/db_apotek.sql` |
| **CSS tidak muncul** | Cache browser | Clear cache: `Ctrl + Shift + Del` atau `Ctrl + F5` |
| **Foto sidebar tidak muncul** | Tidak ada koneksi internet | Foto dari Unsplash, butuh internet. Untuk offline ganti URL dengan foto lokal |
| **Logout otomatis** | Session timeout 2 jam | Normal. Ubah nilai di `auth.php` jika perlu |
| **Password salah** | Hash bcrypt vs plain text | Gunakan `users/create_admin.php` untuk buat ulang akun |

---

## 📝 Catatan Penting

### Foto Background Sidebar & Login
Foto diambil dari **Unsplash** (gratis, bebas komersial):
- **Sidebar admin & pasien:** `photo-1584308666744` (stethoscope / alat medis)
- **Login page:** `photo-1587854692152` (rak obat apotek)

Jika tidak ada koneksi internet, ganti URL foto di:
- `css/sidebar_extra.css` → `.sidebar::before { background: url('...') }`
- `css/style.css` → `.login-page::before { background: url('...') }`

Dengan path foto lokal, contoh:
```css
background: url('../img/sidebar-bg.jpg') center/cover no-repeat;
```

### Mengganti Nama Aplikasi
Edit `koneksi.php`:
```php
define('APP_NAME', 'Nama Apotek Anda');
```

### Mengganti Warna Aksen
Edit variabel di `css/style.css`:
```css
:root {
    --green:    #10b981;  /* ganti ke warna yang diinginkan */
    --green-dk: #059669;
}
```

---

## 📊 Ringkasan File

| Kategori | Jumlah File |
|----------|-------------|
| PHP (halaman utama) | 7 file |
| PHP (modul obat) | 4 file |
| PHP (modul pasien) | 4 file |
| PHP (modul transaksi) | 4 file |
| PHP (utility) | 1 file |
| CSS | 2 file |
| SQL | 1 file |
| **Total** | **23 file** |

---

## 🎓 Identitas Proyek

| | |
|---|---|
| **Nama Aplikasi** | Apotek Sehat |
| **Versi** | 2.0.0 (Redesign) |
| **Kategori** | Sistem Informasi Manajemen Apotek |
| **Teknologi** | PHP Native, MySQL, HTML, CSS, JavaScript |
| **Icon** | Font Awesome 6.5 |
| **Font** | Inter (Google Fonts) |
| **Server** | Apache (XAMPP / InfinityFree / cPanel) |
| **PHP** | 8.2+ |
| **Database** | MariaDB 10.4+ / MySQL 5.7+ |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **tugas akademik / pembelajaran**.
Bebas digunakan dan dimodifikasi untuk keperluan edukasi.

Foto background dari **Unsplash** — gratis untuk penggunaan komersial dan non-komersial.
Font **Inter** — SIL Open Font License.
Icon **Font Awesome 6** — Free tier (CC BY 4.0).

---

**Selamat Menggunakan Apotek Sehat!**