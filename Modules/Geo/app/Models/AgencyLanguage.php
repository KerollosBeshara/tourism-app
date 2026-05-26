<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AgencyLanguage extends Pivot
{
    use HasUlids; 

    protected $table = 'agency_languages';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'agency_id',
        'language_id',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Agency::class, 'agency_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(\Modules\Geo\Models\Language::class, 'language_id');
    }
}