<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google's authentication page.
     */
    public function redirectToGoogle()
    {
        abort_unless(config('services.google.enabled'), 404);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        abort_unless(config('services.google.enabled'), 404);

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

        try {
            $user = User::withTrashed()->where('email', $email)->first();

            if (! $user) {
                if (! config('app.public_registration', true)) {
                    return redirect()->route('login')->with('error', 'Novos registros estão temporariamente desativados.');
                }

                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário Google',
                    'email' => $email,
                    'password' => PasswordSecurityService::hashPassword(Str::random(24)),
                    'lgpd_consent_at' => now(),
                    'status' => 'active',
                    'created_via_google' => true,
                ]);
            } else {
                // Restaura conta soft-deletada se necessário
                if ($user->trashed()) {
                    $user->restore();
                    $user->forceFill(['status' => 'active'])->save();
                }

                if (is_null($user->lgpd_consent_at)) {
                    $user->forceFill(['lgpd_consent_at' => now()])->save();
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Erro ao processar sua conta Google. Tente novamente.');
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
