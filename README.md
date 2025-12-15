# Sistem Helpdesk

Sistem Helpdesk berbasis web untuk manajemen tiket dukungan dengan fitur multi-pengguna (Admin dan User).

## Fitur

- **Autentikasi Pengguna**
  - Login/Logout
  - Registrasi pengguna baru
  - Reset password (coming soon)

- **Manajemen Tiket**
  - Buat tiket baru
  - Lihat daftar tiket
  - Filter tiket berdasarkan status/kategori
  - Tambahkan komentar ke tiket
  - Update status tiket (Open/Diproses/Selesai)

- **Admin Dashboard**
  - Access the admin dashboard at: `http://localhost/helpdesk/admin/`
  - Tinjauan statistik tiket
  - Kelola pengguna
  - Kelola kategori tiket
  - Laporan (coming soon)

- **Antarmuka Pengguna**
  - Desain responsif (mobile-friendly)
  - Notifikasi real-time (coming soon)
  - Pencarian tiket
  - Ekspor data (coming soon)

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru / MariaDB 10.3 atau lebih baru
- Web server (Apache/Nginx)
- Composer (untuk dependency management)
- Node.js & NPM (untuk aset frontend)

## Instalasi

1. **Clone Repository**
   ```bash
   git clone [URL_REPOSITORY]
   cd helpdesk
   ```

2. **Instal Dependensi**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi**
   - Salin file `.env.example` menjadi `.env`
   - Sesuaikan pengaturan database di file `.env`
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=helpdesk
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Key Aplikasi**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database**
   ```bash
   php artisan migrate --seed
   ```
   
   Ini akan membuat tabel yang diperlukan dan menambahkan user admin default:
   - Email: admin@example.com
   - Password: admin123

6. **Compile Aset**
   ```bash
   npm run dev
   # atau untuk production
   npm run build
   ```

7. **Storage Link**
   ```bash
   php artisan storage:link
   ```

8. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```
   Buka http://localhost:8000 di browser Anda.

## Penggunaan

### Admin
1. Login menggunakan kredensial admin
2. Dashboard menampilkan ringkasan statistik
3. Kelola tiket dari menu Tiket
4. Kelola pengguna dari menu Pengguna
5. Kelola kategori dari menu Kategori

### Pengguna Biasa
1. Daftar akun baru atau login
2. Buat tiket baru dari menu "Buat Tiket"
3. Lacak status tiket di "Daftar Tiket Saya"
4. Tambahkan komentar ke tiket yang ada

## Struktur Direktori

```
helpdesk/
├── app/                 # File inti aplikasi
├── bootstrap/           # File bootstrap
├── config/              # File konfigurasi
├── database/            # Migrasi dan seed database
├── public/              # Direktori publik
├── resources/
│   ├── js/             # File JavaScript
│   ├── sass/           # File CSS/SASS
│   └── views/          # File tampilan
├── routes/             # File rute
└── storage/            # File yang diunggah dan cache
```

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur/namafitur`)
3. Commit perubahan (`git commit -am 'Menambahkan fitur'`)
4. Push ke branch (`git push origin fitur/namafitur`)
5. Buat Pull Request

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## Kontak

Untuk pertanyaan atau masukan, silakan hubungi [email@example.com](mailto:email@example.com).
