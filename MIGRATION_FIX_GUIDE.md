# Panduan Perbaikan Migrasi Database

## Masalah yang Ditemukan

Terdapat **36 record migrasi lama** di database yang file-nya sudah tidak ada atau telah direname. Hal ini menyebabkan konflik saat menjalankan migrasi.

### Detail Masalah:
- **Total migrasi di database**: 112
- **Total file migrasi**: 76
- **Migrasi orphan**: 36 (ada di database tapi file tidak ada)

## Solusi yang Telah Diterapkan

### 1. Perbaikan File Migrasi
Sudah ditambahkan:
- ✅ Method `down()` untuk rollback
- ✅ Conditional checks (`hasTable`/`hasColumn`) untuk mencegah error duplikasi
- ✅ Perbaikan pada migrasi media dan activity_log

### 2. Script Helper yang Tersedia

#### a. `check_migration_sync.php`
Memeriksa sinkronisasi antara database dan file migrasi.

```bash
php check_migration_sync.php
```

#### b. `fix_migrations_cleanup.php`
Membersihkan record migrasi yang file-nya sudah tidak ada (interactive).

```bash
php fix_migrations_cleanup.php
```

**PENTING**: Script ini akan meminta konfirmasi sebelum menghapus record.

#### c. `verify_migration_structure.php`
Memverifikasi struktur semua file migrasi.

```bash
php verify_migration_structure.php
```

## Langkah-Langkah Perbaikan

### Opsi 1: Cleanup Record Orphan (Recommended)

Jika Anda ingin membersihkan record migrasi yang tidak memiliki file:

```bash
# 1. Cek status saat ini
php check_migration_sync.php

# 2. Backup database terlebih dahulu
pg_dump -U postgres -d ptsp_db > backup_before_cleanup.sql

# 3. Jalankan cleanup (interactive)
php fix_migrations_cleanup.php

# 4. Verifikasi hasil
php artisan migrate:status

# 5. Jalankan migrasi yang belum dijalankan (jika ada)
php artisan migrate
```

### Opsi 2: Fresh Migration (Untuk Development)

⚠️ **HANYA untuk development! AKAN MENGHAPUS SEMUA DATA!**

```bash
# 1. Backup data penting terlebih dahulu
pg_dump -U postgres -d ptsp_db > backup_full.sql

# 2. Reset dan jalankan ulang semua migrasi
php artisan migrate:fresh --seed

# 3. Verifikasi
php artisan migrate:status
```

### Opsi 3: Manual Fix untuk Production

Untuk production, lebih aman melakukan manual:

```bash
# 1. Cek migrasi yang orphan
php check_migration_sync.php

# 2. Hapus record secara manual via psql
psql -U postgres -d ptsp_db

# Di psql:
DELETE FROM migrations WHERE migration IN (
    '2019_12_14_000001_create_personal_access_tokens_table',
    '2024_01_01_0000017_create_users_table',
    -- dst sesuai output check_migration_sync.php
);

# 3. Exit psql
\q

# 4. Verifikasi
php artisan migrate:status
```

## Pencegahan Error di Masa Depan

### 1. Selalu Gunakan Template Migrasi yang Benar

Untuk CREATE TABLE:
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

Untuk ADD COLUMN:
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

### 2. Periksa Sebelum Commit

```bash
# Verifikasi struktur migrasi
php verify_migration_structure.php

# Cek status migrasi
php artisan migrate:status

# Test migrasi di development
php artisan migrate --pretend
```

### 3. Jangan Rename/Delete File Migrasi

Jika sudah dijalankan di production:
- ❌ JANGAN rename file migrasi
- ❌ JANGAN hapus file migrasi
- ✅ Buat migrasi baru untuk perubahan
- ✅ Gunakan `down()` untuk rollback jika perlu

### 4. Best Practices

1. **Naming Convention**
   ```
   YYYY_MM_DD_HHMMSS_descriptive_name.php
   2025_12_10_120000_create_users_table.php
   2025_12_10_120100_add_email_to_users_table.php
   ```

2. **Testing**
   ```bash
   # Development
   php artisan migrate:fresh

   # Production (HATI-HATI!)
   php artisan migrate --pretend  # Cek dulu
   php artisan migrate            # Baru jalankan
   ```

3. **Version Control**
   - Commit file migrasi segera setelah dibuat
   - Jangan edit migrasi yang sudah di-commit dan di-deploy
   - Gunakan migrasi baru untuk perubahan

## Troubleshooting

### Error: "Duplicate table"
```bash
# Cek apakah tabel sudah ada
psql -U postgres -d ptsp_db -c "\dt table_name"

# Cek apakah migrasi sudah tercatat
php artisan migrate:status | grep table_name

# Jika tabel ada tapi migrasi belum tercatat:
php artisan migrate --force  # Akan skip karena ada hasTable check
```

### Error: "Column already exists"
```bash
# Cek kolom
psql -U postgres -d ptsp_db -c "\d+ table_name"

# Solusi: Update migrasi dengan hasColumn check (sudah ditambahkan)
```

### Migrasi Tidak Sinkron
```bash
# Gunakan script helper
php check_migration_sync.php
php fix_migrations_cleanup.php
```

## Kontak & Dokumentasi

- File helper: `check_migration_sync.php`, `fix_migrations_cleanup.php`, `verify_migration_structure.php`
- Laravel Docs: https://laravel.com/docs/migrations
- PostgreSQL Docs: https://www.postgresql.org/docs/

---

**Terakhir diupdate**: 2025-12-10
**Versi**: 1.0
