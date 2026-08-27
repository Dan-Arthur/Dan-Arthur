<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('GET')) {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            }
            return view('auth.login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Record login
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => true,
            ]);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::record('login', null, [], [], 'auth');

            // Guardians with portal access go to the parent portal
            if ($user->guardian && $user->guardian->portal_access) {
                return redirect()->route('portal.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        // Record failed login attempt
        if ($userRecord = \App\Models\User::where('email', $credentials['email'])->first()) {
            LoginHistory::create([
                'user_id' => $userRecord->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'failure_reason' => 'Invalid password',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
