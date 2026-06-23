# SEGreens Backend

Backend SEGreens adalah aplikasi `Laravel 12` untuk menjalankan API mobile dan admin panel operasional toko.

Dokumen ini menjelaskan isi project, struktur folder, teknologi yang dipakai, dan dokumen mana yang perlu dibaca terlebih dahulu.

## Fungsi Backend

Backend ini mencakup:

- autentikasi mobile
- verifikasi email
- reset password berbasis OTP
- profile dan multi alamat pengguna
- kategori dan produk
- cart dan checkout
- order management
- integrasi pembayaran Midtrans
- notifikasi email
- admin panel untuk pengelolaan data

## Teknologi yang Digunakan

- `PHP 8.4`
- `Laravel 12`
- `Filament 5`
- `Laravel Sanctum`
- `MySQL` untuk database utama client
- `Vite` dan `Tailwind CSS`
- `Midtrans`
- `SMTP`

## Struktur Folder Penting

- `app/`
  - kode utama aplikasi
  - controller, service, model, enum, policy, job, notification
- `config/`
  - konfigurasi app, database, queue, mail, payment, sanctum
- `database/`
  - migration, factory, seeder
- `docker/`
  - konfigurasi container yang tersedia di project
- `lang/`
  - terjemahan panel admin
- `public/`
  - file public, build asset, dan `openapi.json`
- `resources/`
  - source CSS, JS, dan view
- `routes/`
  - route API, web, dan console
- `scripts/`
  - script utilitas internal project
- `storage/`
  - file upload, log, dan cache runtime

## Dokumen Utama

- `QUICKSTART.md`
  - panduan instalasi dan menjalankan aplikasi di Windows
- `ACCOUNTS.md`
  - akun default, API key lokal, URL, dan catatan handover

## URL Default Lokal

- API: `http://127.0.0.1:8000`
- Admin Panel: `http://127.0.0.1:8000/admin`
- OpenAPI JSON: `http://127.0.0.1:8000/openapi.json`

## Catatan Operasional

- buat `backend/.env` dari `backend/.env.example` sebelum menjalankan aplikasi
- Untuk client Windows, gunakan `QUICKSTART.md` sebagai panduan utama.
- Untuk login default dan data akses, cek `ACCOUNTS.md`.
