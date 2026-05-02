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
}