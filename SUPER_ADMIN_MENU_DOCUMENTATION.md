# Dokumentasi Menu Super Admin - PTSP MTsN 2 Kota Malang

## Overview
Super Admin memiliki akses penuh ke semua fitur aplikasi PTSP. Dashboard ini mencakup manajemen sistem, monitoring, laporan, dan konfigurasi.

**Path:** `/suadmin`
**Warna Theme:** Amber (#F59E0B)
**Panel ID:** `suadmin`

---

## 📊 Dashboard

### Widgets
1. **System Overview Widget**
   - Total Pengguna (dengan chart)
   - Total Layanan
   - Total Tiket (dengan chart)
   - Pengunjung Bulan Ini

2. **Service Performance Widget**
   - Line chart kinerja layanan 30 hari terakhir
   - Tracking jumlah tiket per hari

3. **Ticket Status Widget**
   - Doughnut chart distribusi status tiket
   - Status: Pending, Diproses, Selesai, Ditolak

---

## 📋 Menu Struktur

### 1. MASTER DATA

#### 1.1 Layanan (Services)
**Resource:** `ServiceResource`
**Path:** `/suadmin/services`
**Icon:** `heroicon-o-square-3-stack-3d`

**Fitur:**
- ✅ CRUD Layanan lengkap
- ✅ 14 Komponen Standar Pelayanan (Permen PANRB 15/2014)
- ✅ Filter & Search
- ✅ Bulk Actions
- ✅ Export (Excel/PDF)

**Fields:**
- Nama Layanan
- Slug
- Kategori
- Deskripsi
- **14 Komponen Wajib:**
  1. Dasar Hukum
  2. Persyaratan
  3. Sistem, Mekanisme, Prosedur
  4. Jangka Waktu Penyelesaian
  5. Biaya/Tarif
  6. Produk Pelayanan
  7. Sarana & Prasarana
  8. Kompetensi Pelaksana
  9. Pengawasan Internal
  10. Penanganan Pengaduan
  11. Jumlah Pelaksana
  12. Jaminan Pelayanan
  13. Jaminan Keamanan
  14. Evaluasi Kinerja
- Mode (Online/Offline/Both)
- Is Active
- Is Digital Product
- Estimated Days

**Views:**
- List (Table dengan filter)
- Create/Edit (Form dengan sections)
- Detail (Info card)

---

#### 1.2 Kategori Layanan (Service Categories)
**Resource:** `ServiceCategoryResource`
**Path:** `/suadmin/service-categories`
**Icon:** `heroicon-o-tag`

**Fitur:**
- ✅ CRUD Kategori
- ✅ Hierarchical categories
- ✅ Slug auto-generate

**Fields:**
- Nama Kategori
- Slug
- Deskripsi
- Icon
- Urutan (Sort Order)
- Parent Category (optional)
- Is Active

---

### 2. MANAJEMEN TIKET & APPROVAL

#### 2.1 Tiket Layanan (Tickets)
**Resource:** `TicketResource`
**Path:** `/suadmin/tickets`
**Icon:** `heroicon-o-ticket`

**Fitur:**
- ✅ View all tickets (Online & Offline)
- ✅ Filter by status, service, channel
- ✅ Advanced search
- ✅ Bulk status update
- ✅ Export report
- ✅ Timeline tracking
- ✅ File management

**Fields:**
- Ticket Number (auto-generated)
- Service
- User (pemohon)
- Status (pending, in_progress, pending_approval, approved, completed, rejected)
- Priority (low, normal, high, urgent)
- Channel (online/offline)
- Assigned To
- Form Data (JSON)
- Attachments
- Workflow History (JSON)
- Notes
- Estimated Completion Date
- Completed At
- Payment Status
- Payment Amount

**Tabs:**
- Detail
- Attachments (uploaded files)
- Timeline (workflow history)
- Notes (internal notes)
- Payment Info (if applicable)

**Actions:**
- Assign to user
- Change status
- Add note
- Upload output file
- Send notification
- Print receipt

---

### 3. BUKU TAMU & PENGUNJUNG

#### 3.1 Buku Tamu (Visitors)
**Resource:** `VisitorResource`
**Path:** `/suadmin/visitors`
**Icon:** `heroicon-o-user-group`

**Fitur (Modul 1):**
- ✅ View all visitors
- ✅ Check-in/Check-out tracking
- ✅ Active visitors dashboard
- ✅ Photo management
- ✅ Visitor pass generation
- ✅ Export daily report
- ✅ Statistics & analytics

**Fields:**
- Nama
- Email
- Phone
- Institution/Keperluan
- Pihak yang Dituju
- Foto
- Check-in Time
- Check-out Time
- Purpose
- Notes
- Is Obscured (privacy)

**Filters:**
- Status (active/checked-out)
- Date range
- Purpose
- Institution

**Widgets:**
- Active Visitors Count
- Today's Visitors
- Average Visit Duration

---

### 4. PENGADUAN & WHISTLEBLOWING

#### 4.1 Pengaduan Masyarakat (Complaints)
**Resource:** `ComplaintResource`
**Path:** `/suadmin/complaints`
**Icon:** `heroicon-o-megaphone`

**Fitur (Modul 10):**
- ✅ View all complaints
- ✅ Categorize (pengaduan/saran)
- ✅ Assign to handler
- ✅ Track SLA
- ✅ Response management
- ✅ Status workflow
- ✅ Export report

**Fields:**
- Complaint Number
- Type (pengaduan/saran)
- Subject
- Description
- Reporter Name
- Reporter Email
- Reporter Phone
- Category
- Service Related (optional)
- Status (pending, in_review, resolved, closed)
- Priority
- Assigned To
- Response
- Responded At
- Resolved At
- Attachments

**Status Flow:**
```
pending → in_review → resolved → closed
```

**SLA:**
- Response: 1x24 jam
- Resolution: 3-7 hari kerja (sesuai kompleksitas)

---

#### 4.2 Whistleblowing
**Integrated in Complaints Resource**
**Checkbox:** `is_whistleblowing`

**Special Features:**
- 🔒 Anonymous option
- 🔒 Encrypted data
- 🔒 Restricted access (Super Admin only)
- 🔒 Protected identity

---

### 5. PENGAWASAN & EVALUASI

#### 5.1 Manajemen Survey
**Page:** `SurveyManagement`
**Path:** `/suadmin/survey-management`
**Icon:** `heroicon-o-clipboard-document-list`

**Sections:**

**A. Survey Kepuasan Masyarakat (SKM)**
- 📝 9 Unsur SKM (Permen PANRB 14/2017)
- 📊 Statistics dashboard
- 📄 Link to survey responses
- 📥 Export results

**9 Unsur SKM:**
1. Persyaratan
2. Sistem, Mekanisme, Prosedur
3. Waktu Penyelesaian
4. Biaya/Tarif
5. Produk Spesifikasi Jenis Pelayanan
6. Kompetensi Pelaksana
7. Perilaku Pelaksana
8. Penanganan Pengaduan
9. Sarana dan Prasarana

**B. Survey Persepsi Anti Korupsi (SPAK)**
- 🛡️ Aspek integritas pelayanan
- 📊 Dashboard IPAK
- 📄 Link to SPAK report

**Aspek SPAK:**
- Tidak ada pungli
- Tidak ada gratifikasi
- Tidak ada diskriminasi
- Tidak ada percaloan
- Transparansi dan akuntabilitas

---

#### 5.2 Laporan SKM
**Page:** `SKMReport`
**Path:** `/suadmin/skm-report`
**Icon:** `heroicon-o-chart-bar`

**Metrics:**
- **IKM (Indeks Kepuasan Masyarakat)**
  - Rumus: (Total Skor / (Jumlah Unsur × Responden)) × 25
  - Range: 25.00 - 100.00

**Interpretasi IKM:**
| Nilai IKM | Mutu Pelayanan | Kinerja Unit Pelayanan |
|---|---|---|
| 25.00 - 64.99 | D | Tidak Baik |
| 65.00 - 76.60 | C | Kurang Baik |
| 76.61 - 88.30 | B | Baik |
| 88.31 - 100.00 | A | Sangat Baik |

**Features:**
- 📊 IKM Score dengan interpretasi
- 📈 Total responden
- 📉 Skor rata-rata per unsur
- 📅 Filter by date range
- 📥 Export Excel
- 📄 Export PDF
- 🖨️ Print report

**Charts:**
- Bar chart per unsur
- Trend chart (monthly)
- Distribution chart

---

#### 5.3 Laporan SPAK
**Page:** `SPAKReport`
**Path:** `/suadmin/spak-report`
**Icon:** `heroicon-o-shield-check`

**Metrics:**
- **IPAK (Indeks Persepsi Anti Korupsi)**
- Persentase per aspek
- Trend analysis

**Display:**
- 📊 IPAK Score
- 📊 5 Aspek integritas (progress bars)
- 📈 Trend chart
- 📊 Comparison chart
- 📥 Export options

---

#### 5.4 Laporan Kinerja Pelayanan
**Page:** `PerformanceReport`
**Path:** `/suadmin/performance-report`
**Icon:** `heroicon-o-chart-pie`

**Key Performance Indicators (KPI):**

1. **Waktu Penyelesaian Rata-rata**
   - Dalam jam
   - Comparison vs. SLA
   - Trend chart

2. **Tingkat Penyelesaian (Completion Rate)**
   - Persentase tiket selesai
   - Total tiket bulan ini
   - Month-over-month comparison

3. **Ketepatan Waktu (On-Time Rate)**
   - Persentase selesai sesuai SLA
   - Early vs. Late analysis

**Channel Distribution:**
- Online tickets (via Portal)
- Offline tickets (Walk-in)
- Comparison chart

**Service Performance:**
- Top 10 layanan terpopuler
- Completion rate per layanan
- Average time per layanan
- Table sortable

**Export Options:**
- Excel (detailed data)
- PDF (formatted report)
- Print (printer-friendly)

---

### 6. KEAMANAN APLIKASI

#### 6.1 Security Dashboard
**Path:** `/suadmin/admin/security/dashboard`
**Icon:** `heroicon-o-shield-check`

**Features:**
- 🛡️ System security overview
- 🔍 Security scan
- 📊 Activity monitoring
- 🚫 Blocked IPs management
- ⚡ Rate limiting config
- 🔒 Maintenance mode

**Metrics:**
- Failed login attempts
- Suspicious activities
- Blocked IPs count
- Last security scan

**Actions:**
- Run security scan
- Block/Unblock IP
- Update rate limits
- Clear cache
- Enable/Disable maintenance

---

### 7. KONFIGURASI

#### 7.1 Pengaturan Umum
**Page:** `Settings`
**Path:** `/suadmin/settings`
**Icon:** `heroicon-o-cog-6-tooth`

**Sections:**

**A. Informasi Aplikasi**
- Nama Aplikasi (Singkat)
- Nama Aplikasi (Lengkap)

**B. Jam Operasional** ⏰
Ditampilkan di landing page & footer:
- Senin-Kamis:
  - Jam Buka (TimePicker)
  - Jam Tutup (TimePicker)
- Jumat:
  - Jam Buka (TimePicker)
  - Jam Tutup (TimePicker)

**C. Kontak & Alamat** 📧
- Email
- Telepon
- Alamat (Textarea)

**D. Maintenance Mode** 🔧
- Toggle maintenance mode
- Menutup akses aplikasi sementara
- Display maintenance page

**Save Button:** Simpan Pengaturan

---

## 🎨 Navigation Groups

Navigation menu di Super Admin diorganisir dalam groups:

### Group 1: Dashboard
- Dashboard

### Group 2: Master Data
- Layanan
- Kategori Layanan

### Group 3: Manajemen Tiket
- Tiket Layanan

### Group 4: Pengunjung
- Buku Tamu

### Group 5: Pengaduan
- Pengaduan Masyarakat
- (Whistleblowing integrated)

### Group 6: Pengawasan & Evaluasi
- Manajemen Survey
- Laporan SKM
- Laporan SPAK
- Laporan Kinerja

### Group 7: Keamanan
- Security Dashboard
- Blocked IPs
- Activity Logs

### Group 8: Konfigurasi
- Pengaturan Umum
- Maintenance Mode

---

## 🔐 Permissions

Super Admin has access to ALL resources and pages.

**Resource Permissions:**
- `view_any`
- `view`
- `create`
- `update`
- `delete`
- `restore`
- `force_delete`
- `replicate`
- `reorder`

---

## 📊 Widgets Summary

### Dashboard Widgets:
1. **SystemOverviewWidget** (4 stats)
2. **ServicePerformanceWidget** (line chart)
3. **TicketStatusWidget** (doughnut chart)

### Inline Widgets:
- Active Visitors (in VisitorResource)
- Complaint Stats (in ComplaintResource)
- Ticket Statistics (in TicketResource)

---

## 🚀 Quick Actions

Super Admin can perform these quick actions from dashboard:

1. **Manage Services** → Add/Edit layanan
2. **Manage Categories** → Organize kategori
3. **View Pending Tickets** → Review tiket baru
4. **Check Active Visitors** → Monitor tamu
5. **Review Complaints** → Handle pengaduan
6. **View Reports** → Analytics & insights
7. **Security Check** → Run security scan
8. **Update Settings** → Configure system

---

## 📱 Responsive Design

All pages responsive untuk:
- Desktop (>= 1024px)
- Tablet (768px - 1023px)
- Mobile (< 768px)

**Mobile Optimizations:**
- Collapsible sections
- Touch-optimized buttons
- Swipe gestures
- Simplified tables (cards)

---

## 🎯 Use Cases

### Use Case 1: Update Jam Operasional
```
1. Navigate to /suadmin/settings
2. Scroll to "Jam Operasional"
3. Update TimePickers
4. Click "Simpan Pengaturan"
5. Changes reflected on landing page & footer
```

### Use Case 2: Generate Monthly Report
```
1. Navigate to /suadmin/performance-report
2. Review KPIs
3. Check channel distribution
4. Review top services
5. Click "Export Excel" or "Export PDF"
6. Download report
```

### Use Case 3: Handle Complaint
```
1. Navigate to /suadmin/complaints
2. Filter: status = "pending"
3. Open complaint detail
4. Assign to handler
5. Add response
6. Update status to "resolved"
7. System sends notification to reporter
```

### Use Case 4: Monitor Security
```
1. Navigate to /suadmin/admin/security/dashboard
2. Review failed login attempts
3. Check suspicious activities
4. Run security scan
5. Block suspicious IPs if needed
6. Review activity logs
```

---

## 🛠️ Maintenance & Updates

### Update Application:
1. Enable Maintenance Mode (Settings page)
2. Pull latest changes
3. Run migrations
4. Clear cache
5. Test functionality
6. Disable Maintenance Mode

### Backup Data:
Super Admin should regularly:
- Export all tickets data
- Export survey responses
- Export visitor logs
- Backup database
- Store in secure location

---

## 📈 Future Enhancements

Planned features for Super Admin:

1. **Advanced Analytics**
   - Predictive analytics
   - AI-powered insights
   - Custom report builder

2. **API Management**
   - API keys management
   - Rate limiting per key
   - Usage statistics

3. **Notification Center**
   - Centralized notifications
   - Custom notification rules
   - Email/SMS templates

4. **Audit Trail**
   - Comprehensive logging
   - User activity tracking
   - Data change history

5. **Multi-language Support**
   - Interface translation
   - Dynamic content translation
   - RTL support

---

## 📞 Support

For technical support:
- Email: admin@mtsn2kotamalang.sch.id
- Phone: (0341) 123456
- Documentation: /docs

---

**Last Updated:** 2025-11-03
**Version:** 1.0.0
**Maintained By:** Development Team PTSP MTsN 2 Kota Malang
