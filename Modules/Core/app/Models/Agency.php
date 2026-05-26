<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agency extends Model
{
    // Disable auto-incrementing because we use ULIDs
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'contact_email',
        'agency_status_id',
        'country_id',
        'base_currency_id',
        'is_active',
    ];

    /**
     * An Agency has many Users (Staff, Drivers, etc.)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * An Agency belongs to an AgencyStatus
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AgencyStatus::class, 'agency_status_id', 'id');
    }

    public function languages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Geo\Models\Language::class, // Target model
            'agency_languages',                  // Table name
            'agency_id',                         // Foreign key on pivot referencing Agency
            'language_id'                        // Foreign key on pivot referencing Language
        )
        ->using(\Modules\Geo\Models\AgencyLanguage::class) // Bind the custom Pivot model
        ->withPivot('id', 'is_default', 'is_active')       // Make pivot properties queryable
        ->withTimestamps();
    }
}