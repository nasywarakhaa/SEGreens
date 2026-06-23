# Accounts dan Data Akses

Dokumen ini berisi data akses default untuk kebutuhan setup lokal dan handover.

## 1. URL Penting

Lokal:

- API: `http://127.0.0.1:8000`
- Admin Panel: `http://127.0.0.1:8000/admin`
- OpenAPI JSON: `http://127.0.0.1:8000/openapi.json`

Live terakhir:

- API/Admin: `http://129.212.226.135:8080`

## 2. Akun Default

### Superuser

- email: `superuser@segreens.test`
- password: `password`

### Admin

- email: `admin@segreens.test`
- password: `password`

### User

- email: `user@segreens.test`
- password: `password`

### User Belum Verifikasi

- email: `unverified@segreens.test`
- password: `password`

## 3. API Key Lokal

Untuk request API mobile lokal, gunakan header:

```text
X-API-KEY: local-mobile-key
```

## 4. Midtrans Sandbox

Konfigurasi testing yang saat ini terpasang:

- Merchant ID: `M247965230`
- Client Key: `Mid-client-iN90t8puSmdLRqyK`
- Mode production: `false`

Masukkan Server Key dan konfigurasi lengkap ke `backend/.env` setelah file `.env` dibuat dari `.env.example`.

## 5. SMTP yang Aktif Saat Handover

Konfigurasi SMTP yang aktif saat handover menggunakan:

- provider/host: `smtp.sendgrid.net`
- port: `2525`
- username: `apikey`
- from address: `faharysa@gmail.com`
- from name: `SEGreens`

Secret SMTP tidak disertakan di repo. Setelah membuat `backend/.env`, isi credential yang sesuai environment yang akan dipakai.

## 6. Catatan Handover

Setelah client mulai menggunakan sistem, disarankan untuk segera mengganti:

- password akun admin default
- API key mobile bila diperlukan
- password database bila pindah environment
- kredensial SMTP bila akan dipakai untuk produksi
- kredensial Midtrans bila pindah ke mode production


tdyi eaed hzvo nodp