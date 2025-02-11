<?php

declare(strict_types=1);

namespace Aercode\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanProvider extends Model
{
    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.plans_providers');
    }

    protected $fillable = [
        'provider',
        'provider_product_id',
        'provider_price_id',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
