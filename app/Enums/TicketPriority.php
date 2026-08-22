<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'zinc', self::Normal => 'blue', self::High => 'amber', self::Urgent => 'red'
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Urgent => 4, self::High => 3, self::Normal => 2, self::Low => 1
        };
    }
}
