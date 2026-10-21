<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'username' => 'The username or password is incorrect.',
                ])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();

            return back()
                ->withErrors([
                    'username' => 'Your account is currently inactive.',
                ])
                ->onlyInput('username');
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
        
            case 'child_officer':
                return redirect()->intended(
                    route('child_officer.dashboard')
                );
        
            case 'field_officer':
<<<<<<< HEAD
                return redirect()->intended(
=======
                return redirect(
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
                    route('field_officer.dashboard')
                );
        
            case 'enumerator':
                return redirect()->intended(
                    route('enumerator.dashboard')
                );
        
            default:
                Auth::logout();
        
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'username' => 'Your account role is not configured.',
                    ]);
        }    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}