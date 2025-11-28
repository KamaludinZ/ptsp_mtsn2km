# Roles & Permissions - PTSP MTsN 2 Kota Malang

## Daftar Roles

Sistem PTSP MTsN 2 Kota Malang menggunakan 6 roles utama:

### 1. Admin
**Role Name**: `admin`

**Fungsi**:
- Full access ke seluruh sistem
- Manajemen users dan roles
- Konfigurasi sistem
- Manajemen layanan (services)
- Monitoring seluruh aktivitas

**Access Level**: Full Access

**Dapat diakses oleh**: Administrator Sistem

---

### 2. Kepala Sekolah
**Role Name**: `kepala_sekolah`

**Fungsi**:
- Monitoring seluruh pelayanan
- Approval untuk layanan tertentu yang memerlukan persetujuan kepala sekolah
- Melihat laporan dan statistik
- Melihat aktivitas layanan

**Access Level**: Read & Approve

**Dapat diakses oleh**: Kepala Sekolah MTsN 2 Kota Malang

---

### 3. Kepala TU
**Role Name**: `kepala_tu`

**Fungsi**:
- Kontrol dan monitoring seluruh pelayanan
- Koordinasi petugas TU dan petugas loket
- Melihat dan mengelola tiket layanan
- Approval untuk layanan administratif
- Monitoring kinerja petugas
- Melihat laporan pelayanan

**Access Level**: Read, Write, & Approve (khusus administrasi)

**Dapat diakses oleh**: Kepala Tata Usaha MTsN 2 Kota Malang

---

### 4. Petugas TU
**Role Name**: `petugas_tu`

**Fungsi**:
- Memproses tiket layanan
- Update status tiket
- Upload dokumen output layanan
- Verifikasi dokumen pemohon
- Komunikasi dengan pemohon

**Access Level**: Read & Write (operasional)

**Dapat diakses oleh**: Petugas Tata Usaha

---

### 5. Petugas Loket
**Role Name**: `petugas_loket`

**Fungsi**:
- Penerimaan tiket layanan (walk-in)
- Input data pemohon
- Verifikasi dokumen awal
- Update status tiket
- Notifikasi ke pemohon untuk pengambilan dokumen

**Access Level**: Read & Write (front office)

**Dapat diakses oleh**: Petugas Loket/Front Office

---

### 6. Pemohon
**Role Name**: `pemohon`

**Fungsi**:
- Mengajukan permohonan layanan
- Tracking status permohonan
- Upload dokumen persyaratan
- Melihat history permohonan
- Mengisi survey kepuasan

**Access Level**: Limited (hanya data sendiri)

**Dapat diakses oleh**: Seluruh pemohon layanan (guru, pegawai, siswa, wali murid, alumni, instansi, umum)

---

## Struktur Hierarki

```
Admin (Full Access)
  |
  ├─── Kepala Sekolah (Monitoring & Approval)
  |
  └─── Kepala TU (Kontrol & Monitoring Pelayanan)
         |
         ├─── Petugas TU (Operasional Backend)
         |
         └─── Petugas Loket (Operasional Front Office)

Pemohon (External/Limited Access)
```

---

## Akses Panel Admin Filament

**URL**: `/admin`

**Roles yang dapat mengakses**:
- ✅ `admin`
- ✅ `kepala_sekolah`
- ✅ `kepala_tu`
- ✅ `petugas_tu`
- ✅ `petugas_loket`
- ❌ `pemohon` (tidak dapat akses panel admin)

---

## User Methods Helper

Di model `User.php` tersedia helper methods untuk check role:

```php
// Check if user is admin
$user->isAdmin() // return boolean

// Check if user is kepala sekolah
$user->isKepalaSekolah() // return boolean

// Check if user is kepala TU
$user->isKepalaTU() // return boolean

// Check if user is petugas (TU atau Loket)
$user->isPetugas() // return boolean

// Check specific role
$user->hasRole('admin') // return boolean
$user->hasRole(['kepala_sekolah', 'kepala_tu']) // return boolean
```

---

## Rekomendasi Implementasi Permissions

### Service Management
- **Create/Edit Services**: `admin`, `kepala_tu`
- **View Services**: Semua roles
- **Delete Services**: `admin` only

### Ticket Management
- **Create Tickets**: `pemohon`, `petugas_loket`
- **Edit Tickets**: `admin`, `kepala_tu`, `petugas_tu`, `petugas_loket`
- **View All Tickets**: `admin`, `kepala_sekolah`, `kepala_tu`
- **View Own Tickets**: `petugas_tu`, `petugas_loket`, `pemohon`
- **Delete Tickets**: `admin` only

### User Management
- **Create/Edit/Delete Users**: `admin`
- **View Users**: `admin`, `kepala_sekolah`, `kepala_tu`

### Approval Process
- **Approve Layanan Umum**: `kepala_tu`
- **Approve Layanan Khusus**: `kepala_sekolah`
- **Approve Layanan Keuangan**: `admin`, `kepala_sekolah`

---

## Setup Roles

Untuk membuat/update roles, jalankan:

```bash
php artisan db:seed --class=RolesAndAdminSeeder
```

Seeder ini akan:
1. Menghapus role lama (`super_admin`, `bendahara`) jika ada
2. Membuat 6 roles baru
3. Membuat user admin default

---

## Default Admin Credentials

```
Email: admin@mtsn2kotamalang.sch.id
Password: password
Role: admin
```

⚠️ **PENTING**: Ganti password default ini setelah login pertama kali!

---

## Migration & Model Setup

### Model yang Menggunakan Roles:
- `User` model (HasRoles trait dari Spatie Permission)

### Database Tables:
- `roles` - Daftar roles
- `model_has_roles` - Pivot table user-role relationship
- `role_has_permissions` - Pivot table role-permission relationship
- `permissions` - Daftar permissions (optional, bisa dikembangkan)

---

## Best Practices

1. **Jangan Hard-code Role Names**
   ```php
   // Bad
   if ($user->hasRole('admin')) { }

   // Good
   if ($user->isAdmin()) { }
   ```

2. **Gunakan Policies untuk Authorization**
   ```php
   // TicketPolicy.php
   public function update(User $user, Ticket $ticket)
   {
       return $user->isAdmin()
           || $user->isKepalaTU()
           || $user->isPetugas();
   }
   ```

3. **Cache Role Checks**
   Spatie Permission sudah menghandle caching secara otomatis

4. **Clear Cache Setelah Update Roles**
   ```bash
   php artisan permission:cache-reset
   ```

---

Dibuat pada: 13 November 2025
Last Update: 13 November 2025
