<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Akun pengguna:
 * - e-mail addresses are stored in lower case and unique regardless of
 *   case (Budi@x.id and budi@x.id are one account);
 * - last_login_at kept on the user (backfilled from the login log);
 * - user_type checked, and indexed with is_active for the user list.
 * Duplicates that differ only in case are reported, not merged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->index(['user_type', 'is_active']);
        });

        // Lower-case every address that has no case-only twin.
        DB::statement(<<<'SQL'
            update users u set email = lower(u.email)
            where u.email <> lower(u.email)
              and not exists (select 1 from users o where o.id <> u.id and lower(o.email) = lower(u.email))
        SQL);

        if (Schema::hasTable('login_attempts')) {
            DB::statement(<<<'SQL'
                update users u set last_login_at = l.last_at
                from (select lower(email) as email, max(attempted_at) as last_at from login_attempts where successful group by lower(email)) l
                where l.email = lower(u.email)
            SQL);
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $twins = DB::select('select lower(email) as email from users group by lower(email) having count(*) > 1');
        if (! $twins) {
            DB::statement('create unique index users_email_lower_unique on users (lower(email))');
        } else {
            logger()->warning('Akun dengan e-mail kembar (beda huruf besar/kecil) perlu digabung manual: ' . implode(', ', array_column($twins, 'email')));
        }

        DB::statement("alter table users add constraint users_user_type_check check (user_type in ('guru','pegawai','siswa','walimurid','alumni','instansi','umum')) not valid");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('drop index if exists users_email_lower_unique');
            DB::statement('alter table users drop constraint if exists users_user_type_check');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'is_active']);
            $table->dropColumn('last_login_at');
        });
    }
};
