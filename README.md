# 🅿️ Haezvl Park

**Sistem Manajemen Parkir Semi-Otomatis** — Aplikasi web berbasis Laravel untuk mengelola area parkir dengan sistem 2 gate (masuk & keluar), pembayaran terintegrasi, dan dashboard real-time.

---

## ✨ Fitur Utama

### 🚧 Entry Gate (Gerbang Masuk)
- Input plat nomor & jenis kendaraan (Motor, Mobil, Truk)
- Generate tiket parkir otomatis dengan kode unik `PKR-YYYYMMDD-XXXX`
- QR Code berisi URL langsung ke halaman booth untuk proses keluar
- Animasi gate terbuka dengan efek pulse ring & confetti
- Download tiket sebagai gambar

### 🚪 Exit Gate (Gerbang Keluar)
- Scan QR Code via kamera HP atau input manual kode tiket
- Kalkulasi tarif otomatis berdasarkan durasi parkir
- Pembayaran terintegrasi langsung di exit gate
- Generate struk/receipt setelah pembayaran
- Animasi gate terbuka saat proses selesai

### 📊 Dashboard
- Statistik real-time: kendaraan terparkir, pendapatan hari ini, rata-rata durasi
- Chart distribusi jenis kendaraan
- Tabel kendaraan yang sedang parkir
- **Auto-refresh setiap 30 detik**

### 📋 History (Riwayat)
- Riwayat semua transaksi parkir
- Filter berdasarkan tanggal, jenis kendaraan, dan pencarian
- Summary statistik (total transaksi & total pendapatan)
- Download laporan sebagai PDF

### 💰 Sistem Tarif
- Tarif berbeda per jenis kendaraan
- Perhitungan otomatis berdasarkan durasi (per jam)
- Tarif flat untuk 1 jam pertama, tarif per jam berikutnya

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12 (PHP) |
| Database | SQLite |
| Frontend | Blade Templates, Vanilla CSS, JavaScript |
| QR Code | qrcode-generator (JS library) |
| Screenshot | html2canvas |

---

## 🚀 Instalasi & Setup

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & npm (opsional, untuk Vite)

### Langkah Instalasi

```bash
# Clone repository
git clone https://github.com/Jemjeqt/Haezvl-Park.git
cd Haezvl-Park

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Buat database SQLite
touch database/database.sqlite

# Jalankan migrasi & seeder
php artisan migrate --seed

# Jalankan server
php artisan serve
```

Akses aplikasi di **http://127.0.0.1:8000**

---

## 📁 Struktur Project

```
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php    # Dashboard & statistik
│   │   ├── EntryController.php        # Gerbang masuk
│   │   ├── ExitController.php         # Gerbang keluar + pembayaran
│   │   ├── HistoryController.php      # Riwayat transaksi
│   │   └── BoothController.php        # Halaman booth (via QR scan)
│   ├── Models/
│   │   ├── Ticket.php                 # Model tiket parkir
│   │   ├── Payment.php                # Model pembayaran
│   │   └── Tariff.php                 # Model tarif
│   └── Services/
│       ├── TicketService.php          # Logic tiket
│       └── TariffCalculator.php       # Kalkulasi tarif
├── resources/views/
│   ├── layout.blade.php               # Layout utama
│   ├── dashboard.blade.php            # Halaman dashboard
│   ├── entry.blade.php                # Halaman entry gate
│   ├── exit.blade.php                 # Halaman exit gate
│   ├── history.blade.php              # Halaman riwayat
│   ├── entry-ticket.blade.php         # Template tiket masuk
│   ├── receipt.blade.php              # Template struk pembayaran
│   └── exit-receipt.blade.php         # Template bukti keluar
└── public/css/
    └── app.css                        # Semua styling
```

---

## 🔄 Alur Sistem

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│  ENTRY GATE │────▶│   PARKIR     │────▶│  EXIT GATE  │
│             │     │              │     │             │
│ • Input plat│     │ • Kendaraan  │     │ • Scan QR   │
│ • Pilih tipe│     │   terparkir  │     │ • Bayar     │
│ • Print QR  │     │ • Dashboard  │     │ • Gate open │
│ • Gate open │     │   monitoring │     │ • Print     │
└─────────────┘     └──────────────┘     └─────────────┘
```

---

## 📄 License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
