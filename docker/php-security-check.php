<?php

/*
 * Refuses a PHP build without the 21 October 2026 security fixes
 * (PHP2Shell: CVE-2026-109618 and CVE-2026-109619, pre-auth RCE).
 * Run at image build time and on every container start.
 */

// First patched release of each supported branch.
$patched = [
    '8.2' => '8.2.35',
    '8.3' => '8.3.36',
    '8.4' => '8.4.27',
    '8.5' => '8.5.12',
];

$branch = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
$minimum = $patched[$branch] ?? null;

if ($minimum === null && version_compare($branch, '8.5', '>')) {
    // Newer branches were released after the fix.
    echo 'PHP '.PHP_VERSION." OK.\n";
    exit(0);
}

if ($minimum === null || version_compare(PHP_VERSION, $minimum, '<')) {
    fwrite(STDERR, 'PHP '.PHP_VERSION.' rentan PHP2Shell (CVE-2026-109618/109619). Butuh '
        .($minimum ?? 'PHP 8.2.35 atau lebih baru')." - bangun ulang image tanpa cache.\n");
    exit(1);
}

if (filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR, "session.upload_progress.enabled harus Off (lapisan pengaman tambahan PHP2Shell).\n");
    exit(1);
}

echo 'PHP '.PHP_VERSION." sudah memuat tambalan PHP2Shell.\n";
