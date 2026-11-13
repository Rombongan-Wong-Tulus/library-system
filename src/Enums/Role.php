<?php
declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case PREMIUM = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MEMBER => 'Member',
            self::PREMIUM => 'Premium',
        };
    }
}
