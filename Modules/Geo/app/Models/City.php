<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    // Critical for ULIDs
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'country_id', 'name_translations', 'slug', 'timezone', 'latitude', 'longitude', 'meta'
    ];

    protected $casts = [
        'name_translations' => 'array',
        'meta' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}