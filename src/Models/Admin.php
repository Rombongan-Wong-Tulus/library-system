<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;

class Admin extends User
{
    public function getRole(): Role { return Role::ADMIN; }
    public function getMaxLoans(): int { return 0; }
    public function getDiscount(): float { return 0.0; }
}
