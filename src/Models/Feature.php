<?php

declare(strict_types=1);

namespace Aercode\Subscriptions\Models;

use Aercode\Subscriptions\Interval;
use Aercode\Subscriptions\Services\Period;
use Aercode\Subscriptions\Traits\BelongsToPlan;
use Aercode\Subscriptions\Traits\HasSlug;
use Aercode\Subscriptions\Traits\HasTranslations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read int|string $id
 * @property string $slug
 * @property array $title
 * @property array $description
 * @property string $value
 * @property int $resettable_period
 * @property Interval $resettable_interval
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Aercode\Subscriptions\Models\SubscriptionUsage[] $usage
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature byPlanId($planId)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereResettableInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereResettablePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Feature whereValue($value)
 */
class Feature extends Model implements Sortable
{
    use BelongsToPlan;
    use HasFactory;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    protected $casts = [
        'slug' => 'string',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => Interval::class,
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    public array $sortable = [
        'order_column_name' => 'sort_order',
    ];

    public function getTable(): string
    {
        return config('laravel-subscriptions.tables.features');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (Feature $feature): void {
            $feature->usage()->delete();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('plan_id', $this->plan_id));
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription_usage'));
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? Carbon::now());

        return $period->getEndDate();
    }
}
