<?php
declare(strict_types=1);

namespace App\Models;

class Admin extends User
{
    public function getRole(): string { return 'admin'; }
    public function getMaxLoans(): int { return 0; }
    public function getDiscount(): float { return 0.0; }
}
