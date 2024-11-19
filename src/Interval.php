<?php

declare(strict_types=1);

namespace Aercode\Subscriptions;

enum Interval: string
{
    case YEAR = 'year';

    case MONTH = 'month';

    case DAY = 'day';
}
