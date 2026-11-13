<?php
declare(strict_types=1);

namespace App\Models;

class Member extends User
{
    public function getRole(): string { return 'member'; }
    public function getMaxLoans(): int { return 3; }
    public function getDiscount(): float { return 0.0; }
}
