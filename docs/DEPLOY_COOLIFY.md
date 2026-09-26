# Deploy ke Coolify (Docker)

Panduan ini untuk memasang aplikasi PTSP MTsN 2 Kota Malang di server yang
dikelola [Coolify](https://coolify.io). Semua yang dibutuhkan sudah ada di repo:

| Berkas | Fungsi |
| --- | --- |
| `Dockerfile` | Membangun image produksi: dependensi PHP, build aset Vite, PHP 8.3 + nginx |
| `docker-compose.yml` | Aplikasi + database PostgreSQL 16 dengan volume permanen (dipakai Coolify) |
| `docker/entrypoint.sh` | Saat container start: cek konfigurasi, tunggu database, migrasi, isi data awal, siapkan cache |
| `docker/nginx.conf`, `docker/php.ini`, `docker/php-fpm.conf`, `docker/supervisord.conf` | Konfigurasi server di dalam container |
| `.github/workflows/docker.yml` | Uji otomatis di GitHub: build image, jalankan bersama PostgreSQL, cek halaman |

## Cara kerjanya

```
Pengunjung ──HTTPS──▶ Proxy Coolify (sertifikat SSL otomatis)
                          │ http, port 8080
                          ▼
                 Container "app": nginx ─▶ php-fpm (Laravel) + scheduler
                          │
                          ▼
                 Container "postgres": PostgreSQL 16
```

- **Data permanen** disimpan di dua volume dan tidak hilang saat deploy ulang:
  - `ptsp-postgres`: isi database.
  - `ptsp-storage`: berkas unggahan pemohon, hasil layanan, dan log.
- **Setiap kali container menyala**, container otomatis:
  1. Memastikan `APP_KEY` sudah diisi.
  2. Menunggu database siap.
  3. Menjalankan migrasi.
  4. Pada deploy pertama saja: mengisi peran, akun admin, layanan, dan survei.
  5. Menyiapkan cache.

## 1. Persiapan

1. **Server dengan Coolify v4** yang sudah terpasang. Spesifikasi minimal 2 GB RAM dan 20 GB disk.
2. **Domain.** Contoh: `ptsp.mtsn2malang.sch.id`. Buat DNS record **A** yang mengarah ke IP server Coolify.
3. **Akses GitHub.** Hubungkan repo `KamaludinZ/ptsp_mtsn2km` ke Coolify lewat
   *Sources → GitHub App*. Cara ini disarankan karena Coolify bisa deploy otomatis setiap ada push.
4. **Buat `APP_KEY`.** Jalankan salah satu perintah berikut di komputer mana saja:

   ```bash
   # Jika ada PHP + Laravel di komputer Anda
   php artisan key:generate --show

   # Atau hanya dengan openssl (Linux/macOS/Git Bash)
   echo "base64:$(openssl rand -base64 32)"
   ```

   Hasilnya berbentuk `base64:xxxxxxxx...=`. Simpan baik-baik. **Jangan ganti key ini setelah
   aplikasi berjalan**, karena session dan data terenkripsi akan tidak terbaca.

## 2. Buat aplikasi di Coolify

1. Buka Coolify → **Projects** → pilih atau buat project (misal "PTSP") → **+ New** → **Resource**.
2. Pilih **Private Repository (with GitHub App)**. Pilih *Public Repository* jika repo bersifat publik.
3. Pilih repo `ptsp_mtsn2km` dan branch **`master`**.
4. **Build Pack: Docker Compose**. Isi *Docker Compose Location* dengan `/docker-compose.yml`, lalu **Continue**.
5. Coolify menampilkan dua service: `app` dan `postgres`.
   - Pada service **app**, isi **Domains** dengan `https://ptsp.mtsn2malang.sch.id`.
     Port 8080 sudah diatur otomatis lewat `SERVICE_FQDN_APP_8080`.
   - Service **postgres** tidak perlu domain. Database tidak boleh dibuka ke internet.

## 3. Isi Environment Variables

Buka tab **Environment Variables**. Variabel wajib:

| Variabel | Isi | Keterangan |
| --- | --- | --- |
| `APP_KEY` | `base64:...` dari langkah 1.4 | Wajib. Container menolak start jika kosong. |
| `APP_URL` | `https://ptsp.mtsn2malang.sch.id` | Samakan dengan domain. |
| `ADMIN_PASSWORD` | Password kuat | Dipakai sekali saat deploy pertama untuk akun admin `ptsp@mtsn2malang.sch.id`. Jika kosong, password acak dicetak di log deploy. |

`SERVICE_USER_POSTGRES` dan `SERVICE_PASSWORD_POSTGRES` **dibuat otomatis oleh Coolify**.
Tidak perlu diisi.

Variabel opsional:

| Variabel | Bawaan | Keterangan |
| --- | --- | --- |
| `APP_NAME` | `PTSP MTsN 2 Kota Malang` | Nama di judul halaman dan email |
| `APP_TIMEZONE` | `Asia/Jakarta` | Zona waktu tampilan jam |
| `MAIL_MAILER` | `log` | Isi `smtp` agar email benar-benar terkirim |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS` | – | Akun SMTP. Contoh Gmail: `smtp.gmail.com`, port `587`, pakai *App Password*. |
| `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`, `WHATSAPP_SENDER_ID` | – | Gateway WhatsApp, bila dipakai |
| `AUTO_MIGRATE` | `true` | Migrasi otomatis setiap deploy |
| `SEED_ON_FIRST_DEPLOY` | `true` | Isi data awal jika tabel users masih kosong |
| `LOG_LEVEL` | `warning` | Isi `debug` sementara saat mencari masalah |
| `POSTGRES_DB` | `ptsp` | Nama database |

## 4. Deploy

1. Klik **Deploy**. Build pertama memakan waktu sekitar 5–10 menit.
2. Buka tab **Logs** pada service `app`. Deploy berhasil jika muncul:

   ```
   [entrypoint] Database siap.
   [entrypoint] Menjalankan migrasi...
   [entrypoint] Database baru: mengisi peran, akun admin, layanan dan survei...
   [entrypoint] Aplikasi siap di port 8080.
   ```

3. Buka `https://ptsp.mtsn2malang.sch.id`. Masuk dengan `ptsp@mtsn2malang.sch.id` dan password dari `ADMIN_PASSWORD`.
4. **Segera ganti password admin** dari menu profil. Setelah itu buat akun staf, yaitu Kepala
   Sekolah, Kepala TU, petugas loket, petugas TU, dan pengawas, lewat panel `/cp`.
5. Aktifkan deploy otomatis: *Configuration → General → Auto Deploy*. Setiap push ke `master`
   akan otomatis ter-deploy.

## 5. Update aplikasi

Cukup push atau merge ke branch `master`. Coolify akan:

1. Mem-build image baru.
2. Menyalakan container baru. Migrasi berjalan otomatis, dan data awal **tidak** diisi ulang.
3. Setelah health check `/up` lolos, container baru menggantikan container lama.

Data di volume tetap aman.

## 6. Backup

Lakukan backup berkala. Minimal setiap hari untuk database, dan sebelum update besar.

**Database.** Buka Coolify → service `postgres` → tab **Terminal**, lalu jalankan:

```bash
pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc > /var/lib/postgresql/data/backup-$(date +%F).dump
```

Simpan salinan backup di luar server, misalnya diunduh atau dikirim ke S3. Untuk backup terjadwal
otomatis ke S3, pakai **Opsi B** di bawah, yaitu database yang dikelola Coolify.

**Berkas unggahan.** Volume `ptsp-storage` berisi dokumen pemohon. Backup lewat server:

```bash
docker run --rm -v <nama-volume-storage>:/data -v "$PWD":/backup alpine \
  tar czf /backup/ptsp-storage-$(date +%F).tar.gz -C /data .
```

Nama volume lengkap bisa dilihat dengan `docker volume ls | grep storage`.

## 7. Pindah data dari server lama (opsional)

Jika sebelumnya aplikasi berjalan di server lain, misalnya cPanel:

1. Di server lama, buat dump database: `pg_dump -Fc -U <user> <database> > ptsp.dump`.
2. Deploy di Coolify dengan `SEED_ON_FIRST_DEPLOY=false`.
3. Salin `ptsp.dump` ke server Coolify, lalu pulihkan ke container postgres:

   ```bash
   docker cp ptsp.dump <container-postgres>:/tmp/ptsp.dump
   docker exec -it <container-postgres> sh -c 'pg_restore --clean --if-exists --no-owner -U "$POSTGRES_USER" -d "$POSTGRES_DB" /tmp/ptsp.dump'
   ```

4. Salin isi `storage/app` dari server lama ke volume `ptsp-storage` di folder `app/`.
5. Pakai **`APP_KEY` yang sama** dengan server lama.
6. Samakan `APP_TIMEZONE` dengan yang dipakai data lama. Server lama memakai `UTC` bila tidak pernah diatur.
7. Restart service `app`. Migrasi yang belum ada di data lama akan dijalankan otomatis.

## Opsi B: database dikelola Coolify

Pilih cara ini jika ingin fitur backup terjadwal Coolify ke S3.

1. **+ New → Database → PostgreSQL 16**. Catat *Postgres URL (internal)*.
2. **+ New → Resource → repo ini → Build Pack: Dockerfile**. Isi *Ports Exposes* dengan `8080` dan isi domain.
3. Isi Environment Variables. Gunakan variabel di bagian 3, lalu tambahkan:

   ```
   DATABASE_URL=<Postgres URL (internal) dari langkah 1>
   CACHE_DRIVER=database
   SESSION_DRIVER=database
   SESSION_ENCRYPT=true
   SESSION_SECURE_COOKIE=true
   APP_TIMEZONE=Asia/Jakarta
   ```

4. Pada *Storages*, tambahkan **Volume Mount** dengan destination `/var/www/html/storage`.
5. Pada tab *Backups* database, atur jadwal backup ke S3.

## Masalah yang sering terjadi

| Gejala | Penyebab & solusi |
| --- | --- |
| Log: `APP_KEY belum diisi` | Isi `APP_KEY` (langkah 1.4), lalu deploy ulang. |
| Log: `database tidak dapat dihubungi` | Service postgres belum sehat atau password berubah. Cek log postgres. Jika volume database dibuat dengan password lama, password baru tidak berlaku, jadi kembalikan password lama. |
| Halaman error 500 | Isi `LOG_LEVEL=debug`, lalu deploy ulang dan lihat tab Logs. Jangan menyalakan `APP_DEBUG` di produksi. |
| Tampilan tanpa CSS atau tautan `http://` | Pastikan `APP_URL` memakai `https://` dan sama persis dengan domain. |
| Error 419 (page expired) saat login | Buka situs lewat domain yang sama dengan `APP_URL`, lalu hapus cookie browser. |
| Unggah berkas gagal (413) | Batas unggah 20 MB per berkas. Kompres dokumen yang lebih besar. |
| Lupa password admin | Terminal service `app`: `php artisan tinker`, lalu `App\Models\User::where('email','ptsp@mtsn2malang.sch.id')->first()->update(['password'=>bcrypt('PasswordBaru123!')]);` |

## Perintah berguna (tab Terminal service `app`)

```bash
php artisan about                 # ringkasan konfigurasi
php artisan migrate:status        # status migrasi
php artisan optimize:clear && php artisan optimize   # muat ulang cache
php artisan permission:cache-reset                   # setelah ubah peran/izin
```
