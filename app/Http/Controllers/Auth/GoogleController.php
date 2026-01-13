<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // public function callback()
    // {
    //     $googleUser = Socialite::driver('google')->user();

    //     // Cek apakah email sudah ada
    //     $user = User::where('email', $googleUser->getEmail())->first();

    //     // Jika belum ada → buat user baru
    //     if (!$user) {
    //         $user = User::create([
    //             'name'       => $googleUser->getName(),
    //             'email'      => $googleUser->getEmail(),
    //             'password'   => null, // password null
    //             'google_id'  => $googleUser->getId(),
    //         ]);
    //     }

    //     // Login user
    //     Auth::login($user);

    //     // Redirect dengan pesan sukses
    //     return redirect()
    //         ->intended('user/dashboard');
    // }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name'       => $googleUser->getName(),
                'email'      => $googleUser->getEmail(),
                'password'   => null,
                'google_id'  => $googleUser->getId(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('user/dashboard');
    }
}
