<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            self::Pending->value => in_array($to, [self::Confirmed->value, self::Cancelled->value], true),
            self::Confirmed->value => in_array($to, [self::Cancelled->value, self::Completed->value], true),
            self::Cancelled->value, self::Completed->value => false,
            default => false,
        };
    }
}
