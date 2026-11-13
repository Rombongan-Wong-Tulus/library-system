<?php
declare(strict_types=1);

namespace App\Models;

class PremiumMember extends Member
{
    public function getRole(): string { return 'premium'; }
    public function getMaxLoans(): int { return 5; }
    public function getDiscount(): float { return 0.20; }
}
