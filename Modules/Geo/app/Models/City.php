<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use SoftDeletes, HasUlids;

    // Critical for ULIDs
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'country_id', 'name_translations', 'slug', 'timezone', 'latitude', 'longitude', 'meta'
    ];

    protected $casts = [
        'name_translations' => 'array',
        'meta' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the country that owns the city.
     */
    public function country(): BelongsTo
    {
        // Adjust the namespace if your Country model lives elsewhere
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}