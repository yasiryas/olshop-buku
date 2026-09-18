# Wigati Buku — Online Book Store

Sistem e-commerce buku berbasis Laravel 11 dengan fitur lengkap: katalog produk, keranjang belanja, checkout, manajemen pesanan, pengiriman real-time (AgenWebsite), notifikasi, dan dashboard admin.

---

## 🚀 Fitur Utama

### Frontend (Pembeli)
- **Katalog Buku** — Grid produk, kategori, pencarian, filter
- **Detail Produk** — Gambar, deskripsi, stok, tombol "Tambah ke Keranjang"
- **Keranjang & Checkout** — Quantity selector, alamat tersimpan, pilihan kurir & pembayaran
- **Upload Bukti Transfer** — Setelah checkout, pembeli upload bukti pembayaran
- **Riwayat Pesanan** — Status: Pending → Processing → Shipped → Completed
- **Retur Barang** — Ajukan retur untuk pesanan selesai
- **Notifikasi Real-time** — Bell icon dengan AJAX polling

### Backend (Admin / Owner / Penulis)
- **Dashboard** — Statistik pendapatan, pesanan, grafik bulanan
- **Manajemen Pesanan** — Approve (kurangi stok), Ship (input resi), Complete, Reject
- **Preview Pesanan (Modal)** — Detail lengkap tanpa reload halaman
- **Manajemen Produk** — CRUD buku, kategori, stok, mutasi stok
- **Manajemen Kategori & Artikel/Blog**
- **Pengaturan Toko** — WA kontak, ongkir (manual & AgenWebsite API), metode pembayaran, **Pajak & Asuransi**, ambang stok menipis
- **Laporan & Export** — Export XLSX (Laporan Wigati)
- **Manajemen Staff & Role** — Spatie Laravel Permission

### Sistem
- **Ongkir Real-time** — Integrasi AgenWebsite (J&T, Lion Parcel, SAP, SPX, J&T Cargo) dengan fallback zona manual
- **Pajak & Asuransi** — Konfigurasi persentase di settings, override manual saat approve
- **Mutasi Stok** — Otomatis in/out saat approve, reject, cancel, return
- **PWA Ready** — Service Worker, manifest, install prompt
- **SEO & Schema.org** — JSON-LD Product, WebSite, BookStore

---

## 🛠 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11, PHP 8.2+ |
| Database | MySQL / MariaDB |
| Auth & ACL | Laravel Breeze + Spatie Laravel Permission |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Real-time Shipping | AgenWebsite Rate API |
| Notifikasi | Database + Mail (Notification) |
| Export | PhpSpreadsheet (XLSX) |
| Testing | Pest PHP |
| PWA | Service Worker (Workbox) |

---

## 📦 Instalasi

```bash
# Clone repository
git clone https://github.com/yasiryas/olshop-buku.git
cd olshop-buku

# Install dependencies
composer install
npm install && npm run build

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Storage link
php artisan storage:link

# Jalankan server
php artisan serve
```

### Environment Variables (`.env`)

```env
APP_URL=https://olshop-buku.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=olshop_buku
DB_USERNAME=root
DB_PASSWORD=

# AgenWebsite (opsional - untuk ongkir real-time)
WIGATI_AGENWEB_API_KEY=awk_live_xxxxx
WIGATI_AGENWEB_ORIGIN_CITY_ID=152
WIGATI_AGENWEB_ORIGIN_POSTAL_CODE=55651

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

---

## 🗄 Database Seeder

```bash
# Full seed (12 bulan data demo, pendapatan 100-300jt/bulan)
php artisan db:seed --class=TransactionSeeder

# Atau jalankan semua seeder
php artisan db:seed
```

**Akun Default (setelah seed):**
- Owner: `owner@mail.com` / `password`
- Admin: `admin@mail.com` / `password`
- Penulis: `penulis@mail.com` / `password`
- Buyer: `buyer@mail.com` / `password`

---

## ⚙️ Konfigurasi Penting

### Pajak & Asuransi (Settings → Pajak & Asuransi)
- **Pajak (%)** — Default 11% (PPN)
- **Asuransi (%)** — Default 2.3%
- Bisa diubah manual saat **Approve** pesanan di modal preview

### Ongkir
- **Mode AgenWebsite** — Isi API Key + Kota Asal + Kode Pos → ongkir real-time per kota
- **Mode Zona Manual** — Kosongkan API Key → atur tarif per kurir per kota di "Zona / Tarif per Kota"

### Metode Pembayaran
Tambahkan bank/rekening di Settings → Metode Pembayaran (aktif/nonaktif per metode)

---

## 🧪 Testing

```bash
# Jalankan semua test (Pest)
php artisan test

# Test spesifik
php artisan test --filter=CheckoutFlowTest
php artisan test --filter=FullSmokeTest
```

**Coverage:** 65 test / 266 assertions (FullSmokeTest, CheckoutFlowTest, ProfileTest, RoleAccessTest, SecurityHeadersTest)

---

## 📁 Struktur Proyek Penting

```
app/
├── Http/Controllers/
│   ├── ProductTransactionController.php  # Checkout, approve, ship, complete, reject
│   ├── CartController.php                # Keranjang, rates, locations
│   ├── SettingController.php             # Pengaturan toko
│   └── NotificationController.php        # API notifikasi
├── Models/
│   ├── ProductTransaction.php            # Pesanan (tax_amount, insurance_amount)
│   ├── TransactionDetail.php             # Item pesanan
│   ├── Product.php                       # Buku (stock, mutations)
│   └── ProductReturn.php                 # Retur
├── Support/
│   ├── StoreSettings.php                 # Cache settings (tax, insurance, shipping, payment)
│   ├── AgenWebShipping.php               # Client AgenWebsite API
│   ├── OrderNotifications.php            # Kirim notifikasi pesanan
│   └── WaNotifier.php                    # Generate WhatsApp URL
database/
├── migrations/                           # product_transactions (tax_amount, insurance_amount)
└── seeders/TransactionSeeder.php         # 12 bulan data demo
resources/
├── views/
│   ├── front/                            # Frontend (layout-front, navbar, footer)
│   ├── admin/                            # Backend (app-layout, settings, orders)
│   └── components/                       # notification-bell, confirm-modal, footer-front
└── js/app.js                             # Alpine components (notifBell, cartQty, checkoutFlow)
```

---

## 🔄 Alur Pesanan

```
1. Buyer: Checkout → Pilih kurir & bayar → Upload bukti (optional)
2. System: Simpan tax_amount, insurance_amount, total_amount
3. Admin: Preview order → Edit tax/insurance jika perlu → Approve (stok keluar)
4. Admin: Input Resi → Ship
5. Buyer: Terima barang → Complete (otomatis 7 hari) atau manual Complete
6. Optional: Buyer ajukan Retur → Admin approve/reject → Stok masuk kembali
```

---

## 📄 Lisensi

MIT License — bebas digunakan & dimodifikasi.

---

## 🤝 Kontribusi

PR welcome. Ikuti konvensi:
- `php artisan test` harus pass
- PSR-12 / Laravel Pint (`./vendor/bin/pint`)
- Nama branch: `feat/...`, `fix/...`, `refactor/...`