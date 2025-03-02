<?php

declare(strict_types=1);

namespace Aercode\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int|string $id
 * @property int $used
 * @property Carbon|null $valid_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Feature $feature
 * @property-read Subscription $subscription
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage byFeatureSlug($featureSlug)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\SubscriptionUsage whereValidUntil($value)
 */
class SubscriptionUsage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'valid_until',
    ];

    protected $casts = [
        'used' => 'integer',
        'valid_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.subscription_usage');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.feature'), 'feature_id', 'id', 'feature');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.subscription'), 'subscription_id', 'id', 'subscription');
    }

    public function scopeByFeatureSlug(Builder $builder, string $featureSlug, int $plan_id): Builder
    {
        $model = config('laravel-subscriptions.models.feature', Feature::class);
        $feature = $model::where('slug', $featureSlug)->where('plan_id', $plan_id)->first();

        return $builder->where('feature_id', $feature ? $feature->getKey() : null);
    }

    public function expired(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
