<?php

declare(strict_types=1);

namespace Aercode\Subscriptions\Models;

use Aercode\Subscriptions\Traits\HasSlug;
use Aercode\Subscriptions\Traits\HasTranslations;
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
 * @property array $name
 * @property array $description
 * @property bool $is_active
 * @property float $price
 * @property float $signup_fee
 * @property string $currency
 * @property int $trial_period
 * @property string $trial_interval
 * @property int $invoice_period
 * @property string $invoice_interval
 * @property int $grace_period
 * @property string $grace_interval
 * @property int $prorate_day
 * @property int $prorate_period
 * @property int $prorate_extend_due
 * @property int $active_subscribers_limit
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Aercode\Subscriptions\Models\Feature[] $features
 * @property-read \Illuminate\Database\Eloquent\Collection|\Aercode\Subscriptions\Models\Subscription[] $subscriptions
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereActiveSubscribersLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereGraceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereGracePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereInvoiceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereInvoicePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereProrateDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereProrateExtendDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereProratePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereSignupFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereTrialInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereTrialPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aercode\Subscriptions\Models\Plan whereUpdatedAt($value)
 */
class Plan extends Model implements Sortable
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'price',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'float',
        'signup_fee' => 'float',
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
        return config('laravel-subscriptions.tables.plans');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function ($plan): void {
            $plan->features()->delete();
            $plan->subscriptions()->delete();
        });

        static::created(function ($plan): void {
            //create stripe product
            if (config('laravel-subscriptions.stripe_enabled') && config('cashier.key')) {
                $stripe = new \Stripe\StripeClient(
                    config('cashier.secret')
                );
                $stripeProduct = $stripe->products->create([
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'default_price_data' => [
                        'currency' => $plan->currency,
                        'unit_amount' => $plan->price * 100,
                        'recurring' => [
                            'interval' => $plan->invoice_interval,
                            'interval_count' => $plan->invoice_period,
                        ],
                    ],
                ]);

                $plan->providers()->create([
                    'provider' => 'stripe',
                    'provider_product_id' => $stripeProduct->id,
                    'provider_price_id' => $stripeProduct->default_price,
                ]);
            }
        });

        static::updated(function ($plan): void {
            if (config('laravel-subscriptions.stripe_enabled')) {
                $stripe = new \Stripe\StripeClient(
                    config('cashier.secret')
                );
                $provider = $plan->providers()->where('provider', 'stripe')->first();
                $stripeProduct = $stripe->products->retrieve($provider->provider_product_id);
                $stripePrice = $stripe->prices->retrieve($provider->provider_price_id);
                $stripe->products->update($stripeProduct->id, [
                    'name' => $plan->name,
                    'description' => $plan->description,
                ]);
                if ($plan->price != $stripePrice->unit_amount / 100 || $plan->currency != $stripePrice->currency || $plan->invoice_interval != $stripePrice->recurring->interval || $plan->invoice_period != $stripePrice->recurring->interval_count) {
                }

                $price = $stripe->prices->create([
                    'product' => $stripeProduct->id,
                    'currency' => $plan->currency,
                    'unit_amount' => $plan->price * 100,
                    'recurring' => [
                        'interval' => $plan->invoice_interval,
                        'interval_count' => $plan->invoice_period,
                    ],
                ]);
                $stripe->products->update($stripeProduct->id, [
                    'default_price' => $price->id,
                ]);
                $stripe->prices->update($stripePrice->id, [
                    'active' => false
                ]);
                $plan->providers()->where('provider', 'stripe')->update([
                    'provider_price_id' => $price->id,
                ]);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->allowDuplicateSlugs();
    }

    public function features(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.feature'));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription'));
    }

    public function isFree(): bool
    {
        return $this->price <= 0.00;
    }

    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    public function getFeatureBySlug(string $featureSlug): ?Feature
    {
        return $this->features()->where('slug', $featureSlug)->first();
    }

    public function activate(): self
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        return $this;
    }

    public function providers(): HasMany
    {
        return $this->hasMany(config('laravel-subscriptions.models.plan_provider'));
    }
}
