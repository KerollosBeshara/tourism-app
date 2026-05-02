<?php

namespace Modules\Core\Actions\Auth;

use Modules\Core\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(string $email, string $password): string
    {
        $account = Account::where('email', $email)->first();

        if (!$account || !Hash::check($password, $account->password_hash)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Update login timestamp
        $account->update(['last_login_at' => now()]);

        // Generate the high-performance Sanctum token
        return $account->createToken('api_token')->plainTextToken;
    }
}