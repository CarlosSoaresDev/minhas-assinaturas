<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google's authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Falha na autenticação com Google.');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $email = $socialUser->getEmail();

        if (blank($email)) {
            return redirect()->route('login')->with('error', 'A conta Google não retornou e-mail válido.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário Google',
                'email' => $email,
                'password' => bcrypt(Str::random(24)),
                'lgpd_consent_at' => now(),
                'status' => 'active',
                'created_via_google' => true,
            ]);
        } elseif (is_null($user->lgpd_consent_at)) {
            $user->forceFill(['lgpd_consent_at' => now()])->save();
        }

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
