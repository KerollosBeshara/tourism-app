<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyStatus extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'agency_statuses';

    protected $fillable = [
        'id',
        'name_translations',
        'color_code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
