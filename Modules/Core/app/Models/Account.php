<?php

namespace Modules\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough; // 1. FIXED: Imported the through-relation class
use Modules\Core\Models\User;
// use Modules\Core\Models\Agency; // Uncomment and adjust if your Agency model sits in a different path/namespace

class Account extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Set to false because we are using ULIDs (strings), not auto-incrementing integers.
     * This prevents PHP from trying to cast the ID to an integer, which causes crashes.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     */
    protected $table = 'accounts';

    /**
     * Tell Laravel the name of the column used for the password.
     * Essential for Sanctum to validate the model.
     */
    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    /**
     * Return the password for the account.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'email',
        'password_hash',
        'is_super_admin',
        'last_login_at',
    ];

    /**
     * Attributes that should be hidden for serialization.
     * We hide 'users' to prevent infinite circular reference loops.
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'users', 
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    /**
     * Relationship: An Account can have many associated User workspace profiles.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'account_id', 'id');
    }

    /**
     * Relationship: Access the tenant Agency workspace through the User profile bridge.
     * Maps across the string columns on your users table.
     */
    public function agency(): HasOneThrough
    {
        // 2. FIXED: Implemented HasOneThrough with correct structural parameters
        return $this->hasOneThrough(
            Agency::class,       // Target: The model we want to fetch
            User::class,         // Through: The bridge model containing keys
            'account_id',        // Foreign key on intermediate table (users)
            'id',                // Foreign key on target table (agencies)
            'id',                // Local key on this table (accounts)
            'agency_id'          // Local key on intermediate table (users)
        );
    }
}