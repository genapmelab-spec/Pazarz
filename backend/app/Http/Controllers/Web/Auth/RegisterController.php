<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showSellerRegistration()
    {
        return view('auth.register-seller');
    }

    public function registerSeller(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 'seller',
        ]);

        $seller = Seller::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'verification_status' => 'pending',
        ]);

        // Create store with default slug
        Store::create([
            'seller_id' => $seller->id,
            'name' => $validated['business_name'],
            'slug' => Str::slug($validated['business_name']) . '-' . Str::random(5),
        ]);

        Auth()->login($user);

        return redirect()->route('seller.dashboard')
            ->with('success', 'Your seller account has been created. Please wait for verification.');
    }
}
