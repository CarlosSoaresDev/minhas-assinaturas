<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Support\LivewireUploadUrlGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GenerateSignedUploadUrl::class, LivewireUploadUrlGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('pages', resource_path('views/pages'));
        Paginator::useBootstrapFive();
        $this->configureDefaults();

        // URL generation on shared hosting can be tricky when the app is served from a subdirectory.
        // Prefer the current request's basePath (if any) so route() doesn't "escape" to the domain root
        // even when APP_URL is misconfigured or config is cached without the path.
        $configuredAppUrl = $this->normalizePublicPath((string) config('app.url'));
        $forcedRootUrl = $configuredAppUrl;

        if (! app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                $request = request();
                $basePath = $this->normalizePublicPath(rtrim($request->getBasePath(), '/'));

                // Some shared hosts don't provide enough info for Symfony to infer basePath reliably.
                // Fallback to SCRIPT_NAME, which commonly includes the subdirectory + /index.php.
                if ($basePath === '') {
                    $scriptName = (string) ($request->server->get('SCRIPT_NAME') ?? '');
                    if ($scriptName !== '' && str_ends_with($scriptName, '/index.php')) {
                        $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
                        $basePath = $dir === '/' ? '' : $this->normalizePublicPath($dir);
                    }
                }

                if ($basePath !== '') {
                    $forcedRootUrl = $this->normalizePublicPath($request->getSchemeAndHttpHost().$basePath);
                }
            } catch (\Throwable) {
                // Ignore: request() can be unavailable in some early/edge bootstrap flows.
            }
        }

        if ($forcedRootUrl) {
            URL::forceRootUrl($forcedRootUrl);
            $scheme = parse_url($forcedRootUrl, PHP_URL_SCHEME) ?? 'https';
            URL::forceScheme($scheme);

            if ($scheme === 'https' && request()) {
                request()->server->set('HTTPS', 'on');
                request()->server->set('SERVER_PORT', 443);
                request()->headers->set('X-Forwarded-Proto', 'https');
            }

            if (request() && request()->is('*livewire*/upload-file') && ! request()->hasValidSignature()) {
                Log::error('Livewire upload signature mismatch', [
                    'method' => request()->method(),
                    'request_url' => request()->url(),
                    'forced_root' => $forcedRootUrl,
                    'full_url' => request()->fullUrl(),
                    'scheme' => request()->getScheme(),
                    'host' => request()->getHost(),
                    'http_host' => request()->getHttpHost(),
                    'scheme_and_host' => request()->getSchemeAndHttpHost(),
                    'base_url' => request()->getBaseUrl(),
                    'base_path' => request()->getBasePath(),
                    'app_url' => config('app.url'),
                    'asset_url' => config('app.asset_url'),
                    'origin' => request()->headers->get('Origin'),
                    'referer' => request()->headers->get('Referer'),
                    'x_forwarded_proto' => request()->headers->get('X-Forwarded-Proto'),
                    'x_forwarded_host' => request()->headers->get('X-Forwarded-Host'),
                    'https_server' => request()->server->get('HTTPS'),
                    'server_port' => request()->server->get('SERVER_PORT'),
                    'request_uri' => request()->server->get('REQUEST_URI'),
                    'script_name' => request()->server->get('SCRIPT_NAME'),
                ]);
            }

            $forcedPath = trim(parse_url($forcedRootUrl, PHP_URL_PATH) ?? '', '/');

            if ($forcedPath !== '') {
                $forcedOrigin = rtrim(str_replace('/'.$forcedPath, '', $forcedRootUrl), '/');

                URL::formatHostUsing(function (string $root, $route = null) use ($forcedOrigin): string {
                    $routeName = $route?->getName();

                    if (
                        $routeName
                        && (
                            str($routeName)->endsWith('livewire.update')
                        )
                    ) {
                        return $forcedOrigin;
                    }

                    return $root;
                });
            }
        }

        $assetUrl = env('ASSET_URL');
        if ($assetUrl) {
            config(['app.asset_url' => $assetUrl]);
        }

        User::observe(UserObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    private function normalizePublicPath(string $urlOrPath): string
    {
        return preg_replace('#/public/?$#i', '', rtrim($urlOrPath, '/')) ?: '';
    }
}
