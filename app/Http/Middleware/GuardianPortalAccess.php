<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GuardianPortalAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $guardian = $user->guardian;

        if (!$guardian || !$guardian->portal_access) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Portal access is not enabled for this account. Please contact the school.']);
        }

        return $next($request);
    }
}
