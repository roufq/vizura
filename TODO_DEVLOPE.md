FASE 1: MVP Operasional Multi-Lokasi (POS + Stok)
Tujuan: aplikasi siap dipakai UMKM untuk jual-beli multi-lokasi dengan data konsisten dan aman.

1) Fondasi Akses & Lokasi
- [x] Auth + session hardening (Breeze/Jetstream) + Spatie Permission; seed peran Owner, Manager, Kasir dengan matriks aksi create/update/delete/approve.
- [x] Lokasi (CRUD) dengan kode unik, status aktif/nonaktif, dan pemilihan active_location per sesi.
- [ ] Enforce location scope: semua tabel transaksi wajib location_id (not null, FK); global/local scope membatasi query berdasar active_location; superuser bypass hanya jika eksplisit; middleware menolak aksi tanpa active_location; feature test lintas lokasi wajib.
- [ ] Audit log minimum: login/logout, perubahan stok, penjualan void; simpan user_id, location_id, timestamp.

2) Master Data & Stok
- [x] Produk: SKU unik (global), nama, kategori, harga jual, HPP, pajak/PPN flag, satuan dasar; validasi SKU duplikat; soft delete aman.
- [x] Harga per lokasi (opsional): jika butuh override harga jual/HPP per lokasi, simpan di tabel terpisah dengan key (location_id, product_id) untuk hindari tercampur.
- [x] Status produk: aktif/nonaktif; opsi blok penjualan jika stok nol; siapkan kolom batch/expiry (opsional F&B).
- [x] Satuan: satuan dasar wajib; jika ada konversi (dus â†’ pcs), definisikan faktor dan validasi konsisten.
- [x] Stok per lokasi: tabel stock_items (location_id, product_id, qty on hand) dengan unique index; inisialisasi nol saat produk baru; guard transaksi supaya tidak minus; semua pergerakan stok referensi location_id.
- [x] Penyesuaian stok: modul stock adjustment dengan alasan, bukti, approval; tulis ke audit log.
- [x] Transfer stok (wajib): flow draft -> dikirim -> diterima; stok berkurang saat kirim, bertambah saat terima; nomor dokumen unik; catat source/destination location_id, audit trail; idealnya kirim/terima oleh user berbeda.
- [x] Pembelian & HPP: modul penerimaan barang + supplier; metode biaya rata-rata bergerak (tetap); update HPP dan stok per lokasi; tangani diskon/pajak pembelian; siapkan reprocess untuk retur pembelian.

3) Penjualan (POS)
- [x] POS cepat: pencarian produk, pembayaran (definisikan single vs multi-tender), diskon baris & order (aturan prioritas), pajak (inklusif/eksklusif); validasi stok cukup sebelum commit; kasir terkunci ke active_location.
- [x] Draft vs post: stok hanya berkurang saat post; draft tidak memengaruhi stok.
- [x] Posting stok & log transaksi: setiap penjualan kurangi stok lokasi (location_id = active_location saat post), simpan transaction log detail (product_id, qty, price, discount, tax, method, status, location_id).
- [x] Void/retur terkontrol: hanya peran berizin, stok dikembalikan, log audit tercatat; retur referensi invoice dan batas waktu.

4) Pelaporan Dasar
- [x] Laporan penjualan per lokasi dengan filter tanggal, metode bayar, user kasir; tampilkan gross/nett, diskon, pajak, status (post/void/retur); default filter ke active_location, agregat multi-lokasi hanya untuk peran berizin.
- [x] Laporan kas harian (cash-up) per lokasi: total penerimaan per metode bayar vs setoran (setoran menunggu modul).
- [x] Laporan stok real-time per lokasi + konsolidasi; tampilkan nilai persediaan (qty x HPP) dan stok kritis (reorder point per lokasi); selalu segmentasi location_id (stok kritis menunggu field).
- [x] Laporan pergerakan stok (kartu stok) per produk per lokasi: pembelian, transfer, penjualan, penyesuaian; urut kronologis dengan saldo berjalan dan sumber transaksi; wajib location_id.

FASE 2: Jembatan Akuntansi (Sederhana)
Tujuan: memastikan transaksi operasional tercatat otomatis ke jurnal sehingga laporan laba rugi dan arus kas dapat disajikan akurat.

- [x] Bagan akun dasar dengan kode unik (Kas, Bank, Piutang, Pendapatan, Persediaan, HPP, Biaya Operasional).
- [x] Skema jurnal berpasangan (journals + journal_lines) dengan referensi transaksi (sales_id, purchase_id, transfer_id, expense_id).
- [x] Otomasi jurnal transaksi:
  - Penjualan: Dr Kas/Bank/Piutang, Cr Pendapatan.
  - HPP penjualan: Dr HPP, Cr Persediaan (berdasar HPP rata-rata).
  - Pembelian: Dr Persediaan, Cr Kas/Bank/Hutang; termasuk diskon dan pajak.
- [x] Modul biaya operasional (mis. gaji, sewa, listrik) yang menulis jurnal otomatis.
- [x] Laporan laba rugi dan arus kas dengan filter lokasi dan opsi konsolidasi.

FASE 3: Keamanan, QA, dan Go-Live
- [ ] Pengujian regresi: feature test untuk scope lokasi, stok tidak minus, alur penjualan/retur, pembelian, transfer, laporan.
- [ ] Keamanan: enkripsi .env/secret, HTTPS/TLS, rate limit login, CSRF/XSS audit, rotasi token API, RBAC review berkala.
- [ ] Backup & restore drill: snapshot DB harian, uji restore; log perubahan skema migrasi.
- [ ] Observability: logging terstruktur, alert kegagalan stok/jurnal, health check.
- [ ] Pilot 3-5 UMKM dengan data dummy multi-lokasi; kumpulkan feedback; siapkan onboarding/FAQ sebelum go-live.

Test Anti-Bleed Lokasi (wajib regresi)
- [ ] Active_location wajib: tanpa active_location, endpoint transaksi ditolak; superuser hanya bypass eksplisit.
- [ ] Produk & harga: SKU global bisa diakses, tetapi override harga per lokasi tidak terlihat di lokasi lain.
- [x] Stok per lokasi: create penjualan/pembelian/transfer/penyesuaian di lokasi A tidak mengubah qty lokasi B.
- [ ] POS switch lokasi: pindah active_location sebelum transaksi mengubah stok hanya di lokasi baru; setelah switch, stok lokasi asal tidak berubah.
- [x] Transfer stok: stok berkurang di source, bertambah di destination; tidak ada perubahan di lokasi lain; dokumen unik per transfer.
- [ ] Laporan penjualan: filter default active_location; user non-privileged tidak bisa melihat data lokasi lain; agregat multi-lokasi hanya untuk peran berizin.
- [ ] Laporan stok & kartu stok: setiap baris mengandung location_id; saldo berjalan tidak bercampur antar lokasi.
- [ ] Audit log: semua peristiwa menyertakan location_id; log dari lokasi A tidak tampil jika user filter lokasi B.





