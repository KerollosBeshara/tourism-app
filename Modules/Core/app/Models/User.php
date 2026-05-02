<?php

namespace Modules\Core\Models;

use Modules\Core\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'account_id',
        'agency_id',
        'full_name',
        'base_role', // owner, driver, guide, accountant, etc.
        'phone',
    ];

    /**
     * Link back to the Security Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Link to the Agency (Tenant)
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}