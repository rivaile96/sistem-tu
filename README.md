# Sistem TU — Tata Usaha Sekolah

Sistem manajemen Tata Usaha sekolah berbasis Laravel. Mencakup manajemen siswa, kelas, rombel, tagihan, POS kantin/koperasi, PPDB, dan portal siswa dengan payment gateway Midtrans.

**Live:** [https://tu.brody.my.id](https://tu.brody.my.id)

---

## Fitur

### Admin / Staff TU
- **Dashboard** — ringkasan aktivitas terkini, statistik tagihan dan transaksi POS
- **Manajemen Siswa** — CRUD siswa, import via Excel, naik kelas massal
- **Master Kelas** — kelola data kelas per jenjang (SD/SMP/SMA/SMK), modal create/edit/delete
- **Rombel** — pengelompokan siswa per tahun ajaran
- **PPDB** — pendaftaran peserta didik baru, verifikasi, terima/tolak
- **Tagihan Sekolah** — buat tagihan per siswa atau per kelas, rekap pembayaran
- **POS Sekolah** — kasir kantin/koperasi, master barang, paket/bundling
- **Pengaturan Sekolah** — nama sekolah, alamat, kepala sekolah, jenjang, akreditasi, dll

### Portal Siswa (`/siswa`)
- Login dengan **NIS + tanggal lahir** (format `ddmmyy`)
- Dashboard tagihan — lihat tagihan aktif dan riwayat pembayaran
- **Bayar via Midtrans Snap** — semua metode: transfer bank, QRIS, GoPay, OVO, dll
- Struk/bukti pembayaran otomatis setelah transaksi berhasil
- Ringkasan tagihan bulan ini

### Security
- CSP (Content Security Policy) middleware aktif
- Rate limiting pada API login (`throttle:5,1`)
- Mass assignment protection di semua model
- Route `/register` ditutup (tidak ada registrasi publik)

---

## Requirement

- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Node.js & NPM (untuk build assets)
- PHP extension: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/rivaile96/sistem-tu.git
cd sistem-tu
```

### 2. Install Dependency PHP

```bash
composer install
```

### 3. Install Dependency JS & Build Assets

```bash
npm install
npm run build
```

### 4. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:

```env
APP_NAME="Sistem TU"
APP_ENV=local
APP_KEY=         # otomatis terisi setelah key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_tu
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Setup Database

```bash
# Buat database dulu di MySQL
mysql -u root -p -e "CREATE DATABASE sistem_tu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi
php artisan migrate

# (Opsional) Seed data awal
php artisan db:seed
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Jalankan Server

```bash
php artisan serve
```

Akses di `http://localhost:8000`

---

## Konfigurasi Midtrans (Payment Gateway)

Portal siswa menggunakan **Midtrans Snap** untuk pembayaran tagihan sekolah.

### Langkah Setup

#### 1. Daftar / Login Akun Midtrans

- Sandbox (testing): [https://dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com)
- Production: [https://dashboard.midtrans.com](https://dashboard.midtrans.com)

#### 2. Ambil API Keys

Di dashboard Midtrans → **Settings → Access Keys**:

| Key | Keterangan |
|-----|------------|
| Merchant ID | ID merchant Anda |
| Client Key | Digunakan di frontend (JavaScript) |
| Server Key | Digunakan di backend (rahasia, jangan expose) |

#### 3. Tambahkan ke `.env`

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false   # true untuk production
```

> **Penting:** Ganti `false` ke `true` saat deploy ke production.

#### 4. Setup Notification URL (Webhook)

Di dashboard Midtrans → **Settings → Configuration**:

| Field | URL |
|-------|-----|
| Payment Notification URL | `https://your-domain.com/siswa/payment/callback` |
| Finish Redirect URL | `https://your-domain.com/siswa/payment/success` |
| Unfinish Redirect URL | `https://your-domain.com/siswa/dashboard` |
| Error Redirect URL | `https://your-domain.com/siswa/dashboard` |

> Untuk sandbox, gunakan URL production yang sudah bisa diakses publik (bukan localhost). Gunakan [ngrok](https://ngrok.com) atau [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/) untuk testing lokal.

#### 5. Switch ke Production

Saat siap production:

```env
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-xxxxxx   # ganti dengan production key
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxx  # ganti dengan production key
```

Lalu clear config cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Portal Siswa — Login

Portal siswa diakses di `/siswa/login`.

| Field | Format |
|-------|--------|
| NIS | Nomor Induk Siswa |
| Password | Tanggal lahir format `ddmmyy` (contoh: lahir 12 Juli 2006 → `120706`) |

Pastikan kolom `birth_date` siswa sudah diisi di database.

---

## Pengaturan Sekolah

Setelah install, isi data sekolah melalui menu **Konfigurasi → Pengaturan Sekolah**:

- Nama Sekolah
- Alamat
- Nomor Telepon
- Email
- Website
- Nama Kepala Sekolah
- Nama Bendahara
- NPSN / NSS
- Jenjang (SD/SMP/SMA/SMK/dll)
- Akreditasi

Pengaturan ini digunakan di header struk pembayaran dan tampilan sistem.

---

## Akun Admin Default

Setelah `db:seed` (jika ada seeder):

| Field | Value |
|-------|-------|
| Email | `admin@sekolah.com` |
| Password | `password` |

> Segera ganti password setelah login pertama melalui menu **Profile User**.

---

## Struktur Role

| Role | Akses |
|------|-------|
| `superadmin` | Semua fitur + pengaturan sistem |
| `admin_tu` | Manajemen siswa, kelas, tagihan, POS |
| `bendahara` | Tagihan dan laporan keuangan |
| `siswa` | Portal siswa (login via NIS) |

---

## Deployment ke Production

### Nginx Config (contoh)

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/sistem-tu/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Optimasi Production

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### Permission

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## Troubleshooting

### Config tidak terupdate setelah edit `.env`

```bash
php artisan config:clear
php artisan cache:clear
```

Jika file `bootstrap/cache/config.php` dimiliki oleh `www-data` dan tidak bisa dihapus:

```bash
sudo php artisan config:clear
# atau
sudo -u www-data php artisan config:clear
```

### Midtrans 401 Unauthorized

Cek apakah config cache berisi placeholder lama:

```bash
php artisan tinker
>>> config('midtrans.server_key')
```

Jika output adalah `your-midtrans-server-key` (placeholder), bukan key asli → config cache perlu di-clear.

### White screen / blank page

```bash
php artisan view:clear
php artisan cache:clear
tail -100 storage/logs/laravel.log
```

### Portal siswa tidak bisa login

- Pastikan kolom `nis` dan `birth_date` siswa sudah diisi
- Format password: `ddmmyy` tanpa strip (contoh: 12 Juli 2006 → `120706`)

---

## Teknologi

- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Database:** MySQL / MariaDB
- **Payment:** Midtrans Snap
- **Notifikasi:** SweetAlert2
- **Auth Siswa:** Custom guard (NIS + tanggal lahir)

---

## Lisensi

Project ini dikembangkan untuk keperluan internal. Tidak untuk distribusi publik.
