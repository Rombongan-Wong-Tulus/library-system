<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;

class PremiumMember extends Member
{
    public const MAX_ACTIVE_LOANS = 5;
    public const DISCOUNT_RATE = 0.20;

    public function getRole(): Role { return Role::PREMIUM; }
    public function getMaxLoans(): int { return self::MAX_ACTIVE_LOANS; }
    public function getDiscount(): float { return self::DISCOUNT_RATE; }
}
