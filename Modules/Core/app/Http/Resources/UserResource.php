<?php

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->relationLoaded('users') ? $this->users->first() : null;

        return [
            'id'         => $this->id,
            'email'      => $this->email,
            'name'       => $profile ? $profile->full_name : 'System User',
            'role'       => $profile ? $profile->base_role : ($this->is_super_admin ? 'SuperAdmin' : 'Guest'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}