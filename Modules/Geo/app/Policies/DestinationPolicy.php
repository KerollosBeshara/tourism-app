<?php

namespace Modules\Geo\Policies;

use App\Models\User;
use Modules\Geo\Models\Destination;

class DestinationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Adjust permission string names to match your spatie/laravel-permission setups if used
        return $user->tokenCan('destinations:view') || $user->hasPermissionTo('view-destinations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Destination $destination): bool
    {
        return $user->tokenCan('destinations:view') || $user->hasPermissionTo('view-destinations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tokenCan('destinations:create') || $user->hasPermissionTo('create-destinations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Destination $destination): bool
    {
        return $user->tokenCan('destinations:update') || $user->hasPermissionTo('update-destinations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Destination $destination): bool
    {
        return $user->tokenCan('destinations:delete') || $user->hasPermissionTo('delete-destinations');
    }
}