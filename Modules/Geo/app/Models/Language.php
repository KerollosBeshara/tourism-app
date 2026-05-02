<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'languages';

    protected $fillable = [
        'id',
        'code',
        'name',
        'native_name',
        'is_rtl',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
    ];

    /**
     * Scope to find language by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}