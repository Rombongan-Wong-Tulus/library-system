<?php
declare(strict_types=1);

namespace App\Factories;

use App\Enums\Role;
use App\Models\Admin;
use App\Models\Member;
use App\Models\PremiumMember;
use App\Models\User;
use InvalidArgumentException;
use ValueError;

final class UserFactory
{
    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): User
    {
        $id = (string) $d['id'];
        $name = (string) $d['name'];
        $email = (string) $d['email'];
        $hash = (string) $d['passwordHash'];

        try {
            $role = $d['role'] instanceof Role ? $d['role'] : Role::from((string) $d['role']);
        } catch (ValueError) {
            throw new InvalidArgumentException('Role tidak dikenal: ' . ($d['role'] ?? ''));
        }

        $user = match ($role) {
            Role::MEMBER => new Member($id, $name, $email, $hash),
            Role::PREMIUM => new PremiumMember($id, $name, $email, $hash),
            Role::ADMIN => new Admin($id, $name, $email, $hash),
        };

        $user->initTimestamps($d['createdAt'] ?? null, $d['updatedAt'] ?? null);

        return $user;
    }
}
