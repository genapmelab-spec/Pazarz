<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function edit(Request $request)
    {
        $store = $request->user()->seller->store;
        $store->load('address');

        return view('seller.settings.edit', compact('store'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', $request->user()->seller->store);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo_url' => 'nullable|string|max:500',
            'banner_url' => 'nullable|string|max:500',
        ]);

        $request->user()->seller->store->update($validated);

        return redirect()->route('seller.settings.edit')
            ->with('success', 'Store settings updated.');
    }
}
