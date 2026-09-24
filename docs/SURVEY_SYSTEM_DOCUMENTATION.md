# Dokumentasi Sistem Survei PTSP MTsN 2 Kota Malang

## Overview
Sistem survei lengkap untuk mengukur Kepuasan Masyarakat (SKM) dan Persepsi Anti Korupsi (SPAK) sesuai Permenpan 14/2017.

---

## 🎯 Fitur Utama

### 1. **Form Survei Multi-Step (3 Halaman)**
- Halaman 1: Identitas Responden
- Halaman 2: Pertanyaan SKM (9 pertanyaan)
- Halaman 3: Pertanyaan SPAK (10 pertanyaan)

### 2. **Dashboard Super Admin**
- CRUD Pertanyaan Survei
- View Data Responden
- Export Excel
- Perhitungan IKM & IPAK otomatis
- Publikasi Hasil Survei

### 3. **Perhitungan Sesuai Permenpan 14/2017**
- IKM (Indeks Kepuasan Masyarakat)
- IPAK (Indeks Persepsi Anti Korupsi)
- Laporan Bulanan & Triwulan

---

## 📋 Struktur Database

### Table: `survey_questions`
```sql
CREATE TABLE survey_questions (
    id BIGINT PRIMARY KEY,
    type VARCHAR(20), -- 'identity', 'skm', 'spak'
    question TEXT,
    options JSON, -- Array pilihan jawaban
    field_type VARCHAR(20), -- 'select', 'radio', 'text', 'number'
    order INT DEFAULT 0,
    is_required BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Table: `survey_responses`
```sql
CREATE TABLE survey_responses (
    id BIGINT PRIMARY KEY,
    -- Identitas Responden
    service_type VARCHAR(255), -- Jenis pelayanan yang disurvei
    full_name VARCHAR(255),
    age_range VARCHAR(50), -- Dibawah 20, 21-30, dst
    gender ENUM('laki-laki', 'perempuan'),
    education VARCHAR(50),
    occupation VARCHAR(255),

    -- Jawaban SKM (JSON untuk fleksibilitas)
    skm_answers JSON, -- Array of {question_id: answer_value}
    skm_score DECIMAL(5,2), -- Skor SKM

    -- Jawaban SPAK (JSON untuk fleksibilitas)
    spak_answers JSON, -- Array of {question_id: answer_value}
    spak_score DECIMAL(5,2), -- Skor SPAK

    -- Metadata
    ip_address VARCHAR(45),
    user_agent TEXT,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Table: `survey_publications`
```sql
CREATE TABLE survey_publications (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    cover_image VARCHAR(255), -- Path to cover image
    published_date DATE,
    document_url VARCHAR(255), -- External link
    document_path VARCHAR(255), -- Uploaded PDF path
    description TEXT,
    type ENUM('skm', 'spak', 'combined'),
    period ENUM('monthly', 'quarterly', 'yearly'),
    month INT NULL, -- 1-12
    quarter INT NULL, -- 1-4
    year INT,
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 📝 Form Survei (Public)

### **Halaman 1: Identitas Responden**

**URL:** `/survey/identity` atau `/supervision/skm-survey`

```php
// Fields
1. Pilih Jenis Pelayanan (Dropdown/Select)
   - Mutasi Siswa Masuk
   - Mutasi Siswa Keluar
   - Penerbitan Surat Rekomendasi Siswa
   - Penerimaan Peserta Didik Baru
   - Izin Melaksanakan Penelitian/Observasi
   - Selesai Melaksanakan Penelitian/Observasi
   - Surat Keterangan Kerusakan Ijazah
   - Surat Keterangan Pengganti Ijazah Hilang
   - Legalisasi Ijazah Offline
   - Pengambilan Ijazah
   - Perbaikan Kesalahan Penulisan Ijazah
   - Penerimaan Tamu Studi Banding
   - Penerimaan Tamu Dinas
   - Pengaduan Masyarakat
   - SOP Kerjasama dengan Wartawan
   - Screening Kesehatan Siswa
   - Penerimaan Iuran Komite

2. Nama Lengkap (Text Input)

3. Usia (Radio Buttons)
   - Dibawah 20 Tahun
   - 21 s.d 30 Tahun
   - 31 s.d 40 Tahun
   - 41 s.d 50 Tahun
   - Diatas 50 Tahun

4. Jenis Kelamin (Radio Buttons)
   - Laki-laki
   - Perempuan

5. Pendidikan (Select/Dropdown)
   - SD
   - SMP
   - SMA
   - D3
   - D4/S1
   - S2
   - S3

6. Pekerjaan (Select/Dropdown)
   - PNS/TNI/POLRI
   - Pegawai Swasta
   - Wiraswasta
   - Petani/Pekebun
   - Pelajar/Mahasiswa
   - Lainnya

[Button: Lanjut ke SKM →]
```

---

### **Halaman 2: Survei Kepuasan Masyarakat (SKM)**

**9 Unsur SKM sesuai Permenpan 14/2017**

Semua pertanyaan menggunakan skala 4 point:
- **1** = Tidak [Sesuai/Mudah/Cepat/dll]
- **2** = Kurang [Sesuai/Mudah/Cepat/dll]
- **3** = [Sesuai/Mudah/Cepat/dll]
- **4** = Sangat [Sesuai/Mudah/Cepat/dll]

```
U1: PERSYARATAN
Pertanyaan: "Bagaimana pendapat Saudara tentang kesesuaian persyaratan layanan di MTsN 2 Kota Malang dengan jenis pelayanannya?"
○ Tidak Sesuai (1)
○ Kurang Sesuai (2)
○ Sesuai (3)
○ Sangat Sesuai (4)

U2: SISTEM, MEKANISME, DAN PROSEDUR
Pertanyaan: "Bagaimana pemahaman Saudara tentang kemudahan prosedur pelayanan di MTsN 2 Kota Malang?"
○ Tidak Mudah (1)
○ Kurang Mudah (2)
○ Mudah (3)
○ Sangat Mudah (4)

U3: WAKTU PENYELESAIAN
Pertanyaan: "Bagaimana pendapat Saudara tentang kecepatan pelayanan di MTsN 2 Kota Malang?"
○ Tidak Cepat (1)
○ Kurang Cepat (2)
○ Cepat (3)
○ Sangat Cepat (4)

U4: PRODUK SPESIFIKASI JENIS PELAYANAN
Pertanyaan: "Bagaimana pendapat Saudara tentang Jenis pelayanan ini di MTsN 2 Kota Malang?"
○ Tidak Bagus (1)
○ Kurang Bagus (2)
○ Bagus (3)
○ Sangat Bagus (4)

U5: KOMPETENSI PELAKSANA
Pertanyaan: "Bagaimana pendapat Saudara tentang kemampuan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan?"
○ Tidak Mampu (1)
○ Kurang Mampu (2)
○ Mampu (3)
○ Sangat Mampu (4)

U6: PERILAKU PELAKSANA
Pertanyaan: "Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan?"
○ Tidak Sopan (1)
○ Kurang Sopan (2)
○ Sopan (3)
○ Sangat Sopan (4)

U7: MAKLUMAT PELAYANAN
Pertanyaan: "Bagaimana pendapat Saudara tentang maklumat pelayanan di MTsN 2 Kota Malang?"
○ Tidak Jelas (1)
○ Kurang Jelas (2)
○ Jelas (3)
○ Sangat Jelas (4)

U8: PENANGANAN PENGADUAN, SARAN DAN MASUKAN
Pertanyaan: "Bagaimana pendapat Saudara tentang Sarana dan Penanganan atas Pengaduan, Kritik dan Saran pelayanan di MTsN 2 Kota Malang?"
○ Tidak Bagus (1)
○ Kurang Bagus (2)
○ Bagus (3)
○ Sangat Bagus (4)

U9: KESESUAIAN BIAYA PELAYANAN
Pertanyaan: "Bagaimana pendapat Saudara tentang kesesuaian antara biaya pelayanan dengan yang ada pada standar pelayanan di MTsN 2 Kota Malang (semua jenis layanan gratis)?"
○ Selalu Tidak Sesuai (1)
○ Terkadang Sesuai (2)
○ Sesuai (3)
○ Selalu Sesuai (4)

[Button: ← Kembali] [Button: Lanjut ke SPAK →]
```

---

### **Halaman 3: Indeks Persepsi Anti Korupsi (SPAK)**

**10 Pertanyaan SPAK**

Skala 4 point (disesuaikan per pertanyaan):
- Frekuensi: Sangat sering (1), Sering (2), Jarang (3), Tidak Pernah (4)
- Transparansi: Tidak Transparan (1), Kurang Transparan (2), Transparan (3), Sangat Transparan (4)

```
P1: Apakah Saudara penah mengalami atau mengetahui adanya manipulasi peraturan di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P2: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang menyalahgunaan jabatan di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P3: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang menjual pengaruh di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P4: Bagaimana menurut Saudara dengan transparansi biaya yang ada di MTsN 2 Kota Malang?
○ Tidak Transparan (1)
○ Kurang Transparan (2)
○ Transparan (3)
○ Sangat Transparan (4)

P5: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang meminta biaya tambahan diluar ketentuan dan standar pelayanan di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P6: Apakah Saudara penah mengetahui adanya pemberian hadiah kepada petugas di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P7: Bagaimana menurut Saudara dengan transparansi transaksi pembayaran yang ada di MTsN 2 Kota Malang?
○ Tidak Transparan (1)
○ Kurang Transparan (2)
○ Transparan (3)
○ Sangat Transparan (4)

P8: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang melakukan praktik percaloan di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P9: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang melakukan kecurangan dalam pelayanan di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

P10: Apakah Saudara penah mengalami atau mengetahui adanya petugas yang melakukan transaksi rahasia dalam melayani di MTsN 2 Kota Malang?
○ Sangat sering (1)
○ Sering (2)
○ Jarang (3)
○ Tidak Pernah (4)

[Button: ← Kembali] [Button: Selesai & Kirim]
```

---

## 🧮 Perhitungan Sesuai Permenpan 14/2017

### **IKM (Indeks Kepuasan Masyarakat)**

#### Rumus:
```
IKM = (Total Nilai Persepsi Per Unsur / Total Unsur) × Nilai Penimbang

Dimana:
- Total Unsur = 9
- Nilai Penimbang = 25
- Skala Jawaban: 1 - 4
```

#### Langkah Perhitungan:
```php
// 1. Hitung Nilai Rata-rata Per Unsur
$nilaiRataRata = [];
foreach ($unsur as $u) {
    $totalNilai = sum($u->jawaban); // Sum semua jawaban untuk unsur ini
    $jumlahResponden = count($u->jawaban);
    $nilaiRataRata[$u] = $totalNilai / $jumlahResponden;
}

// 2. Hitung NRR (Nilai Rata-rata Tertimbang)
$NRR = array_sum($nilaiRataRata) / 9; // 9 unsur

// 3. Hitung IKM
$IKM = $NRR * 25;
```

#### Interpretasi IKM:
| Nilai IKM | Mutu Pelayanan | Kinerja |
|---|---|---|
| 25.00 - 64.99 | D | Tidak Baik |
| 65.00 - 76.60 | C | Kurang Baik |
| 76.61 - 88.30 | B | Baik |
| 88.31 - 100.00 | A | Sangat Baik |

#### Contoh:
```
Unsur 1: (3+4+3+4+3) / 5 responden = 3.4
Unsur 2: (4+4+3+4+4) / 5 responden = 3.8
... (9 unsur)

NRR = (3.4 + 3.8 + ... + 3.6) / 9 = 3.55
IKM = 3.55 × 25 = 88.75 (Sangat Baik)
```

---

### **IPAK (Indeks Persepsi Anti Korupsi)**

#### Rumus:
```
IPAK = (Total Skor SPAK / Skor Maksimal) × 100

Dimana:
- Total Pertanyaan SPAK = 10
- Skala Jawaban: 1 - 4
- Skor Maksimal = 10 × 4 = 40
```

#### Langkah Perhitungan:
```php
// 1. Hitung Total Skor per Responden
$totalSkor = array_sum($respondenAnswers); // Sum of P1-P10

// 2. Hitung IPAK
$skorMaksimal = 10 * 4; // 40
$IPAK = ($totalSkor / $skorMaksimal) * 100;
```

#### Interpretasi IPAK:
| Nilai IPAK | Kategori |
|---|---|
| 85.00 - 100.00 | Sangat Baik |
| 70.00 - 84.99 | Baik |
| 55.00 - 69.99 | Kurang Baik |
| 0.00 - 54.99 | Tidak Baik |

#### Contoh:
```
Responden A:
P1=4, P2=4, P3=4, P4=3, P5=4, P6=4, P7=3, P8=4, P9=4, P10=4
Total = 38

IPAK = (38 / 40) × 100 = 95% (Sangat Baik)
```

---

## 🎛️ Dashboard Super Admin

### **Menu: Pengaturan Survei**

Path: `/suadmin/survey-management`

#### Tab 1: Pertanyaan Identitas
**CRUD untuk pertanyaan identitas:**
- Jenis Pelayanan (Dropdown options)
- Nama Lengkap
- Usia (Range options)
- Jenis Kelamin
- Pendidikan
- Pekerjaan

**Actions:**
- ✏️ Edit Options (untuk dropdown)
- ✅ Set Required/Optional
- 🔄 Reorder Questions

---

#### Tab 2: Pertanyaan SKM
**CRUD untuk 9 Unsur SKM:**
- Edit Pertanyaan
- Edit 4 Pilihan Jawaban
- Set Bobot (default: sama)
- Active/Inactive

**Table:**
| Unsur | Pertanyaan | Pilihan | Status |
|---|---|---|---|
| U1 | Bagaimana pendapat Saudara... | 4 options | ✅ Active |
| U2 | Bagaimana pemahaman Saudara... | 4 options | ✅ Active |
| ... | ... | ... | ... |

**Actions:**
- ✏️ Edit
- 📊 View Statistics
- 🔄 Reorder

---

#### Tab 3: Pertanyaan SPAK
**CRUD untuk 10 Pertanyaan SPAK:**
- Edit Pertanyaan
- Edit 4 Pilihan Jawaban
- Set Bobot (default: sama)
- Active/Inactive

**Table:**
| No | Pertanyaan | Pilihan | Status |
|---|---|---|---|
| P1 | Apakah Saudara penah mengalami... | 4 options | ✅ Active |
| P2 | Apakah Saudara penah mengalami... | 4 options | ✅ Active |
| ... | ... | ... | ... |

---

#### Tab 4: Data Responden
**View & Export data responden:**

**Filters:**
- Jenis Pelayanan
- Periode (Bulan/Triwulan/Tahun)
- Usia
- Pendidikan
- Pekerjaan

**Table Columns:**
- Tanggal
- Nama
- Jenis Pelayanan
- Skor SKM
- Skor SPAK
- Actions

**Actions:**
- 👁️ View Detail
- 📥 Export Excel (All Data)
- 📥 Export Excel (Filtered)
- 📊 View Analytics

**Export Excel Format:**
```
Sheet 1: Identitas Responden
- No, Tanggal, Nama, Usia, Gender, Pendidikan, Pekerjaan, Jenis Pelayanan

Sheet 2: Jawaban SKM
- No, Nama, U1, U2, U3, U4, U5, U6, U7, U8, U9, Total, IKM

Sheet 3: Jawaban SPAK
- No, Nama, P1, P2, P3, P4, P5, P6, P7, P8, P9, P10, Total, IPAK

Sheet 4: Ringkasan
- Total Responden
- IKM Rata-rata
- IPAK Rata-rata
- Distribusi per Jenis Pelayanan
- Distribusi per Usia
- dll
```

---

### **Menu: Laporan SKM**

Path: `/suadmin/skm-report`

**Features:**
- 📊 IKM Score (Real-time)
- 📈 Chart per Unsur
- 📉 Trend Bulanan/Triwulan
- 🎯 Target vs Actual
- 📥 Export Report (Excel/PDF)

**Metrics:**
- IKM Bulan Ini
- IKM Triwulan Ini
- IKM Tahun Ini
- Comparison vs Previous Period

**Charts:**
1. Bar Chart: Nilai per Unsur (U1-U9)
2. Line Chart: Trend IKM (12 bulan)
3. Pie Chart: Distribusi Kategori (Sangat Baik, Baik, Kurang Baik, Tidak Baik)

---

### **Menu: Laporan SPAK**

Path: `/suadmin/spak-report`

**Features:**
- 📊 IPAK Score (Real-time)
- 📈 Chart per Aspek
- 📉 Trend Bulanan/Triwulan
- 🎯 Red Flag Indicators
- 📥 Export Report (Excel/PDF)

**Metrics:**
- IPAK Bulan Ini
- IPAK Triwulan Ini
- IPAK Tahun Ini
- Persentase "Tidak Pernah" (Goal: >90%)

**Charts:**
1. Bar Chart: Persentase per Aspek (P1-P10)
2. Line Chart: Trend IPAK (12 bulan)
3. Heatmap: Identifikasi area risiko

---

### **Menu: Publikasi Hasil Survei**

Path: `/suadmin/survey-publications`

**Resource:** `SurveyPublicationResource`

**CRUD Fields:**
- Judul Publikasi
- Cover Image (Upload)
- Tanggal Terbit (DatePicker)
- Tipe (SKM/SPAK/Combined)
- Periode (Monthly/Quarterly/Yearly)
- Bulan (Select 1-12)
- Triwulan (Select 1-4)
- Tahun (Number)
- Link Dokumen (URL) *atau*
- Upload PDF
- Deskripsi
- Status Publikasi (Published/Draft)

**List View:**
| Cover | Judul | Tipe | Periode | Status | Actions |
|---|---|---|---|---|---|
| [img] | Laporan SKM Januari 2025 | SKM | Bulanan | ✅ Published | Edit, View |
| [img] | Laporan SPAK Q1 2025 | SPAK | Triwulan | 📝 Draft | Edit, Publish |

**Public View:**
Path: `/supervision/survey-results` atau `/hasil-survei`

Display semua publikasi yang sudah published dengan:
- Cover image
- Judul
- Tanggal terbit
- [Download PDF] atau [Lihat Dokumen]

---

## 🚀 Implementation Steps

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Seed Default Questions
```bash
php artisan db:seed --class=SurveyQuestionSeeder
```

### Step 3: Create Filament Resources
```bash
php artisan make:filament-resource SurveyQuestion --panel=suadmin
php artisan make:filament-resource SurveyResponse --panel=suadmin
php artisan make:filament-resource SurveyPublication --panel=suadmin
```

### Step 4: Update Super Admin Panel
Register resources in `AdminPanelProvider.php`

### Step 5: Create Public Survey Form
- Multi-step Livewire component
- 3 pages with validation
- Auto-calculate scores
- Store responses

### Step 6: Test
1. Fill survey as public user
2. View responses in Super Admin
3. Check calculations
4. Generate reports
5. Publish results

---

## 📊 Analytics Dashboard (Future Enhancement)

**Potential Features:**
- Real-time response counter
- Geographic distribution (if IP geolocation added)
- Response rate by service type
- Peak response times
- Sentiment analysis (if open-ended questions added)
- Comparative analysis (year-over-year)
- Benchmark with other institutions

---

## 🔒 Privacy & Security

**Measures:**
- Anonymous responses (no login required)
- IP logging (for duplicate prevention)
- Data encryption
- GDPR compliance option
- Export controls (Super Admin only)

---

## 📱 Mobile Optimization

Survey form fully responsive:
- Touch-optimized radio buttons
- Swipe navigation (next/prev)
- Progress indicator
- Save & resume (optional)

---

## 🎯 Success Metrics

**KPIs to Track:**
- Response rate (goal: >100 responses/month)
- IKM score (goal: >88.31 "Sangat Baik")
- IPAK score (goal: >85 "Sangat Baik")
- Completion rate (goal: >80%)
- Average completion time (goal: <5 minutes)

---

## 📖 User Guide

### For Public Users:
1. Visit survey page
2. Fill identity form
3. Answer SKM questions (9)
4. Answer SPAK questions (10)
5. Submit
6. View thank you message

### For Super Admin:
1. Configure questions (if needed)
2. Monitor responses
3. Generate reports
4. Publish results
5. Export data for analysis

---

**Last Updated:** 2025-11-03
**Version:** 1.0.0
**Maintained By:** Development Team PTSP MTsN 2 Kota Malang
