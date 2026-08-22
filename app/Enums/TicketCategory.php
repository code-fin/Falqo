<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Development = 'development';
    case Design = 'design';
    case Marketing = 'marketing';
    case Support = 'support';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Development => 'code-2',
            self::Design => 'palette',
            self::Marketing => 'megaphone',
            self::Support => 'life-buoy',
        };
    }
}
