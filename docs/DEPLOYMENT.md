# Deploy ke Hosting (tanpa Docker)

Panduan memasang PTSP MTsN 2 Kota Malang di **cPanel/shared hosting** atau **VPS biasa**.
Pakai Docker atau Coolify? Lihat [DEPLOY_COOLIFY.md](DEPLOY_COOLIFY.md). Cara itu lebih disarankan
karena web server, scheduler, dan database sudah disiapkan otomatis.

## 1. Syarat server

| Kebutuhan | Keterangan |
| --- | --- |
| **PHP 8.2–8.4** | Ekstensi: `pdo_pgsql`, `pgsql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `bcmath`, `exif`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`. Fungsi `proc_open` dan `symlink` sebaiknya tidak dimatikan. |
| **PostgreSQL 14+** | **Wajib.** Aplikasi memakai fitur khusus PostgreSQL (trigger audit, JSONB, indeks trigram `pg_trgm`), jadi **tidak bisa memakai MySQL/MariaDB**. Pastikan paket hosting menyediakan PostgreSQL. |
| **Cron** | Satu cron job per menit untuk scheduler (pengingat, monitoring, arsip survei). |
| **HTTPS** | Sertifikat SSL aktif (AutoSSL/Let's Encrypt). Cookie login hanya dikirim lewat HTTPS. |
| Batas unggah | `upload_max_filesize ≥ 20M`, `post_max_size ≥ 25M`, `memory_limit ≥ 256M`. |
| Composer & Node.js | **Tidak perlu di server** bila memakai paket rilis (Cara A). Diperlukan bila build di server (Cara B). |

## 2. Database

Buat database dan user PostgreSQL lewat cPanel (**PostgreSQL Databases**) atau, di VPS:

```bash
# Ganti password di berkas ini dulu, lalu jalankan sebagai superuser postgres
sudo -u postgres psql -f database/setup_postgres.sql
```

Di cPanel, extension `pg_trgm` biasanya bisa dibuat oleh pemilik database (extension *trusted*
sejak PostgreSQL 13). Bila migrasi gagal dengan pesan `permission denied to create extension`,
minta penyedia hosting menjalankan `CREATE EXTENSION pg_trgm;` di database Anda.

## 3. Pasang aplikasi

### Cara A — Paket rilis (disarankan untuk cPanel, tanpa Composer/Node di server)

1. Di komputer yang punya PHP, Composer, Node.js, dan `zip`, jalankan dari folder proyek:

   ```bash
   scripts/build-release.sh          # menghasilkan dist/ptsp-<versi>.zip + .sha256
   ```

   Paket berisi `vendor/` produksi dan aset ter-build. Paket **tidak** berisi `.env`, isi
   `storage`, test, atau `node_modules`.
2. Unggah ZIP ke server, ke folder **di luar** `public_html`, misalnya `~/ptsp-app`, lalu ekstrak
   (File Manager → Extract, atau `unzip`).
3. Lanjut ke langkah 4 (konfigurasi), lalu jalankan langkah 5 (perintah pasca-deploy).

### Cara B — Git + build di server (VPS / hosting dengan SSH, Composer, dan Node.js)

```bash
git clone https://github.com/KamaludinZ/ptsp_mtsn2km.git ~/ptsp-app
cd ~/ptsp-app
cp .env.production.example .env   # lalu isi (langkah 4)
scripts/build-production.sh --seed   # --seed hanya pada instalasi pertama
```

## 4. Konfigurasi `.env`

Salin `.env.production.example` menjadi `.env` di folder aplikasi, lalu isi minimal:

| Variabel | Isi |
| --- | --- |
| `APP_URL` | `https://domain-anda` (sama persis dengan alamat yang dibuka) |
| `APP_KEY` | Kosongkan: `scripts/post-deploy.sh` membuatnya otomatis. **Jangan diganti** setelah aplikasi berjalan. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Akun PostgreSQL dari langkah 2 (`DB_CONNECTION=pgsql`) |
| `ADMIN_PASSWORD` | Password admin awal (`ptsp@mtsn2malang.sch.id`). Jika kosong, password acak dicetak sekali saat seeding. |
| `TINYMCE_API_KEY` | Kunci TinyMCE Cloud untuk editor teks. Daftarkan domain di tiny.cloud → *Approved Domains*. |
| `TRUSTED_PROXIES` | Kosongkan di hosting biasa; isi `*` hanya bila di belakang Cloudflare/reverse proxy. |

Email dan WhatsApp **diatur di panel** (Manajemen Sistem → Integrasi Notifikasi). Nilai `MAIL_*`
dan `WHATSAPP_*` di `.env` hanya dipakai sampai pengaturan di panel disimpan.

Lindungi berkas `.env`, misalnya dengan `chmod 600 .env`, dan jangan pernah menaruhnya di dalam `public_html`.

## 5. Perintah pasca-deploy

Lewat SSH atau *Terminal* cPanel, di folder aplikasi:

```bash
scripts/post-deploy.sh --seed   # instalasi pertama: migrasi + data awal (peran, admin, layanan, survei)
scripts/post-deploy.sh          # setiap update berikutnya
```

Skrip ini menyalakan mode maintenance, menyiapkan folder `storage`, menjalankan migrasi, membuat
`public/storage`, menyiapkan cache, lalu mematikan mode maintenance.

Tanpa SSH sama sekali? Sebagian cPanel punya fitur *Cron Jobs* yang bisa dipakai sekali jalan:
isi perintah `cd ~/ptsp-app && bash scripts/post-deploy.sh --seed`, jadwalkan satu menit lagi,
lalu hapus cron itu setelah berjalan.

## 6. Cron scheduler (wajib)

Tambahkan satu cron job **setiap menit** (cPanel → Cron Jobs → *Once Per Minute*):

```
* * * * * cd ~/ptsp-app && php artisan schedule:run >> /dev/null 2>&1
```

Gunakan path PHP versi yang benar bila hosting punya beberapa versi, misalnya
`/opt/cpanel/ea-php83/root/usr/bin/php`. Tanpa cron ini, pengingat permohonan, snapshot
monitoring, pemangkasan notifikasi, dan arsip survei triwulanan tidak berjalan.

## 7. Web root

Document root harus menunjuk ke folder **`public/`**, bukan folder utama aplikasi.

**Domain/subdomain yang bisa diatur document root-nya** (cPanel → Domains): arahkan ke
`/home/<user>/ptsp-app/public`. Selesai.

**Domain utama yang terkunci ke `public_html`:**

1. Pindahkan isi `~/ptsp-app/public/` ke `~/public_html/`, termasuk `.htaccess`.
2. Ubah `~/public_html/index.php` agar menunjuk ke folder aplikasi:

   ```php
   <?php
   use Illuminate\Http\Request;
   define('LARAVEL_START', microtime(true));
   $app = __DIR__.'/../ptsp-app';
   if (file_exists($maintenance = $app.'/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   require $app.'/vendor/autoload.php';
   (require_once $app.'/bootstrap/app.php')->handleRequest(Request::capture());
   ```

3. Buat symlink storage di web root: `ln -s ~/ptsp-app/storage/app/public ~/public_html/storage`.
   Bila symlink dimatikan hosting, mintalah penyedia hosting mengaktifkannya. Tanpa symlink,
   gambar editor, foto tamu, dan gambar slider tidak tampil.
4. Setiap update, salin ulang isi `public/build`, `public/js`, dan `public/css` dari paket rilis ke `public_html`.

## 8. Update aplikasi

**Cara A (paket rilis):**

1. Buat paket baru dengan `scripts/build-release.sh`.
2. Backup database dan folder `storage` (langkah 9).
3. Ekstrak paket ke folder aplikasi dan timpa berkas lama. `.env` dan isi `storage` tidak ikut tertimpa.
4. Jalankan `scripts/post-deploy.sh`.

Alternatif dari dalam aplikasi: **Monitoring Sistem → Pembaruan → Unggah paket**, lalu pilih ZIP
dari `build-release.sh` (opsional dengan checksum dari berkas `.sha256`). Aplikasi membuat backup
berkas, memasang paket, menjalankan migrasi, dan memulihkan otomatis bila gagal. Fitur ini sengaja
dinonaktifkan di Docker, karena di sana update dilakukan dengan deploy ulang image.

**Cara B (git):** `git pull && scripts/build-production.sh`.

### Berkas yang aman saat update

Yang **harus tetap** dan tidak pernah ada di paket rilis maupun di git:

| Data | Tempat |
| --- | --- |
| Konfigurasi dan `APP_KEY` | `~/ptsp-app/.env` |
| Dokumen pemohon dan berkas surat (privat) | `~/ptsp-app/storage/app/private` |
| Logo, gambar editor, foto tamu, slider (publik) | `~/ptsp-app/storage/app/public`, tampil lewat symlink `public/storage` |
| Data aplikasi, session, cache | database PostgreSQL |

Folder lain (`app`, `config`, `database`, `resources`, `routes`, `vendor`, `public/build`, ...)
boleh ditimpa setiap update. Aturannya:

- **Ekstrak paket di atas folder lama.** Jangan menghapus folder aplikasi lalu mengunggah ulang,
  karena `.env` dan `storage/app` ikut terhapus.
- Domain yang terkunci ke `public_html` (langkah 7): jangan menimpa `public_html/index.php` yang
  sudah diubah, dan jangan menghapus symlink `public_html/storage`. Salin hanya `public/build`,
  `public/js`, dan `public/css`.
- Jangan mengganti `APP_KEY`. Jangan menjalankan `migrate:fresh`, `migrate:reset`, atau `db:wipe`
  di server produksi.
- `scripts/post-deploy.sh` aman dijalankan berulang: hanya menambah migrasi baru, membuat ulang
  symlink dan cache, serta membuang `public/hot` (sisa `npm run dev` yang membuat tampilan rusak).

## 9. Backup

Minimal setiap hari, dan selalu sebelum update:

```bash
# Database (format custom, bisa dipulihkan dengan pg_restore)
pg_dump -Fc -h <DB_HOST> -U <DB_USERNAME> <DB_DATABASE> > ~/backup/ptsp-$(date +%F).dump

# Berkas unggahan: dokumen pemohon (privat), hasil layanan, gambar editor, foto tamu
tar -czf ~/backup/ptsp-storage-$(date +%F).tar.gz -C ~/ptsp-app/storage app
```

Kedua perintah bisa dijadwalkan sebagai cron harian. Simpan salinan di luar server. Fitur
**Backup** cPanel saja tidak cukup bila PostgreSQL tidak ikut di-backup oleh paket hosting.

Memulihkan database: `pg_restore --clean --if-exists --no-owner -d <DB_DATABASE> ptsp-<tanggal>.dump`.

## 10. Daftar periksa setelah deploy

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` memakai `https://`
- [ ] Login admin berhasil, lalu **password admin diganti** dari menu profil
- [ ] Cron `schedule:run` aktif (Monitoring Sistem → *Scheduler* berstatus sehat setelah beberapa menit)
- [ ] Editor teks (TinyMCE) tampil di form Layanan/Pengumuman, dan domain sudah terdaftar di tiny.cloud
- [ ] Unggah gambar di editor dan foto tamu tampil, artinya `public/storage` terpasang
- [ ] Integrasi Email/WhatsApp diatur dan **Uji kirim** berhasil
- [ ] Backup harian database dan `storage` berjalan

## 11. Masalah yang sering terjadi

| Gejala | Penyebab & solusi |
| --- | --- |
| Error 500 setelah unggah | `.env` belum ada atau `APP_KEY` kosong: jalankan `scripts/post-deploy.sh`. Lihat `storage/logs/laravel.log`. |
| `could not find driver` | Ekstensi `pdo_pgsql` belum aktif. Aktifkan di cPanel → *Select PHP Version → Extensions*. |
| `permission denied to create extension "pg_trgm"` | Minta penyedia hosting membuat extension `pg_trgm` (langkah 2). |
| Error 419 saat login | Buka situs lewat alamat yang sama dengan `APP_URL` dan pastikan HTTPS aktif. |
| Tampilan tanpa CSS | `public/build` tidak ikut terunggah atau document root salah (langkah 7). Hapus `public/hot` bila ada (sisa `npm run dev`). |
| `ERR_TOO_MANY_REDIRECTS` di belakang Cloudflare | Pakai `.htaccess` terbaru (mengenali `X-Forwarded-Proto`) dan set SSL Cloudflare ke *Full*, lalu isi `TRUSTED_PROXIES=*`. |
| Editor teks diblokir (*Content Security Policy*) | `.htaccess` lama mengirim CSP sendiri. Pakai `.htaccess` terbaru: header keamanan dikirim oleh aplikasi. |
| Gambar/foto tidak tampil | Symlink `public/storage` belum ada (langkah 7.3). |
| Editor teks tidak muncul | `TINYMCE_API_KEY` kosong atau domain belum terdaftar di tiny.cloud. |
| Pengingat/monitoring tidak jalan | Cron scheduler belum dipasang (langkah 6). |
| Unggah berkas gagal (413) | Naikkan `upload_max_filesize`/`post_max_size` di *MultiPHP INI Editor*. |
