# 🍃 Koperasi Digital v2.0

Sistem manajemen koperasi modern berbasis web untuk mengelola data anggota, pinjaman, angsuran, dan manajemen pengguna secara real-time. Dibangun dengan stack teknologi terbaru untuk performa tinggi dan antarmuka yang intuitif.

---

## 🚀 Tech Stack

- **Backend:** Laravel 11+ (PHP 8.2+)
- **Frontend:** Vue.js 3 (Composition API)
- **Inertia.js:** Adhesif antara Laravel & Vue (Single Page Application)
- **Styling:** Tailwind CSS
- **Database:** MySQL / SQLite (Development)

---

## 🛠 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di lingkungan lokal Anda:

### 1. Clone Repositori
```bash
git clone [https://github.com/username/koperasi-digital.git](https://github.com/username/koperasi-digital.git)
cd koperasi-digital
```

2. Instal Dependensi Backend (PHP)
```Bash
composer install
```

3. Instal Dependensi Frontend (Node.js)
```Bash
npm install
```

4. Konfigurasi Environment
Salin file .env.example menjadi .env:
```Bash
cp .env.example .env
```
⚙️ Konfigurasi Utama

Buka file .env dan sesuaikan bagian berikut:
1. Database (Pilih salah satu)

Jika menggunakan MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db_koperasi
DB_USERNAME=root
DB_PASSWORD=
```
Jika menggunakan SQLite (Development):
```
DB_CONNECTION=sqlite
# Kosongkan bagian DB_HOST, DB_PORT, dll untuk SQLite
```

2. App Key

Generate kunci aplikasi untuk keamanan enkripsi:
```Bash
php artisan key:generate
```

3. Migrasi & Seed Data
Jalankan migrasi tabel dan isi data awal (User Admin default):
```Bash
php artisan migrate --seed
```

🏃‍♂️ Menjalankan Aplikasi

Anda perlu menjalankan dua terminal secara bersamaan:

Terminal 1: Server Laravel
```Bash
php artisan serve
```

Terminal 2: Vite (Frontend Compilation)
```Bash
npm run dev
```
Akses aplikasi di browser melalui: http://127.0.0.1:8000

🔑 Akun Default (Seeder)

Jika Anda menjalankan php artisan db:seed, gunakan akun berikut untuk login pertama kali:

    Email: admin@koperasi.com

    Password: Password5us4h

📂 Struktur Penting Proyek

    app/Http/Controllers/: Logika backend (Pinjaman, User, Auth).

    resources/js/Pages/: Komponen UI Vue (Halaman Login, User List, dll).

    routes/web.php: Definisi rute aplikasi.

    database/migrations/: Struktur tabel database.

📝 Catatan Tambahan

    Format Mata Uang: Sistem menggunakan formatIDR() di frontend untuk konsistensi tampilan Rupiah.

    Keamanan: Pastikan selalu menjalankan php artisan config:cache saat melakukan perubahan pada .env di lingkungan produksi.

🤝 Kontribusi & Support

Jika menemukan bug atau ingin menambahkan fitur, silakan buat Pull Request atau hubungi tim pengembang.
