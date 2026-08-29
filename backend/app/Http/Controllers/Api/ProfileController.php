<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'avatar_url' => 'sometimes|nullable|string|max:500',
            'current_password' => 'required_with:password',
            'password' => ['sometimes', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (isset($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'error' => ['code' => 'INVALID_PASSWORD', 'message' => 'Current password is incorrect.'],
                ], 422);
            }
            $user->update(['password' => Hash::make($validated['password'])]);
            unset($validated['password'], $validated['current_password']);
        }

        $user->update(collect($validated)->except('current_password')->toArray());

        return response()->json([
            'success' => true,
            'data' => $user->only('id', 'name', 'email', 'phone', 'avatar_url'),
        ]);
    }
}
