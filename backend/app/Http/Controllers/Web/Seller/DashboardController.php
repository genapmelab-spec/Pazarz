<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $seller = $request->user()->seller;
        $store = $seller->store;

        if (!$seller->isVerified()) {
            return view('seller.pending-verification');
        }

        // Metrics
        $todayRevenue = SubOrder::where('store_id', $store->id)
            ->whereHas('order', fn($q) => $q->whereDate('created_at', today()))
            ->where('status', 'completed')
            ->sum('subtotal');

        $monthRevenue = SubOrder::where('store_id', $store->id)
            ->whereHas('order', fn($q) => $q->whereMonth('created_at', now()->month))
            ->where('status', 'completed')
            ->sum('subtotal');

        $newOrders = SubOrder::where('store_id', $store->id)
            ->where('status', 'pending')
            ->count();

        $lowStockProducts = Product::where('store_id', $store->id)
            ->whereHas('variants.inventory', fn($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold'))
            ->count();

        // Recent orders
        $recentOrders = SubOrder::with('order.user', 'items.variant.product')
            ->where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('seller.dashboard', compact(
            'store',
            'todayRevenue',
            'monthRevenue',
            'newOrders',
            'lowStockProducts',
            'recentOrders'
        ));
    }
}
