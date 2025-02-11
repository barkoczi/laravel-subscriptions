<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Aercode\Subscriptions\Interval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => 'Pro',
            'description' => 'Pro plan',
            'price' => 9.99,
            'signup_fee' => 1.99,
            'invoice_period' => 1,
            'invoice_interval' => Interval::MONTH->value,
            'trial_period' => 15,
            'trial_interval' => Interval::DAY->value,
            'sort_order' => 1,
            'currency' => 'USD',
        ];
    }
}
