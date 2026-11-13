<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/** Dilempar saat aturan bisnis penyewaan dilanggar. */
class RentalException extends RuntimeException
{
}
