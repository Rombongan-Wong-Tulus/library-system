<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;

class Member extends User
{
    public const MAX_ACTIVE_LOANS = 3;
    public const DISCOUNT_RATE = 0.0;

    public function getRole(): Role { return Role::MEMBER; }
    public function getMaxLoans(): int { return self::MAX_ACTIVE_LOANS; }
    public function getDiscount(): float { return self::DISCOUNT_RATE; }
}
