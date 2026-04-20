# TODO_PERABIKAN

## Daftar Semua Perbaikan
- Valuasi stok per lokasi di laporan stok menggunakan `product_prices.cost_price` dengan fallback `products.cost_price`.
- Menghapus side effect update di GET pada `ReceivableController@index`.
- Menambahkan lock stok saat cek dan potong stok penjualan menggunakan `lockForUpdate()`.
- Menambahkan validasi diskon pembelian agar tidak melebihi subtotal.
- Akses lintas lokasi untuk Manager di modul piutang/hutang memakai `accessibleLocationIds()`.
- Otorisasi lokasi pada `StockTransferController@send` dan `@receive`.
- Konsistensi penggunaan `posted_at` untuk filter tanggal laporan sales dan cash-up.
- Standardisasi Tailwind v4 dengan `@import "tailwindcss";` dan penghapusan `tailwind.config.js`.
- Pindahkan query dashboard ke `DashboardController` + `DashboardService`.
- Ekstrak query laporan ke `ReportService` dan gunakan dependency injection di `ReportController`.
- Tambahkan util `LocationResolver` untuk menghapus duplikasi logika pemilihan lokasi di controller.
- Memperbaiki ambiguity `location_id` pada query laporan stok.
- Penyesuaian akses `ReceivableController@show/store` dan `PurchasePayableController@show/store` agar tidak terblokir global scope.
- Menambahkan test laporan stok per lokasi.
- Menambahkan test laporan sales/cash-up berbasis `posted_at`.
- Menambahkan test akses lintas lokasi untuk piutang/hutang.
- Menambahkan test otorisasi lokasi pada transfer stok.
- Menambahkan test penolakan penjualan saat stok tidak cukup.
- Menambahkan unit test `AccountingService::createJournal` untuk jurnal tidak balance.
- Perbaikan test Purchase: menambah `payment_method` dan seeding akun.
- Perbaikan test Sale: seeding akun dan redirect ke struk.

## UI/UX Per Halaman (Urutan Sidebar)
- Dashboard: empty state lokasi, quick actions. (SELESAI)
- Lokasi: tandai lokasi aktif dan lindungi lokasi pusat dari hapus. (SELESAI)
- Pilih Lokasi: tampilkan info lokasi aktif saat ini. (SELESAI)
- Pengguna: cegah hapus user sendiri, tampilkan label "Anda". (SELESAI)
- Akses Lokasi Manager: tampilkan jumlah lokasi per manager. (SELESAI)
- Produk: tambahkan informasi status lebih jelas pada list. (SELESAI)
- Kategori: empty state dengan CTA. (SELESAI)
- Satuan: empty state dengan CTA. (SELESAI)
- Penyesuaian Stok: empty state dengan CTA. (SELESAI)
- Transfer Stok: tampilkan badge status lebih jelas. (SELESAI)
- Pembelian: empty state dengan CTA. (SELESAI)
- Pelunasan Hutang: ringkasan status sederhana. (SELESAI)
- Pelunasan Piutang: ringkasan status sederhana. (SELESAI)
- Biaya Operasional: empty state dengan CTA. (SELESAI)
- Supplier: empty state dengan CTA. (SELESAI)
- Penjualan (POS): gunakan `posted_at` saat tersedia. (SELESAI)
- Laporan Penjualan: tampilkan `posted_at` saat tersedia. (SELESAI)
- Laporan Kas Harian: ringkasan total tetap jelas. (SELESAI)
- Laporan Laba Rugi: tampilkan tanggal filter aktif. (SELESAI)
- Laporan Arus Kas: tampilkan tanggal filter aktif. (SELESAI)
- Laporan Stok: tampilkan total value dan lokasi aktif. (SELESAI)
- Kartu Stok: tampilkan ringkasan periode. (SELESAI)
- Profile: tampilkan role dan lokasi aktif. (SELESAI)
