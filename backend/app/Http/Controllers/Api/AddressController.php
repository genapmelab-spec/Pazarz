<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create([
            'addressable_type' => User::class,
            'addressable_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'data' => $address,
        ], 201);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        if ($address->addressable_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Not your address.'],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->addressable_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Not your address.'],
            ], 403);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->addressable_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Not your address.'],
            ], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'data' => null,
        ]);
    }
}
