<?php

declare(strict_types=1);

use Aercode\Subscriptions\Interval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('vat', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 10, 2)->unique();
            $table->timestamps();
        });

        Schema::create(config('laravel-subscriptions.tables.plans'), function (Blueprint $table): void {
            $table->id();

            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price')->default('0.00');
            $table->foreignId('vat_id')->nullable()->constrained('vat');
            $table->string('currency', 3);
            $table->unsignedSmallInteger('trial_period')->default(0);
            $table->string('trial_interval')->default(Interval::DAY->value);
            $table->unsignedSmallInteger('invoice_period')->default(0);
            $table->string('invoice_interval')->default(Interval::MONTH->value);
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->default(Interval::DAY->value);
            $table->unsignedTinyInteger('prorate_day')->nullable();
            $table->unsignedTinyInteger('prorate_period')->nullable();
            $table->unsignedTinyInteger('prorate_extend_due')->nullable();
            $table->unsignedSmallInteger('active_subscribers_limit')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create(config('laravel-subscriptions.tables.plans_providers'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained(config('laravel-subscriptions.tables.plans'));
            $table->string('provider')->index();
            $table->string('provider_product_id')->unique();
            $table->string('provider_price_id')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists(config('laravel-subscriptions.tables.plans_providers'));
        Schema::dropIfExists(config('laravel-subscriptions.tables.plans'));
        Schema::dropIfExists('vat');
    }
};
