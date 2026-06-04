<?php

namespace App\Enums;

enum RoleApplicationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * @return array<int, array{id: string, value: string}>
     */
    public static function options(): array
    {
        return [
            ['id' => self::PENDING->value, 'value' => __('Pending')],
            ['id' => self::APPROVED->value, 'value' => __('Approved')],
            ['id' => self::REJECTED->value, 'value' => __('Rejected')],
        ];
    }
}
