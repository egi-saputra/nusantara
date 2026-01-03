<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     $request->session()->regenerate();

    //     return redirect()->intended(RouteServiceProvider::HOME);
    // }

    public function store(LoginRequest $request)
{
    $remember = $request->filled('remember');
    $request->authenticate($remember);
    $request->session()->regenerate();

    $user = Auth::user();

    $redirectUrl = match($user->role) {
        'admin' => '/admin/dashboard',
        'proktor' => '/proktor/dashboard',
        'guru' => '/guru/dashboard',
        'siswa' => '/siswa/dashboard',
        default => '/siswa/dashboard',
    };

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl
        ]);
    }

    return redirect()->intended($redirectUrl);
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('login')); // ← paksa Inertia redirect full page
    }
}
