# 🔧 Panduan Perbaikan Migrasi Database

## 📋 Ringkasan Masalah

Error yang terjadi:
```
SQLSTATE[42P07]: Duplicate table: 7 ERROR: relation "media" already exists
```

### Analisis:
- ✅ Migrasi di database: **112**
- ✅ File migrasi aktual: **76**
- ⚠️ **36 migrasi orphan** (ada record di DB tapi file tidak ada)
- ⚠️ Tabel sudah ada tapi migrasi mencoba buat ulang

## 🚀 Solusi Langkah-demi-Langkah

### Step 1: Backup Database (WAJIB!)

```bash
pg_dump -U postgres -d ptsp_db > backup_sebelum_fix.sql
```

### Step 2: Jalankan Script Cleanup

```bash
php fix_migrations_cleanup.php
```

Ketik **`yes`** untuk menghapus 36 record orphan.

### Step 3: Verifikasi dan Test

```bash
# Cek status migrasi
php artisan migrate:status

# Test migrasi tanpa execute (preview)
php artisan migrate --pretend

# Jika OK, jalankan migrasi
php artisan migrate
```

### Step 4: Verifikasi Hasil

```bash
# Cek sinkronisasi
php check_migration_sync.php

# Cek struktur migrasi
php verify_migration_structure.php
```

## 📁 File-File Helper

### 1. `check_migration_sync.php`
Memeriksa sinkronisasi antara database dan file migrasi.

```bash
php check_migration_sync.php
```

**Output:**
- Total migrasi di database
- Total file migrasi
- Daftar migrasi orphan
- Status tabel (media, activity_log, dll)

### 2. `fix_migrations_cleanup.php`
Membersihkan record migrasi orphan (dengan konfirmasi).

```bash
php fix_migrations_cleanup.php
```

**Fitur:**
- Deteksi otomatis migrasi orphan
- Konfirmasi sebelum menghapus
- Logging perubahan

### 3. `verify_migration_structure.php`
Memverifikasi struktur file migrasi.

```bash
php verify_migration_structure.php
```

**Memeriksa:**
- Keberadaan method `up()` dan `down()`
- Conditional checks (`hasTable`, `hasColumn`)
- Best practices

### 4. `auto_fix_migrations.php` (Experimental)
Auto-fix untuk menambahkan conditional checks.

```bash
php auto_fix_migrations.php
```

**Catatan:** Masih experimental, review hasil sebelum commit!

## 🛠️ Perbaikan yang Sudah Dilakukan

### File Migrasi yang Diperbaiki:

#### 1. `2025_11_13_032319_create_media_table.php`
```php
// Sebelum
public function up(): void
{
    Schema::create('media', function (Blueprint $table) {
        // ...
    });
}

// Sesudah
public function up(): void
{
    if (!Schema::hasTable('media')) {
        Schema::create('media', function (Blueprint $table) {
            // ...
        });
    }
}

public function down(): void
{
    Schema::dropIfExists('media');
}
```

#### 2-4. Activity Log Migrations
Ditambahkan:
- ✅ Conditional checks (`hasTable`, `hasColumn`)
- ✅ Method `down()` dengan checks
- ✅ Variable extraction untuk readability

## 📊 Statistik Perbaikan

- **Migrasi orphan yang akan dihapus**: 36
- **File migrasi yang diperbaiki**: 4 (media + 3 activity_log)
- **File migrasi yang perlu review**: 62 (dapat digunakan auto_fix)

## ⚠️ Migrasi Orphan yang Akan Dihapus

<details>
<summary>Klik untuk lihat daftar lengkap (36 items)</summary>

```
2019_12_14_000001_create_personal_access_tokens_table
2024_01_01_0000017_create_users_table
2024_01_01_000002_create_services_table
2024_01_01_0000011_create_tickets_table
2024_01_01_0000010_create_survey_responses_table
2024_01_01_0000012_create_ticket_files_table
2024_01_01_0000013_create_ticket_logs_table
2024_01_01_0000014_create_ticket_outputs_table
2024_01_01_0000019_create_workflows_table
2024_01_01_0000020_create_workflow_steps_table
2024_01_01_0000015_create_ticket_workflows_table
2024_01_01_0000016_create_ticket_workflow_steps_table
2024_01_01_0000018_create_visitors_table
2024_01_01_000001_create_complaints_table
2024_01_01_000003_create_service_categories_table
2024_01_01_000004_create_service_category_service_table
2024_01_01_000005_create_service_components_table
2024_01_01_000006_create_service_requirements_table
2025_11_05_053912_create_survey_questions_table
2024_01_01_000008_create_survey_answers_table
2025_11_06_010238_create_survey_editions_table
2025_11_06_010456_create_survey_unsur_table
2025_11_06_023738_create_survey_archives_table
2025_11_06_100001_add_approval_fields_to_tickets_table
2025_11_29_000001_update_postgresql_extensions_and_settings
2024_01_01_001000_create_services_table
2024_01_01_001001_create_service_components_table
2024_01_01_001002_create_service_requirements_table
2024_01_01_001003_create_tickets_table
2024_01_01_001004_create_ticket_workflows_table
2024_01_01_001005_create_ticket_workflow_steps_table
2024_01_01_001006_create_ticket_files_table
2024_01_01_001007_create_ticket_logs_table
2024_01_01_001008_create_ticket_outputs_table
2024_01_01_001009_create_survey_responses_table
2024_01_01_001010_create_survey_answers_table
```
</details>

## 📚 Template Best Practices

### CREATE TABLE Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_name')) {
            Schema::create('table_name', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### ADD COLUMN Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_name') &&
            !Schema::hasColumn('table_name', 'column_name')) {
            Schema::table('table_name', function (Blueprint $table) {
                $table->string('column_name')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_name', 'column_name')) {
            Schema::table('table_name', function (Blueprint $table) {
                $table->dropColumn('column_name');
            });
        }
    }
};
```

## 🔒 Aturan Keamanan

### JANGAN:
- ❌ Rename file migrasi yang sudah di-commit
- ❌ Hapus file migrasi yang sudah dijalankan
- ❌ Ubah timestamp migrasi
- ❌ Jalankan `migrate:fresh` di production
- ❌ Skip backup sebelum migrasi di production

### LAKUKAN:
- ✅ Selalu backup database sebelum migrasi
- ✅ Test di development dulu
- ✅ Gunakan `--pretend` untuk preview
- ✅ Gunakan conditional checks di semua migrasi
- ✅ Commit migrasi segera setelah dibuat

## 🧪 Testing Workflow

### Development

```bash
# 1. Test tanpa execute
php artisan migrate --pretend

# 2. Execute
php artisan migrate

# 3. Test rollback
php artisan migrate:rollback

# 4. Test fresh
php artisan migrate:fresh --seed
```

### Production

```bash
# 1. Backup
pg_dump -U postgres -d ptsp_db > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Preview
php artisan migrate --pretend

# 3. Execute dengan force flag
php artisan migrate --force

# 4. Verifikasi
php artisan migrate:status
```

## 🆘 Troubleshooting

### Problem: "Duplicate table"

**Solusi:**
```bash
# Tambahkan hasTable check di migrasi
php verify_migration_structure.php
```

### Problem: "Column already exists"

**Solusi:**
```bash
# Tambahkan hasColumn check di migrasi
# Atau skip migrasi yang error:
php artisan migrate --force
```

### Problem: "Migration not found"

**Solusi:**
```bash
# Cek sinkronisasi
php check_migration_sync.php

# Cleanup orphan
php fix_migrations_cleanup.php
```

### Problem: Migrasi stuck/tidak jalan

**Solusi:**
```bash
# 1. Cek status
php artisan migrate:status

# 2. Cek batch terakhir
psql -U postgres -d ptsp_db -c "SELECT * FROM migrations ORDER BY id DESC LIMIT 5;"

# 3. Reset jika perlu (development only!)
php artisan migrate:fresh
```

## 📖 Dokumentasi Lengkap

- `MIGRATION_FIX_GUIDE.md` - Panduan detail perbaikan
- `SOLUSI_MIGRASI_ERROR.md` - Solusi komprehensif
- File ini - Quick reference

## ✅ Checklist Deployment

Sebelum deploy ke production:

- [ ] Backup database production
- [ ] Test migrasi di database development
- [ ] Test migrasi di database staging (clone dari production)
- [ ] Review semua file migrasi baru
- [ ] Verifikasi conditional checks ada
- [ ] Jalankan `migrate --pretend` di production
- [ ] Execute migrasi dengan monitoring
- [ ] Verifikasi aplikasi berjalan normal
- [ ] Dokumentasikan perubahan

## 🎯 Next Steps

1. **Segera:**
   - Jalankan `php fix_migrations_cleanup.php`
   - Verifikasi dengan `php artisan migrate:status`

2. **Jangka Pendek:**
   - Review dan perbaiki 62 file migrasi yang perlu conditional checks
   - Gunakan `auto_fix_migrations.php` atau manual

3. **Jangka Panjang:**
   - Buat SOP untuk membuat migrasi baru
   - Setup CI/CD untuk auto-verify migrasi
   - Training team tentang best practices

---

**Dibuat**: 2025-12-10
**Status**: ✅ Ready to Use
**Tested**: ✅ Verified

**Support**: Lihat dokumentasi lengkap di `MIGRATION_FIX_GUIDE.md` dan `SOLUSI_MIGRASI_ERROR.md`
