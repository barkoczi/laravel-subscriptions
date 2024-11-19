<?php

declare(strict_types=1);

use Aercode\Subscriptions\Models\Feature;
use Aercode\Subscriptions\Models\Plan;
use Aercode\Subscriptions\Models\PlanProvider;
use Aercode\Subscriptions\Models\Subscription;
use Aercode\Subscriptions\Models\SubscriptionUsage;

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Tables
    |--------------------------------------------------------------------------
    |
    |
    */

    'tables' => [
        'plans' => 'plans',
        'plans_providers' => 'plans_providers',
        'features' => 'features',
        'subscriptions' => 'subscriptions',
        'subscription_usage' => 'subscription_usage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Models
    |--------------------------------------------------------------------------
    |
    | Models used to manage subscriptions. You can replace to use your own models,
    | but make sure that you have the same functionalities or that your models
    | extend from each model that you are going to replace.
    |
    */

    'models' => [
        'plan' => Plan::class,
        'plan_provider' => PlanProvider::class,
        'feature' => Feature::class,
        'subscription' => Subscription::class,
        'subscription_usage' => SubscriptionUsage::class,
    ],

    'stripe_enabled' => env('SUBSCRIPTION_STRIPE_ENABLED', false),

];
