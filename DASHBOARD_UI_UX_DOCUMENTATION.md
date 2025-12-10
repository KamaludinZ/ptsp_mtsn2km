# Dokumentasi UI/UX Dashboard PTSP MTsN 2 Kota Malang

## Overview
Sistem dashboard PTSP menggunakan bootsrap atau tailwind dengan 8 panel berbeda untuk setiap role, masing-masing dengan fungsi dan tampilan yang disesuaikan dengan kebutuhan pengguna.

---

**Modul:** 12 (Master Konfigurasi) & 13 (Dashboard Eksekutif)
**Warna:** Amber

### Widgets yang Ditampilkan:

#### A. System Overview Widget (Stats)
Menampilkan 4 metric utama sistem:
- **Total Pengguna**: Jumlah user terdaftar dengan sparkline chart
- **Total Layanan**: Jumlah layanan (dengan info layanan aktif)
- **Total Tiket**: Jumlah tiket dengan chart dan info hari ini
- **Pengunjung Bulan Ini**: Statistik visitor bulan berjalan

#### B. Service Performance Widget (Line Chart)
- Grafik garis menampilkan kinerja layanan 30 hari terakhir
- Tracking jumlah tiket per hari
- Warna: Blue gradient

#### C. Ticket Status Widget (Doughnut Chart)
- Visualisasi distribusi status tiket
- 4 Status: Pending (kuning), Diproses (biru), Selesai (hijau), Ditolak (merah)
- Format: Pie/Doughnut chart

### Fitur Akses:
- Manajemen layanan (admin.services)
- Manajemen kategori layanan
- Security dashboard
- Semua resources Filament
- Konfigurasi workflow

### Layout:
```
┌─────────────────────────────────────────────────────────┐
│  [Total Pengguna] [Total Layanan] [Total Tiket] [Visitor] │
├─────────────────────────────────────────────────────────┤
│  📈 Service Performance (30 Days)                        │
│                                                          │
│  [Line Chart showing ticket trends]                     │
├───────────────────────┬─────────────────────────────────┤
│  🍩 Ticket Status      │  Quick Actions                  │
│  [Doughnut Chart]     │  • Manage Services              │
│                       │  • Manage Categories            │
│                       │  • Security Settings            │
└───────────────────────┴─────────────────────────────────┘
```

---

## 2. Admin Dashboard (`/admin`)
**Role:** Admin
**Modul:** Manajemen Sistem & Layanan
**Warna:** Blue

### Widgets yang Ditampilkan:

#### Service Management Widget (Stats)
- **Layanan Aktif**: Total layanan yang aktif
- **Pending Tickets**: Tiket yang perlu ditinjau
- **Diproses**: Tiket sedang dikerjakan

### Fitur Akses:
- Kelola layanan
- Kelola kategori layanan
- Lihat tiket

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Layanan Aktif] [Pending Tickets] [Diproses]      │
├────────────────────────────────────────────────────┤
│  Recent Activities                                 │
│  • Layanan baru ditambahkan                        │
│  • Tiket pending review                            │
└────────────────────────────────────────────────────┘
```

---

## 3. Kepala Sekolah Dashboard (`/kepala`)
**Role:** Kepala Sekolah
**Modul:** 8 (Approval) & 13 (Dashboard Eksekutif)
**Warna:** Purple

### Widgets yang Ditampilkan:

#### Approval Overview Widget (Stats)
- **Pending Approval**: Tiket menunggu persetujuan
- **Disetujui Hari Ini**: Approval yang sudah dibuat hari ini
- **Total Bulan Ini**: Total tiket masuk bulan berjalan

### Fitur Utama:
- Melihat daftar permohonan pending approval
- Approve/Reject dengan catatan
- Pilih jenis tanda tangan (TTE/TTD)
- Laporan kinerja (SKM/SPAK)

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Pending Approval] [Disetujui] [Total Bulan Ini]  │
├────────────────────────────────────────────────────┤
│  📋 Pending Approvals                              │
│  ┌──────────────────────────────────────────────┐ │
│  │ LAYANAN-202511-001  │  Surat Keterangan     │ │
│  │ Pemohon: Ahmad      │  ⏰ 2 hari kerja      │ │
│  │ [Setujui] [Tolak]                            │ │
│  └──────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────┘
```

---

## 4. Kepala TU Dashboard (`/katu`)
**Role:** Kepala TU
**Modul:** 13 (Dashboard Eksekutif) + Pengawasan Operasional
**Warna:** Indigo

### Widgets yang Ditampilkan:
- Sama dengan Kepala Sekolah (Approval Overview)
- Fokus: Pengawasan operasional TU

### Fitur Tambahan:
- Monitor team performance (petugas TU)
- Overdue tickets
- Laporan operasional

---

## 5. Waka Dashboard (`/waka`)
**Role:** Waka (Kesiswaan, Kurikulum, Sarpras, Humas)
**Modul:** 8 (Approval) - Section Specific
**Warna:** Teal

### Widgets yang Ditampilkan:
- Approval Overview Widget (sama dengan Kepala)
- Filtered sesuai bidang masing-masing

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Pending Approval] [Disetujui] [Total Minggu Ini] │
├────────────────────────────────────────────────────┤
│  Tiket untuk Bidang Saya                           │
│  (Filtered berdasarkan waka-kesiswaan, dll)        │
└────────────────────────────────────────────────────┘
```

---

## 6. Back Office/TU Dashboard (`/back`)
**Role:** Petugas TU
**Modul:** 7 (Antrian Tugas & Workflow Engine)
**Warna:** Green

### Widgets yang Ditampilkan:

#### Task Queue Widget (Stats)
- **Antrian Tugas**: Tiket baru yang belum diambil
- **Tugas Saya**: Tiket yang sedang dikerjakan user
- **Selesai Hari Ini**: Produk layanan yang sudah selesai

### Fitur Utama (Modul 7):
- **Unified Inbox**: Semua tiket (online & offline) dalam satu antrian
- Verifikasi berkas
- Validasi data
- Eskalasi/Disposisi ke unit lain
- Upload produk layanan digital
- Log audit setiap langkah

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Antrian Tugas] [Tugas Saya] [Selesai Hari Ini]   │
├────────────────────────────────────────────────────┤
│  📥 Antrian Tugas (Queue)                          │
│  ┌──────────────────────────────────────────────┐ │
│  │ LAYANAN-202511-002  │  Legalisir Rapor      │ │
│  │ Channel: Online     │  Status: Pending      │ │
│  │ [Ambil Tugas]                                │ │
│  ├──────────────────────────────────────────────┤ │
│  │ LAYANAN-202511-003  │  Surat Aktif          │ │
│  │ Channel: Offline    │  Status: Pending      │ │
│  │ [Ambil Tugas]                                │ │
│  └──────────────────────────────────────────────┘ │
├────────────────────────────────────────────────────┤
│  📋 Tugas Saya (My Tasks)                          │
│  • LAYANAN-202511-001 - Sedang diverifikasi       │
│  • Upload berkas output → Upload                  │
└────────────────────────────────────────────────────┘
```

---

## 7. Front Desk Dashboard (`/front`)
**Role:** Petugas Loket
**Modul:** 1 (Triage & Buku Tamu) & 2 (Registrasi Offline)
**Warna:** Orange

### Widgets yang Ditampilkan:

#### Visitor Management Widget (Stats)
- **Tamu Aktif**: Pengunjung yang sedang di sekolah (belum checkout)
- **Pengunjung Hari Ini**: Total check-in hari ini
- **Layanan Offline**: Registrasi layanan walk-in hari ini

### Fitur Utama:

#### Modul 1: Triage & Buku Tamu
- **Triage Decision**: Tamu atau Pemohon Layanan?
  - Jika **Tamu**:
    - Form check-in tamu
    - Ambil foto (webcam)
    - Generate visitor pass (badge digital)
    - Kirim via email/WA
    - Dashboard tamu aktif
    - Check-out
  - Jika **Pemohon Layanan**:
    - Arahkan ke Modul 2

#### Modul 2: Registrasi Layanan Offline
- Identifikasi pemohon (Guru/Pegawai/Siswa/Wali/Alumni/Instansi/Umum)
- Pilih layanan dari katalog
- Isi formulir digital
- Scan & upload berkas
- Generate nomor tiket
- Cetak tanda terima (email/WA)

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Tamu Aktif] [Pengunjung Hari Ini] [Layanan]      │
├────────────────────────────────────────────────────┤
│  🚪 Triage (Pemilahan)                             │
│  ┌──────────────────┬─────────────────────────┐   │
│  │ [Tamu Berkunjung]│ [Pemohon Layanan]       │   │
│  │ → Buku Tamu      │ → Registrasi Layanan    │   │
│  └──────────────────┴─────────────────────────┘   │
├────────────────────────────────────────────────────┤
│  👥 Tamu Aktif (Sedang Berkunjung)                 │
│  • Ahmad - Bertemu Waka Kurikulum - 10:30          │
│  • Siti - Vendor - 11:00 [Check-out]               │
├────────────────────────────────────────────────────┤
│  Quick Actions                                     │
│  • Check-in Tamu Baru                              │
│  • Registrasi Layanan Offline                      │
│  • Cetak Visitor Pass                              │
└────────────────────────────────────────────────────┘
```

---

## 8. Portal Dashboard (`/portal`)
**Role:** Regular Users (Guru, Pegawai, Siswa, Wali Murid, Alumni, Instansi, Umum)
**Modul:** 6 (Dashboard Pemohon & Pelacakan Tiket)
**Warna:** Cyan

### Widgets yang Ditampilkan:

#### My Tickets Widget (Stats)
- **Tiket Saya**: Total permohonan user
- **Sedang Diproses**: Tiket dengan status pending/in_progress
- **Selesai**: Tiket yang sudah completed (siap diunduh/diambil)

### Fitur Utama (Modul 6):
- Riwayat pengajuan layanan
- Real-time tracking status tiket
- Download produk layanan digital (harus login)
- Notifikasi perubahan status

### Layout:
```
┌────────────────────────────────────────────────────┐
│  [Tiket Saya] [Sedang Diproses] [Selesai]          │
├────────────────────────────────────────────────────┤
│  🎫 Tiket Saya                                      │
│  ┌──────────────────────────────────────────────┐ │
│  │ LAYANAN-202511-005                           │ │
│  │ Surat Keterangan Aktif                       │ │
│  │ Status: ✅ Selesai                            │ │
│  │ [Download PDF] [Lihat Detail]                │ │
│  ├──────────────────────────────────────────────┤ │
│  │ LAYANAN-202511-010                           │ │
│  │ Legalisir Ijazah                             │ │
│  │ Status: ⏰ Sedang Diproses (Verifikasi TU)   │ │
│  │ [Lacak] [Lihat Detail]                       │ │
│  └──────────────────────────────────────────────┘ │
├────────────────────────────────────────────────────┤
│  Quick Actions                                     │
│  • Ajukan Layanan Baru                             │
│  • Lacak Tiket (tanpa login)                       │
│  • Isi Survei Kepuasan                             │
└────────────────────────────────────────────────────┘
```

---

## Tracking Tiket Tanpa Login (Modul 6)
**Path:** `/portal/track-ticket` (public)

### Fitur:
- Input nomor tiket
- Lihat status real-time
- **TIDAK** bisa download produk digital (wajib login)

### Layout:
```
┌────────────────────────────────────────────────────┐
│  🔍 Lacak Tiket                                     │
│  ┌──────────────────────────────────────────────┐ │
│  │ Nomor Tiket: [________________] [Lacak]     │ │
│  └──────────────────────────────────────────────┘ │
├────────────────────────────────────────────────────┤
│  Status: LAYANAN-202511-005                        │
│  • Jenis: Surat Keterangan Aktif                   │
│  • Status: Selesai                                 │
│  • Timeline:                                       │
│    ✅ Diterima - 1 Nov 2025, 10:00                 │
│    ✅ Diverifikasi - 1 Nov, 14:30                  │
│    ✅ Disetujui Kepsek - 2 Nov, 09:00              │
│    ✅ Selesai - 2 Nov, 11:00                       │
│  • Produk: PDF Surat (Login untuk download)        │
└────────────────────────────────────────────────────┘
```

---

## Design System

### Warna Panel
| Role | Primary Color | Hex |
|---|---|---|
| Super Admin | Amber | `#F59E0B` |
| Admin | Blue | `#3B82F6` |
| Kepala | Purple | `#A855F7` |
| Kepala TU | Indigo | `#6366F1` |
| Waka | Teal | `#14B8A6` |
| Back Office | Green | `#22C55E` |
| Front Desk | Orange | `#F97316` |
| Portal | Cyan | `#06B6D4` |

### Typography
- **Font:** Inter (via Bunny Fonts)
- **Heading:** 600-700 weight
- **Body:** 400-500 weight

### Icons
- **Library:** Heroicons (Filament default)
- **Style:** Outline untuk navigasi, Solid untuk stats

### Spacing
- **Stats Cards:** 4 kolom responsive (2 di tablet, 1 di mobile)
- **Charts:** Full width dengan aspect ratio 2:1
- **Padding:** Konsisten 1rem (16px) internal, 2rem eksternal

---

## Responsive Design

### Desktop (>= 1024px)
- Sidebar fixed
- Stats: 4 kolom
- Charts: 2 kolom side-by-side

### Tablet (768px - 1023px)
- Sidebar collapse dengan toggle
- Stats: 2 kolom
- Charts: 1 kolom full width

### Mobile (< 768px)
- Sidebar drawer
- Stats: 1 kolom stack
- Charts: 1 kolom full width
- Touch-optimized buttons

---

## Aksesibilitas (WCAG 2.1 AA)

### Implementasi:
- ✅ Contrast ratio minimal 4.5:1
- ✅ Keyboard navigation (Tab, Enter, Esc)
- ✅ Screen reader labels (aria-label)
- ✅ Focus indicators jelas
- ✅ Error messages deskriptif
- ✅ Responsive text sizing

### Testing:
```bash
# Lighthouse Accessibility Score Target: >= 90
npm run lighthouse:a11y
```

---

## Performance Metrics

### Target KPI:
- **First Contentful Paint (FCP):** < 1.5s
- **Time to Interactive (TTI):** < 3.5s
- **Largest Contentful Paint (LCP):** < 2.5s
- **Cumulative Layout Shift (CLS):** < 0.1

### Optimization:
- Lazy loading untuk charts
- Widget caching (5 minutes)
- Database query optimization
- Image compression (visitor photos)

---

## Keamanan Dashboard

### CSP Policy
- Fonts: `fonts.bunny.net` (allowed)
- Avatars: `ui-avatars.com` (allowed)
- Scripts: `'self'` only (production)
- Styles: `'self'` + `'unsafe-inline'` (Filament requirement)

### Authentication
- Setiap panel protected oleh `Filament\Http\Middleware\Authenticate`
- Role-based access control via Spatie Permission
- Session timeout: 120 minutes

---

## Maintenance & Updates

### Update Widgets:
```bash
# After modifying widget logic
php artisan filament:cache-components
php artisan optimize:clear
```

### Add New Widget:
1. Create widget: `php artisan make:filament-widget WidgetName`
2. Place in: `app/Filament/Widgets/{RoleName}/`
3. Register in PanelProvider
4. Clear cache

---

## Dokumentasi Teknis

### File Structure:
```
app/Filament/
├── Widgets/
│   ├── SuperAdmin/
│   │   ├── SystemOverviewWidget.php
│   │   ├── ServicePerformanceWidget.php
│   │   └── TicketStatusWidget.php
│   ├── Admin/
│   │   └── ServiceManagementWidget.php
│   ├── Kepala/
│   │   └── ApprovalOverviewWidget.php
│   ├── BackOffice/
│   │   └── TaskQueueWidget.php
│   ├── FrontDesk/
│   │   └── VisitorManagementWidget.php
│   └── Portal/
│       └── MyTicketsWidget.php
│
app/Providers/Filament/
├── StaffAdminPanelProvider.php (Admin - /admin)
├── KepalaPanelProvider.php (Kepala - /kepala)
├── KaTUPanelProvider.php (Kepala TU - /katu)
├── WakaPanelProvider.php (Waka - /waka)
├── BackOfficePanelProvider.php (TU - /back)
├── FrontDeskPanelProvider.php (Loket - /front)
└── PortalPanelProvider.php (Portal - /portal)
```

---

## Troubleshooting

### Widget Tidak Muncul
```bash
php artisan filament:upgrade
php artisan filament:cache-components
php artisan optimize:clear
```

### Data Tidak Terupdate
- Check widget `$cacheLifetime` property
- Clear cache: `php artisan cache:clear`

### Permission Error
- Verify role in database: `SELECT * FROM model_has_roles WHERE model_id = {user_id};`
- Check helper function: `get_dashboard_route_for_user()`

---

**Last Updated:** 2025-11-03
**Version:** 1.0.0
**Maintained By:** Development Team PTSP MTsN 2 Kota Malang
