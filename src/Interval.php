<?php

declare(strict_types=1);

namespace Aercode\Subscriptions;

enum Interval: string
{
    case YEAR = 'year';
    case MONTH = 'month';
    case DAY = 'day';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAY => __('day'),
            self::MONTH => __('month'),
            self::YEAR => __('year'),
        };
    }
}
