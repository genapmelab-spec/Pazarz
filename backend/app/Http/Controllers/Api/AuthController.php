<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 'customer',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only('id', 'name', 'email', 'role_id'),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'avatar_url' => $user->avatar_url,
                ],
                'token' => $token,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id,
                    'avatar_url' => $user->avatar_url,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'data' => null,
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // In production, send email with reset link
        // For now, just return success
        return response()->json([
            'success' => true,
            'data' => ['message' => 'If the email exists, a reset link has been sent.'],
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'success' => true,
            'data' => ['message' => 'Password has been reset.'],
        ]);
    }

    /**
     * Apply to become a seller
     */
    public function becomeSeller(Request $request): JsonResponse
    {
        $user = $request->user();

        // Already a seller
        if ($user->seller) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'ALREADY_SELLER', 'message' => 'You already have a seller account.'],
            ], 422);
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
        ]);

        // Upgrade user role to seller
        $user->update(['role_id' => 'seller']);
        $user->syncRoles(['seller']);

        // Create seller record with pending status
        $seller = Seller::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'verification_status' => 'pending',
        ]);

        // Create store
        Store::create([
            'seller_id' => $seller->id,
            'name' => $validated['business_name'],
            'slug' => Str::slug($validated['business_name']) . '-' . Str::random(5),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Your seller application has been submitted. Please wait for admin approval.',
                'seller' => [
                    'id' => $seller->id,
                    'verification_status' => $seller->verification_status,
                ],
            ],
        ], 201);
    }

    /**
     * Get seller application status
     */
    public function sellerStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $seller = $user->seller;

        if (!$seller) {
            return response()->json([
                'success' => true,
                'data' => ['is_seller' => false, 'status' => null],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_seller' => true,
                'status' => $seller->verification_status,
                'business_name' => $seller->business_name,
                'verified_at' => $seller->verified_at,
            ],
        ]);
    }
}
