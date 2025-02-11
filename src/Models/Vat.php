<?php

declare(strict_types=1);

namespace Aercode\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class Vat extends Model
{
    protected $table = 'vat';

    protected $fillable = [
        'name',
        'rate',
    ];
}
