<?php

return [
    'navigation' => [
        'groups' => [
            'access' => 'Akses',
            'catalog' => 'Katalog',
            'sales' => 'Penjualan',
            'configuration' => 'Konfigurasi',
        ],
        'resources' => [
            'users' => [
                'navigation' => 'Pengguna',
                'singular' => 'Pengguna',
                'plural' => 'Pengguna',
            ],
            'user_addresses' => [
                'navigation' => 'Alamat Pengguna',
                'singular' => 'Alamat Pengguna',
                'plural' => 'Alamat Pengguna',
            ],
            'stores' => [
                'navigation' => 'Toko',
                'singular' => 'Toko',
                'plural' => 'Toko',
            ],
            'product_categories' => [
                'navigation' => 'Kategori Produk',
                'singular' => 'Kategori Produk',
                'plural' => 'Kategori Produk',
            ],
            'products' => [
                'navigation' => 'Produk',
                'singular' => 'Produk',
                'plural' => 'Produk',
            ],
            'orders' => [
                'navigation' => 'Pesanan',
                'singular' => 'Pesanan',
                'plural' => 'Pesanan',
            ],
            'system_settings' => [
                'navigation' => 'Pengaturan Sistem',
                'singular' => 'Pengaturan Sistem',
                'plural' => 'Pengaturan Sistem',
            ],
        ],
    ],

    'common' => [
        'not_available' => '-',
    ],

    'locale' => [
        'switch' => 'Ganti bahasa',
        'indonesian' => 'Bahasa Indonesia',
        'english' => 'English',
    ],

    'map' => [
        'search_placeholder' => 'Cari alamat',
        'search_button' => 'Cari',
        'searching' => 'Mencari...',
        'choose_result' => 'Pilih alamat dari hasil pencarian.',
        'no_results' => 'Alamat tidak ditemukan.',
        'search_failed' => 'Pencarian alamat gagal. Coba lagi.',
        'reverse_failed' => 'Gagal memperbarui alamat dari pin peta.',
        'selected' => 'Alamat dipilih.',
        'hint' => 'Klik peta atau geser pin untuk mengisi koordinat. Alamat akan diisi otomatis.',
    ],

    'store' => [
        'fields' => [
            'logo' => 'Logo',
            'name' => 'Nama Toko',
            'description' => 'Deskripsi',
            'phone_number' => 'Nomor Telepon',
            'open_time' => 'Jam Buka (WIB)',
            'close_time' => 'Jam Tutup (WIB)',
            'service_radius_m' => 'Radius Layanan (meter)',
            'base_delivery_fee' => 'Ongkir Dasar (Rp)',
            'address' => 'Alamat',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'location_map' => 'Peta Lokasi',
            'service_radius_short' => 'Radius layanan (m)',
            'base_delivery_fee_short' => 'Biaya antar dasar',
        ],
        'helpers' => [
            'latitude_decimal' => 'Gunakan titik untuk desimal, contoh: -6.2',
            'longitude_decimal' => 'Gunakan titik untuk desimal, contoh: 106.8166667',
        ],
    ],

    'user_address' => [
        'fields' => [
            'user' => 'Pengguna',
            'label' => 'Label Alamat',
            'recipient_name' => 'Nama Penerima',
            'phone_number' => 'Nomor Telepon',
            'address' => 'Alamat',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'location_map' => 'Peta Lokasi',
            'address_note' => 'Catatan Alamat',
            'postal_code' => 'Kode Pos',
            'is_default' => 'Alamat Utama',
            'is_active' => 'Aktif',
        ],
    ],

    'orders' => [
        'fields' => [
            'customer' => 'Pelanggan',
            'address' => 'Alamat',
            'user' => 'Pengguna',
            'store' => 'Toko',
            'user_address' => 'Alamat pengguna',
            'product' => 'Produk',
            'order_date' => 'Tanggal pesanan',
            'cancel_reason' => 'Alasan pembatalan',
        ],
        'filters' => [
            'order_date' => 'Tanggal Pesanan',
            'order_date_from' => 'Dari tanggal',
            'order_date_until' => 'Sampai tanggal',
        ],
        'actions' => [
            'group' => 'Ubah Status',
            'update_status' => 'Ubah ke Status Berikutnya',
            'detail' => 'Detail',
        ],
        'messages' => [
            'update_status_heading' => 'Perbarui status pesanan',
            'update_status_description' => 'Status akan diubah ke :status.',
            'status_not_updatable' => 'Status tidak dapat diperbarui',
            'payment_not_paid' => 'Status pembayaran belum lunas',
            'payment_required' => 'Pesanan hanya bisa diproses setelah status pembayaran Sudah bayar.',
            'status_updated' => 'Status pesanan diperbarui',
            'status_updated_body' => 'Status sekarang: :status',
            'status_update_failed' => 'Gagal memperbarui status',
        ],
    ],

    'products' => [
        'fields' => [
            'category' => 'Kategori',
            'sku' => 'SKU',
            'weight' => 'Berat',
            'weight_unit' => 'Satuan berat',
            'sort_order' => 'Urutan',
        ],
        'filters' => [
            'price_range' => 'Rentang Harga',
            'price_min' => 'Harga minimum',
            'price_max' => 'Harga maksimum',
        ],
    ],

    'product_categories' => [
        'fields' => [
            'icon' => 'Ikon',
            'image' => 'Gambar',
        ],
    ],

    'users' => [
        'fields' => [
            'email' => 'Alamat email',
            'username' => 'Username',
            'is_email_verified' => 'Email terverifikasi',
            'role' => 'Peran',
            'status' => 'Status',
            'password' => 'Password baru',
        ],
        'helpers' => [
            'email_already_verified' => 'Email ini sudah terverifikasi.',
            'toggle_to_verify_email' => 'Nyalakan untuk membantu verifikasi email pengguna.',
            'password_optional_on_edit' => 'Kosongkan jika tidak ingin mengubah password.',
        ],
    ],

    'system_settings' => [
        'fields' => [
            'group_name' => 'Nama Grup',
            'key_name' => 'Nama Kunci',
            'label' => 'Label',
            'value' => 'Nilai',
            'type' => 'Tipe',
            'is_encrypted' => 'Terenkripsi',
            'is_active' => 'Aktif',
        ],
        'filters' => [
            'group_name' => 'Grup',
        ],
        'types' => [
            'string' => 'Teks',
            'integer' => 'Angka Bulat',
            'boolean' => 'Boolean',
            'json' => 'JSON',
            'password' => 'Password',
        ],
    ],

    'integrations' => [
        'actions' => [
            'test_smtp' => 'Tes SMTP',
            'test_fcm' => 'Tes FCM',
        ],
        'fields' => [
            'smtp_to_email' => 'Email tujuan',
            'fcm_device_token' => 'Token perangkat FCM',
            'fcm_title' => 'Judul notifikasi',
            'fcm_body' => 'Isi notifikasi',
        ],
        'helpers' => [
            'fcm_device_token' => 'Kosongkan jika hanya ingin validasi konfigurasi tanpa kirim push.',
        ],
        'messages' => [
            'smtp_not_configured' => 'SMTP belum dikonfigurasi.',
            'smtp_target_required' => 'Email tujuan wajib diisi.',
            'smtp_test_sent' => 'Email tes SMTP berhasil dikirim.',
            'smtp_test_failed' => 'Tes SMTP gagal.',
            'fcm_config_valid' => 'Konfigurasi FCM valid.',
            'fcm_test_sent' => 'Notifikasi tes FCM berhasil dikirim.',
            'fcm_test_failed' => 'Tes FCM gagal.',
        ],
    ],

    'enums' => [
        'user_role' => [
            'superuser' => 'Superuser',
            'admin' => 'Admin',
            'user' => 'User',
        ],
        'user_status' => [
            'active' => 'Aktif',
            'inactive' => 'Tidak aktif',
            'banned' => 'Diblokir',
        ],
        'order_status' => [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'packed' => 'Dikemas',
            'on_delivery' => 'Dalam pengiriman',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ],
        'payment_status' => [
            'unpaid' => 'Belum bayar',
            'paid' => 'Sudah bayar',
            'failed' => 'Gagal',
            'refunded' => 'Dikembalikan',
        ],
        'cart_status' => [
            'active' => 'Aktif',
            'checked_out' => 'Checkout',
            'abandoned' => 'Ditinggalkan',
        ],
        'fulfillment_type' => [
            'delivery' => 'Delivery',
            'pickup' => 'Pickup',
        ],
    ],

    'dashboard' => [
        'title' => 'Dashboard Operasional',
        'subheading' => 'Pantau performa harian dan proses pesanan aktif langsung dari satu halaman.',
        'quick_actions' => [
            'heading' => 'Aksi Cepat Pesanan',
            'description' => 'Jalankan alur operasional pesanan tanpa pindah ke halaman detail.',
            'active_orders' => ':count pesanan aktif',
            'columns' => [
                'order' => 'Pesanan',
                'customer' => 'Pelanggan',
                'date' => 'Tanggal',
                'total' => 'Total',
                'status' => 'Status',
                'payment' => 'Pembayaran',
                'actions' => 'Aksi',
            ],
            'empty' => 'Tidak ada pesanan yang perlu diproses saat ini.',
            'total' => 'Total',
            'next_status' => 'Ubah ke :status',
            'processing' => 'Memproses...',
            'waiting_payment' => 'Menunggu pembayaran',
            'no_action' => 'Tidak ada aksi lanjutan',
            'cancel' => 'Batalkan',
            'detail' => 'Detail',
            'cancel_confirm' => 'Yakin ingin membatalkan pesanan ini?',
            'cancel_modal_heading' => 'Batalkan Pesanan',
            'cancel_modal_description' => 'Isi alasan pembatalan pesanan ini.',
            'cancel_modal_submit' => 'Simpan Pembatalan',
            'cancel_reason_placeholder' => 'Contoh: Stok habis / permintaan pelanggan',
            'cancelled_note' => 'Dibatalkan dari dashboard admin.',
            'status_not_allowed' => 'Status tidak bisa diubah',
            'status_updated' => 'Status pesanan diperbarui',
            'status_updated_body' => 'Status sekarang: :status',
            'update_failed' => 'Gagal memperbarui status',
            'cancel_blocked' => 'Pesanan tidak bisa dibatalkan',
            'cancel_success' => 'Pesanan dibatalkan',
            'cancel_failed' => 'Gagal membatalkan pesanan',
        ],
        'trend' => [
            'heading' => 'Tren Pesanan 7 Hari',
            'description' => 'Perbandingan jumlah pesanan masuk dan pesanan yang sudah lunas.',
            'total_label' => 'Pesanan Masuk',
            'paid_label' => 'Pesanan Lunas',
        ],
    ],

    'stats' => [
        'users' => 'Pengguna',
        'users_description' => 'Total pengguna terdaftar',
        'categories' => 'Kategori',
        'categories_description' => 'Grup katalog aktif',
        'products' => 'Produk',
        'products_description' => 'Data produk tersedia',
        'pending_orders' => 'Pesanan Pending',
        'pending_orders_description' => 'Pesanan yang menunggu diproses',
        'revenue_today' => 'Pendapatan Hari Ini',
        'revenue_today_description' => 'Total order lunas hari ini',
        'completed_orders' => 'Order Selesai',
        'completed_orders_description' => 'Total pesanan selesai',
    ],
];
