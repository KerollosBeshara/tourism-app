<?php
namespace Modules\Core\Http\Middleware;

use Closure;
use Laravel\Sanctum\PersonalAccessToken;

class ManualAuth
{
public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) return response()->json(['message' => 'Unauthenticated.'], 401);

        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        // --- MANUAL EXPIRATION CHECK ---
        $expiration = config('sanctum.expiration');

        if ($expiration && $accessToken->created_at->addMinutes($expiration)->isPast()) {
            return response()->json(['message' => 'Token expired.'], 401);
        }
        // -------------------------------

        // Set the user for the rest of the application
        $request->setUserResolver(fn () => $accessToken->tokenable);

        return $next($request);
    }
}