<?php

return [
    /*
     * Ganti akun sementara: a session ends by itself after this many minutes
     * (back to the administrator's account), so a forgotten one does not stay open.
     */
    'max_minutes' => (int) env('IMPERSONATION_MAX_MINUTES', 120),
];
