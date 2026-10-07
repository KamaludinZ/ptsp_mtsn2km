<?php

/**
 * Container helper, run by docker/entrypoint.sh.
 *
 *   php docker/db-check.php ready    exit 0 once the database accepts connections
 *   php docker/db-check.php empty    exit 0 when no user exists yet (first deploy)
 *
 * Uses the application's own database configuration, so DATABASE_URL and the
 * separate DB_HOST/DB_PORT/... variables both work.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$name = config('database.default');
$connection = config("database.connections.{$name}");

// Never hang on an unreachable host (wrong network/IP): libpq gives up after
// 5 s so the entrypoint can report the reason.
if (getenv('PGCONNECT_TIMEOUT') === false) {
    putenv('PGCONNECT_TIMEOUT=5');
}

if (($argv[1] ?? 'ready') === 'target') {
    // Where the app tries to connect, without the password (for the deploy log).
    echo sprintf('%s://%s@%s:%s/%s', $connection['driver'] ?? $name, $connection['username'] ?? '?', $connection['host'] ?? '?', $connection['port'] ?? '?', $connection['database'] ?? '?'), PHP_EOL;
    exit(0);
}

try {
    DB::connection()->getPdo();

    if (($argv[1] ?? 'ready') === 'empty') {
        exit(Schema::hasTable('users') && DB::table('users')->exists() ? 1 : 0);
    }

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(2);
}
