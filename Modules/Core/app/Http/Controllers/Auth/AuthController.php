<?php

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\Traits\ApiResponseTrait;
use Modules\Core\Http\Requests\Auth\LoginRequest;
use Modules\Core\Http\Requests\Auth\RegisterRequest;
use Modules\Core\Actions\Auth\LoginAction;
use Modules\Core\Actions\Auth\RegisterAgencyAction;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle Account & Agency Registration.
     */
   public function register(RegisterRequest $request, RegisterAgencyAction $action): JsonResponse
    {
        // The RegisterRequest handles validation automatically
        $account = $action->execute($request->validated());

        return $this->createdResponse($account, 'Agency and owner account registered successfully. Your agency is pending review.');
    }

    /**
     * Handle Login and Token Issue.
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $token = $action->execute(
            $request->email, 
            $request->password
        );

        return $this->successResponse([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 'Logged in successfully');
    }

    /**
     * Handle Logout (Revoke Token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->noContentResponse('Logged out successfully');
    }
}