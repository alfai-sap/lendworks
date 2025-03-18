<?php

namespace App\Http\Controllers\Auth;

// ...existing imports...
use App\Traits\LogsAdminActivity;

class AuthenticatedSessionController extends Controller
{
    use LogsAdminActivity;

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        if (auth()->user()->isAdmin()) {
            $this->logActivity(
                'Admin logged in',
                'info',
                'admin',
                ['ip' => $request->ip()]
            );
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request)
    {
        if (auth()->user()->isAdmin()) {
            $this->logActivity(
                'Admin logged out',
                'info',
                'admin',
                ['ip' => $request->ip()]
            );
        }

        // ...existing logout code...
    }
}
