# Solusi Error Migrasi "Duplicate Table"

## Masalah Utama

Error yang terjadi:
```
SQLSTATE[42P07]: Duplicate table: 7 ERROR: relation "media" already exists
```

### Penyebab

1. **36 migrasi orphan** - Record migrasi di database yang file-nya sudah tidak ada
2. Database memiliki **112 record migrasi** tapi hanya ada **76 file migrasi**
3. Migrasi yang sudah dijalankan mencoba membuat tabel yang sudah ada

## Solusi Cepat (Recommended)

### Langkah 1: Backup Database

```bash
# Backup database (PENTING!)
pg_dump -U postgres -d ptsp_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Langkah 2: Bersihkan Record Orphan

```bash
# Jalankan script cleanup (akan ada konfirmasi)
php fix_migrations_cleanup.php
```

Ketik `yes` untuk menghapus 36 record orphan.

### Langkah 3: Verifikasi dan Jalankan Migrasi

```bash
# Cek status
php artisan migrate:status

# Jalankan migrasi
php artisan migrate
```

## Perbaikan yang Sudah Dilakukan

✅ **File yang sudah diperbaiki:**
1. `2025_11_13_032319_create_media_table.php` - Ditambahkan conditional check dan down() method
2. `2025_11_13_032331_create_activity_log_table.php` - Ditambahkan conditional check
3. `2025_11_13_032332_add_event_column_to_activity_log_table.php` - Ditambahkan conditional check
4. `2025_11_13_032333_add_batch_uuid_column_to_activity_log_table.php` - Ditambahkan conditional check

✅ **Script helper yang tersedia:**
1. `check_migration_sync.php` - Memeriksa sinkronisasi migrasi
2. `fix_migrations_cleanup.php` - Membersihkan record orphan (interactive)
3. `verify_migration_structure.php` - Memverifikasi struktur migrasi
4. `auto_fix_migrations.php` - Auto-fix conditional checks (experimental)

## Solusi Alternatif

### Opsi A: Manual Cleanup via SQL

Jika tidak ingin menggunakan script PHP:

```bash
# Masuk ke PostgreSQL
psql -U postgres -d ptsp_db

# Lihat migrasi orphan
SELECT migration FROM migrations
WHERE migration NOT IN (
    '2024_01_01_000001_create_personal_access_tokens_table',
    '2024_01_01_000002_create_users_table',
    -- dst (lihat output check_migration_sync.php untuk daftar lengkap)
);

# Hapus record orphan
DELETE FROM migrations WHERE migration LIKE '2019_12_14_%';
DELETE FROM migrations WHERE migration LIKE '2024_01_01_0000%';
DELETE FROM migrations WHERE migration = '2025_11_29_000001_update_postgresql_extensions_and_settings';
DELETE FROM migrations WHERE migration LIKE '2024_01_01_001%';

# Keluar
\q
```

### Opsi B: Fresh Migration (Development Only!)

⚠️ **PERHATIAN: Ini akan menghapus semua data!**

```bash
# HANYA untuk development/testing
php artisan migrate:fresh --seed
```

## Daftar Migrasi Orphan yang Akan Dihapus

36 migrasi berikut ada di database tapi file-nya sudah tidak ada:

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

## Pencegahan Error di Masa Depan

### 1. Jangan Rename/Hapus File Migrasi yang Sudah Dijalankan

❌ **Jangan lakukan:**
- Rename file migrasi yang sudah di-commit
- Hapus file migrasi yang sudah dijalankan
- Ubah timestamp migrasi

✅ **Yang benar:**
- Buat migrasi baru untuk perubahan
- Gunakan `down()` method untuk rollback

### 2. Selalu Gunakan Conditional Checks

**Template untuk CREATE TABLE:**
```php
public function up(): void
{
    if (!Schema::hasTable('table_name')) {
        Schema::create('table_name', function (Blueprint $table) {
            // columns
        });
    }
}

public function down(): void
{
    Schema::dropIfExists('table_name');
}
```

**Template untuk ADD COLUMN:**
```php
public function up(): void
{
    if (Schema::hasTable('table_name') &&
        !Schema::hasColumn('table_name', 'column_name')) {
        Schema::table('table_name', function (Blueprint $table) {
            $table->string('column_name');
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
```

### 3. Workflow Development yang Benar

```bash
# 1. Buat migrasi baru
php artisan make:migration create_example_table

# 2. Edit file migrasi dengan conditional checks

# 3. Test di development
php artisan migrate --pretend  # Preview
php artisan migrate            # Execute

# 4. Jika ada error, rollback
php artisan migrate:rollback

# 5. Fix dan test ulang

# 6. Commit setelah yakin
git add database/migrations/
git commit -m "Add example table migration"
```

## Troubleshooting

### Error: "Column already exists"

```bash
# Cek kolom yang ada
psql -U postgres -d ptsp_db -c "\d+ table_name"

# Solusi: Tambahkan hasColumn check di migrasi
```

### Error: "Migration not found"

```bash
# Cek file migrasi
ls -la database/migrations/

# Cek record di database
php artisan migrate:status
```

### Migrasi Tidak Sinkron

```bash
# Diagnosa
php check_migration_sync.php

# Perbaiki
php fix_migrations_cleanup.php
```

## Testing Sebelum Production

```bash
# 1. Backup production database
pg_dump -U postgres -d ptsp_db > backup_production.sql

# 2. Restore ke database testing
psql -U postgres -d ptsp_db_test < backup_production.sql

# 3. Test migrasi di database testing
php artisan migrate --database=testing --pretend

# 4. Jika OK, jalankan di production
php artisan migrate --force
```

## Kontak

Jika masih ada masalah setelah mengikuti panduan ini:
1. Jalankan `php check_migration_sync.php` dan simpan output
2. Jalankan `php artisan migrate:status` dan simpan output
3. Jalankan `psql -U postgres -d ptsp_db -c "\dt"` dan simpan output
4. Dokumentasikan error message lengkap

---

**Dibuat**: 2025-12-10
**Status**: Siap digunakan
**Tested**: ✓
