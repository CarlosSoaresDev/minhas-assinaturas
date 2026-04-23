<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user || !\App\Services\PasswordSecurityService::checkPassword($request->password, $user->password)) {
                // Dispara o evento de falha manualmente para que os logs capturem
                event(new \Illuminate\Auth\Events\Failed('web', $user, $request->only('email', 'password')));

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => [trans('auth.failed')],
                ]);
            }

            return $user;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function () {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            }

            return view('pages::auth.login');
        });
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input(Fortify::username()));
            $ip = $request->ip();
            $throttleKey = Str::transliterate($email.'|'.$ip);

            return Limit::perMinutes(60, 5)->by($throttleKey)->response(function (Request $request, $limit) use ($throttleKey, $email, $ip) {
                event(new \Illuminate\Auth\Events\Lockout($request));

                $seconds = 0;
                
                // 1. Tenta via RateLimiter padrão
                $seconds = RateLimiter::availableIn($throttleKey);
                if ($seconds <= 0) $seconds = RateLimiter::availableIn('login:'.$throttleKey);

                // 2. Fallback Nuclear: Varredura física do cache (necessário em alguns ambientes Windows/Laravel 13)
                if ($seconds <= 0) {
                    $cachePath = storage_path('framework/cache/data');
                    if (is_dir($cachePath)) {
                        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath));
                        $now = time();
                        $latestTimer = 0;
                        foreach ($dir as $file) {
                            if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                                $content = @file_get_contents($file->getPathname());
                                if ($content && strlen($content) > 10) {
                                    $val = substr($content, 10);
                                    if (strpos($val, 'i:') === 0) {
                                        $num = (int)substr($val, 2, -1);
                                        // Procura por um timestamp futuro (timer)
                                        if ($num > $now && $num < $now + 7200) {
                                            if ($num > $latestTimer) $latestTimer = $num;
                                        }
                                    }
                                }
                            }
                        }
                        if ($latestTimer > 0) $seconds = $latestTimer - $now;
                    }
                }

                if ($seconds > 0) {
                    $minutes = ceil($seconds / 60);
                    $message = "Muitas tentativas de login. Sua conta está bloqueada por mais {$minutes} minuto(s).";
                } else {
                    $message = "Muitas tentativas de login. Tente novamente em alguns minutos.";
                }

                return back()->withErrors([
                    Fortify::username() => $message,
                ]);
            });
        });
    }
}
