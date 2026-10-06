<?php

namespace App\Exceptions;

use RuntimeException;

/** Ganti akun sementara yang tidak diizinkan; pesannya untuk administrator dan ditampilkan apa adanya. */
class ImpersonationException extends RuntimeException
{
}
