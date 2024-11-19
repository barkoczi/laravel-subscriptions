<?php

declare(strict_types=1);

namespace Tests\Models;

use Tests\Database\Factories\PlanFactory;

class Plan extends \Aercode\Subscriptions\Models\Plan
{
    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }
}
