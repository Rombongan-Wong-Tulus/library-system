<?php
declare(strict_types=1);

namespace App\Factories;

use App\Models\Admin;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Models\User;
use InvalidArgumentException;

final class UserFactory
{
    /** @param array<string,mixed> $d */
    public static function fromArray(array $d): User
    {
        $id = $d['id'];
        $name = $d['name'];
        $email = $d['email'];
        $hash = $d['passwordHash'];

        return match ($d['role']) {
            'member' => new Member($id, $name, $email, $hash),
            'premium' => new PremiumMember($id, $name, $email, $hash),
            'admin' => new Admin($id, $name, $email, $hash),
            default => throw new InvalidArgumentException('Role tidak dikenal: ' . $d['role']),
        };
    }
}
