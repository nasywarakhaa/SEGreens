# Quickstart Client Windows

Dokumen ini adalah panduan utama untuk client yang menggunakan Windows.

Tujuannya:

- menjalankan backend SEGreens di lokal
- login ke admin panel
- mencoba API
- memahami file yang perlu diedit bila konfigurasi berubah

Panduan ini menggunakan `MySQL` sebagai default database client.

## 1. Kebutuhan Sistem

Yang perlu tersedia di Windows:

- `Git`
- `PHP 8.4`
- `Composer`
- `Node.js 20 LTS`
- `npm`

Opsional tetapi disarankan:

- `Git Bash` atau `WSL2` untuk menjalankan script Bash
- `XAMPP` untuk MySQL

Catatan:

- Untuk handover client, database yang dipakai adalah `MySQL` dari `XAMPP`.
- Jalankan module `MySQL` dari XAMPP sebelum migration.

## 1.1 Cara Install Composer di Windows

Jika Composer belum terpasang, ikuti langkah berikut:

1. Pastikan PHP sudah tersedia di Windows.
2. Jika memakai XAMPP, lokasi PHP biasanya:

```text
C:\xampp\php\php.exe
```

3. Buka halaman resmi Composer:

```text
https://getcomposer.org/download/
```

4. Download `Composer-Setup.exe`.
5. Jalankan installer Composer.
6. Saat installer meminta lokasi PHP, pilih:

```text
C:\xampp\php\php.exe
```

7. Selesaikan proses install.
8. Tutup PowerShell lama, lalu buka PowerShell baru.
9. Cek apakah Composer sudah aktif:

```powershell
composer -V
php -v
```

Jika `composer` belum dikenali, pastikan path berikut sudah masuk `PATH` Windows:

```text
C:\ProgramData\ComposerSetup\bin
```

## 2. Folder yang Dipakai

Buka folder project, lalu masuk ke backend:

```powershell
cd backend
```

## 3. Install Dependency

Jalankan di PowerShell:

```powershell
composer install
npm install
```

## 4. Cek Konfigurasi `.env`

Buat file `.env` dari `.env.example`.

Di PowerShell:

```powershell
Copy-Item .env.example .env
```

Hal yang perlu dipastikan di `backend/.env`:

- `APP_URL=http://127.0.0.1:8000`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=segreens_client`
- `DB_USERNAME=root`
- `DB_PASSWORD=`
- `QUEUE_CONNECTION=database`
- `MOBILE_API_KEY=local-mobile-key`

Jika ingin generate ulang app key:

```powershell
php artisan key:generate
```

## 5. Siapkan Database MySQL Lokal

1. Buka `XAMPP Control Panel`.
2. Jalankan module `MySQL`.
3. Buat database, misalnya lewat `phpMyAdmin` atau MySQL command line:

```sql
CREATE DATABASE segreens_client;
```

4. Pastikan `backend/.env` berisi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=segreens_client
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migration dan seed:

```powershell
php artisan migrate --seed
```

Jika ingin reset total data lokal:

```powershell
php artisan migrate:fresh --seed
```

## 6. Menjalankan Backend

Buka beberapa terminal PowerShell.

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
php artisan queue:work --tries=3 --timeout=90
```

Terminal 3:

```powershell
npm run dev
```

Jika hanya ingin mode minimal:

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm run dev
```

## 7. URL yang Bisa Dibuka

Setelah backend berjalan:

- API: `http://127.0.0.1:8000`
- Admin Panel: `http://127.0.0.1:8000/admin`
- OpenAPI JSON: `http://127.0.0.1:8000/openapi.json`

## 8. Login Default

Lihat file:

- `ACCOUNTS.md`

Di situ tersedia:

- akun superuser
- akun admin
- akun user
- API key lokal
- URL penting

## 9. Build Asset

Kalau perlu build production lokal:

```powershell
npm run build
```

## 10. Storage Link

Kalau file upload tidak muncul:

```powershell
php artisan storage:link
```

## 11. Testing Dasar

Menjalankan test otomatis:

```powershell
php artisan test
```

Lint syntax PHP:

```powershell
composer run lint:syntax
```

## 12. Email dan Payment

### SMTP

Untuk setup lokal, konfigurasi SMTP ada di `backend/.env`.

Untuk test SMTP:

```powershell
php artisan integrations:test-smtp someone@example.com
```

Konfigurasi SMTP yang dipakai saat handover:

- host: `smtp.sendgrid.net`
- port: `2525`
- username: `apikey`
- from address: `faharysa@gmail.com`
- from name: `SEGreens`

### Midtrans

Konfigurasi Midtrans juga ada di `backend/.env`.

Untuk kebutuhan testing mobile, cek `ACCOUNTS.md`.

## 13. Troubleshooting

### `php` tidak dikenali

Pastikan PHP sudah terpasang dan masuk ke `PATH` Windows.

Kalau memakai PHP dari XAMPP, biasanya lokasinya:

```text
C:\xampp\php
```

Cek dengan:

```powershell
php -v
```

### `composer` tidak dikenali

Pastikan Composer sudah terpasang dan masuk ke `PATH`.

Cek dengan:

```powershell
composer -V
```

### `npm` tidak dikenali

Pastikan Node.js sudah terpasang.

Cek dengan:

```powershell
npm -v
```

### Halaman admin error setelah setup

Jalankan:

```powershell
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize:clear
```

### Upload gambar tidak tampil

Jalankan:

```powershell
php artisan storage:link
```

### MySQL tidak bisa diakses

Pastikan:

- module `MySQL` di XAMPP sudah berjalan
- port `3306` tidak bentrok
- database `segreens_client` sudah dibuat
- `DB_USERNAME=root` dan `DB_PASSWORD=` sesuai konfigurasi MySQL XAMPP

## 14. Urutan Paling Aman untuk Mulai

1. Buka PowerShell.
2. Masuk ke `backend`.
3. Jalankan `composer install`.
4. Jalankan `npm install`.
5. Siapkan database MySQL `segreens_client`.
6. Cek dan sesuaikan `backend/.env`.
7. Jalankan `php artisan migrate --seed`.
8. Jalankan `php artisan serve`.
9. Jalankan `php artisan queue:work --tries=3 --timeout=90`.
10. Jalankan `npm run dev`.
11. Buka `/admin` dan login memakai akun default di `ACCOUNTS.md`.
