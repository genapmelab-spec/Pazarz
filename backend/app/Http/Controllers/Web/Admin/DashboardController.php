<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Platform metrics
        $totalGMV = Order::where('status', 'completed')->sum('grand_total');
        $todayOrders = Order::whereDate('created_at', today())->count();
        $pendingSellers = Seller::where('verification_status', 'pending')->count();
        $totalUsers = User::count();
        $totalProducts = \App\Models\Product::count();
        $openDisputes = \App\Models\Dispute::where('status', 'open')->count();

        // Recent orders
        $recentOrders = Order::with(['user', 'subOrders.store'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalGMV',
            'todayOrders',
            'pendingSellers',
            'totalUsers',
            'totalProducts',
            'openDisputes',
            'recentOrders'
        ));
    }
}
