<?php

declare(strict_types=1);

namespace Tests\Models;

use Tests\Database\Factories\FeatureFactory;

class Feature extends \Aercode\Subscriptions\Models\Feature
{
    protected static function newFactory(): FeatureFactory
    {
        return FeatureFactory::new();
    }
}
