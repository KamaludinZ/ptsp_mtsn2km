# PTSP MTsN 2 KOTA MALANG

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Filament-3.2-F59E0B?style=for-the-badge&logo=data:image/svg+xml;base64,..." alt="Filament 3.2">
  <img src="https://img.shields.io/badge/PostgreSQL-Latest-336791?style=for-the-badge&logo=postgresql" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css" alt="TailwindCSS">
</div>

Sistem Pelayanan Terpadu Satu Pintu (PTSP) untuk Madrasah Tsanawiyah Negeri 2 Kota Malang yang sesuai dengan **Permen PANRB 15/2014** tentang Pedoman Standar Pelayanan.

## 🎯 Deskripsi

Aplikasi PTSP MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat dengan prinsip:

- ✅ **Transparan & Akuntabel** - Setiap proses dapat dilacak real-time
- ✅ **Cepat & Tepat Waktu** - Jaminan penyelesaian sesuai SLA
- ✅ **Mudah Diakses** - Layanan online 24/7 dan offline di loket PTSP
- ✅ **Multi-Channel** - Satu sistem untuk layanan online dan offline

### Modul Utama

- 🏢 **Front-Desk**: Modul triage dan pendaftaran layanan offline (walk-in)
- 🌐 **Online Portal**: Portal publik untuk pendaftaran layanan online
- ⚙️ **Back-Office**: Sistem workflow, approval, dan manajemen tiket
- 📊 **Supervision**: Modul pengaduan, whistleblowing, dan survei kepuasan (SKM/SPAK)
- 👨‍💼 **Administrator**: Dashboard eksekutif dan konfigurasi sistem

## 🚀 Teknologi yang Digunakan

- **Frontend**: Laravel 12, Blade, dan Filament 3.2
- **Auth**: Laravel Breeze 2.0
- **Backend**: Laravel 12 (untuk database/API)
- **Database**: PostgreSQL
- **UI Framework**: TailwindCSS 3.x
- **JavaScript**: Alpine.js 3.x
- **Icons**: Font Awesome 6.4, Heroicons
- **Deployment**: Shared Hosting / Cpanel / LARAGON LOKAL

## 🎨 Tema & Design

Aplikasi menggunakan tema warna:
- 🟢 **Primary Green** (#22c55e) - Untuk elemen utama dan branding
- 🟠 **Secondary Orange** (#f97316) - Untuk aksen dan call-to-action
- ⚪ **White** (#ffffff) - Background dan clean space

Design responsif dan mobile-first dengan:
- Material Design inspired components
- Smooth transitions dan animations
- Gradient backgrounds dan shadows
- Icon-based navigation

## ✨ Fitur Utama

### Multi-User System
7 tipe pengguna dengan dashboard berbeda:
- 👨‍🏫 **Guru** - Layanan kepegawaian dan administrasi
- 👔 **Pegawai** - Layanan administrasi pegawai
- 🎓 **Siswa** - Layanan akademik siswa aktif
- 🎓 **Alumni** - Layanan untuk alumni
- 👨‍👩‍👧 **Wali Murid** - Layanan untuk orang tua/wali
- 🏢 **Instansi** - Layanan kerjasama institusional
- 👤 **Masyarakat Umum** - Layanan publik

### Core Features
- 🎫 **Sistem Tiket Terintegrasi** - Online & offline dalam satu alur
- 🔄 **Workflow Engine** - Otomasi proses approval multi-level
- 📱 **Tracking Real-time** - Lacak status permohonan kapan saja
- 📊 **Dashboard Analytics** - Statistik dan KPI layanan
- 💬 **Pengaduan Terpadu** - Complaints, suggestions, whistleblowing
- 📋 **Survei Otomatis** - SKM (Survei Kepuasan Masyarakat) & SPAK
- 📖 **Buku Tamu Digital** - Check-in/out pengunjung fisik
- 🔔 **Notifikasi** - Email & WhatsApp notifications
- 📄 **14 Komponen Standar** - Sesuai Permen PANRB 15/2014

## Instalasi

### Prerequisites

- PHP >= 8.2
- Composer
- PostgreSQL
- Node.js & NPM

### Langkah-langkah Instalasi

1. Clone repository (atau gunakan direktori yang telah disiapkan)
2. Install dependencies:
   ```
   composer install
   npm install
   ```

3. Copy dan atur file environment:
   ```
   cp .env.example .env
   ```

4. Atur konfigurasi database di file `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pts_mtsn2_malang
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. Jalankan migrasi database:
   ```
   php artisan migrate --seed
   ```

7. Compile assets:
   ```
   npm run build
   ```

8. Jalankan aplikasi:
   ```
   php artisan serve
   ```

## 📦 Struktur Modul

### A. 🏢 MODUL FRONT-DESK (LOKET FISIK SEKOLAH)
| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Modul 1** | Triage (Pemilahan) & Buku Tamu | ✅ Selesai |
| **Modul 2** | Registrasi Layanan Offline (Walk-In) | ✅ Selesai |

### B. 🌐 MODUL PORTAL PUBLIK (ONLINE)
| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Modul 3** | Autentikasi dan Manajemen Peran (Multi-User) | ✅ Selesai |
| **Modul 4** | Katalog Standar Pelayanan (Permen PANRB 15/2014) | ✅ Selesai |
| **Modul 5** | Pengajuan Layanan Online (e-Form) | ✅ Selesai |
| **Modul 6** | Dashboard Pemohon (Pelacakan Tiket) | ✅ Selesai |

### C. ⚙️ MODUL BACK-OFFICE (DAPUR PROSES)
| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Modul 7** | Antrian Tugas (Workflow Engine) | ✅ Selesai |
| **Modul 8** | Persetujuan (Approval) Pimpinan | ✅ Selesai |
| **Modul 9** | Manajemen Produk Layanan | ✅ Selesai |

### D. 📊 MODUL PENGAWASAN & EVALUASI (WAJIB)
| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Modul 10** | Penanganan Pengaduan & Whistleblowing | ✅ Selesai |
| **Modul 11** | Survei Otomatis (SKM & SPAK) | ✅ Selesai |

### E. 👨‍💼 MODUL ADMINISTRATOR & PIMPINAN
| Modul | Deskripsi | Status |
|-------|-----------|--------|
| **Modul 12** | Super Admin (Master Konfigurasi) | ✅ Selesai |
| **Modul 13** | Dashboard Eksekutif (Pimpinan) | ✅ Selesai |

## 📸 Screenshots

### Landing Page
- **Hero Section** dengan gradient hijau dan CTA buttons
- **Performance Stats** menampilkan kinerja pelayanan
- **Service Cards** untuk 3 kategori utama layanan
- **Quick Access** dengan 6 layanan penting
- **About Section** dengan 14 komponen standar pelayanan

### Dashboard (Role-Based)
- **Dashboard Guru/Pegawai** - Stats permohonan dan tiket aktif
- **Dashboard Siswa/Alumni** - Layanan tersedia dan progress
- **Dashboard Wali Murid** - Monitor layanan untuk putra/putri
- **Dashboard Instansi/Umum** - Layanan publik dan tracking
- **Dashboard Admin** - Overview sistem dan statistik lengkap

## 🧪 Testing

Jalankan test dengan perintah:
```bash
php artisan test
```

Atau test spesifik:
```bash
# Test unit
php artisan test --testsuite=Unit

# Test feature
php artisan test --testsuite=Feature

# Test dengan coverage
php artisan test --coverage
```

## 📚 Dokumentasi

Dokumentasi lengkap tersedia di:
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Summary implementasi
- **[database_schema.md](database_schema.md)** - Schema database
- **[rancangan_app.md](rancangan_app.md)** - Rancangan aplikasi lengkap

## 🤝 Kontribusi

Kontribusi sangat dihargai! Silakan:
1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

## 💡 Sistem Build Tanpa Vite

Proyek ini telah dimodifikasi untuk berjalan tanpa Vite. Untuk informasi lebih lanjut tentang sistem build baru, silakan lihat dokumen `README_BUILD.md`.

## 📞 Kontak & Support

Untuk pertanyaan, bantuan, atau informasi lebih lanjut:

- 📧 **Email**: info@mtsn2malang.sch.id
- ☎️ **Telepon**: (0341) 123456
- 🌐 **Website**: [mtsn2malang.sch.id](https://mtsn2malang.sch.id)
- 📍 **Alamat**: Jl. Raya Tumpang No. 123, Kota Malang, Jawa Timur

### Jam Operasional
- **Senin - Kamis**: 07.00 - 15.00 WIB
- **Jumat**: 07.00 - 11.00 WIB
- **Sabtu - Minggu**: Tutup

---

<div align="center">
  <p><strong>Dikembangkan dengan ❤️ menggunakan Laravel & Filament</strong></p>
  <p>© 2025 PTSP MTsN 2 Kota Malang. Hak Cipta Dilindungi.</p>
</div>