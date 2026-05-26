<?php

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Modules\Core\Http\Requests\Auth\LoginRequest;
use Modules\Core\Http\Requests\Auth\RegisterRequest;
use Modules\Core\Actions\Auth\LoginAction;
use Modules\Core\Actions\Auth\RegisterAgencyAction;
use Modules\Core\Http\Resources\UserResource;
use Modules\Core\Models\Account;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle Account & Agency Registration.
     */
    public function register(RegisterRequest $request, RegisterAgencyAction $action): JsonResponse
    {
        $account = $action->execute($request->validated());

        return $this->createdResponse(
            new UserResource($account), 
            'Agency and owner account registered successfully. Your agency is pending review.'
        );
    }

    /**
     * Handle Login and Token Issue.
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->email, $request->password);

        // Load multi-tenant and localization relations instantly before output
        $this->hydrateUserSession($result->account);

        return $this->respondWithDomainData(
            $result->token, 
            $result->account, 
            'Logged in successfully'
        );
    }

    /**
     * Handle User Session Reload.
     */
    public function userLoad(Request $request): JsonResponse
    {
        $account = $request->user();

        if (!$account) {
            return $this->unauthorizedResponse('Unauthenticated or invalid session.');
        }

        // Use the exact same relationship hydration logic as login
        $this->hydrateUserSession($account);

        return $this->respondWithDomainData(
            null, 
            $account, 
            'Active user session loaded successfully'
        );
    }

    /**
     * Handle Logout (Revoke Token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
            return $this->noContentResponse('Logged out successfully');
        }

        return $this->noContentResponse('Session already cleared');
    }

    /**
     * Centrally manages eager loading for B2B SaaS tenancy context.
     * Prevents N+1 query bottlenecks across high-frequency auth operations.
     */
    private function hydrateUserSession(Account $account): void
    {
        $account->load([
            'users' => function($query) {
                $query->where('is_active', true)->limit(1);
            }, 
            'agency.languages'
        ]);
    }

    /**
     * Envelopes and splits the final response cleanly into flat domain fields.
     * Resolved resource mapping prevents Laravel from flattening peer root keys.
     */
    private function respondWithDomainData(?string $token, Account $account, string $message): JsonResponse
    {
        $agency = $account->agency;
        $payload = [];

        // Only append access configuration metadata if provided (e.g., inside Login)
        if ($token) {
            $payload['access_token'] = $token;
            $payload['token_type']   = 'Bearer';
        }

        // CRITICAL FIX: Use ->resolve() to convert the resource into its pure array presentation layer.
        // This stops Laravel from completely throwing away the peer 'agency' and 'languages' keys.
        $payload['user'] = (new UserResource($account))->resolve();

        // Domain 2: Flat Tenant Structural Context
        $payload['agency'] = $agency ? [
            'id'   => $agency->id,
            'name' => $agency->name,
        ] : null;

        // Domain 3: Standalone Localization Array for direct UI cache hydration
        $payload['languages'] = $agency && $agency->relationLoaded('languages') 
            ? $agency->languages->map(fn($lang) => [
                'id'         => $lang->id,
                'name'       => $lang->name,
                'code'       => $lang->code,
                'is_rtl'     => (bool) ($lang->is_rtl ?? false),

                'is_default' => (bool) ($lang->pivot?->is_default ?? false),
                'is_active'  => (bool) ($lang->pivot?->is_active ?? true),
            ])->values()->all()
            : [];

        return $this->successResponse($payload, $message);
    }
}