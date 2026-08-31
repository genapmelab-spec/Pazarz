<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $seller = $user->seller;

        // No seller record = not a seller at all
        if (!$seller) {
            abort(403, 'You do not have a seller account.');
        }

        // Pending = waiting for admin approval
        if ($seller->isPending()) {
            return redirect()->route('seller.pending');
        }

        // Rejected = application was denied
        if ($seller->verification_status === 'rejected') {
            return redirect()->route('seller.rejected');
        }

        return $next($request);
    }
}
