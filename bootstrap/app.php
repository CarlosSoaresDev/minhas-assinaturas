<?php

use App\Http\Middleware\CheckIfAdmin;
use App\Http\Middleware\LogLivewireUploadResponse;
use App\Http\Middleware\SanitizeInput;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (! function_exists('isLivewireDiagnosticRequest')) {
    function isLivewireDiagnosticRequest(Request $request): bool
    {
        return $request->is('*livewire*')
            || $request->hasHeader('X-Livewire')
            || str_contains((string) $request->headers->get('Referer'), '/subscriptions');
    }
}

if (! function_exists('livewireDiagnosticContext')) {
    function livewireDiagnosticContext(Request $request, ?Throwable $e = null): array
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->url(),
            'full_url' => $request->fullUrl(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'expects_json' => $request->expectsJson(),
            'ajax' => $request->ajax(),
            'has_valid_signature' => $request->hasValidSignature(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'http_host' => $request->getHttpHost(),
            'scheme_and_host' => $request->getSchemeAndHttpHost(),
            'base_url' => $request->getBaseUrl(),
            'base_path' => $request->getBasePath(),
            'app_url' => config('app.url'),
            'asset_url' => config('app.asset_url'),
            'session_driver' => config('session.driver'),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'upload_disk' => config('livewire.temporary_file_upload.disk'),
            'upload_directory' => config('livewire.temporary_file_upload.directory'),
            'content_type' => $request->headers->get('Content-Type'),
            'origin' => $request->headers->get('Origin'),
            'referer' => $request->headers->get('Referer'),
            'x_livewire' => $request->headers->get('X-Livewire'),
            'x_forwarded_proto' => $request->headers->get('X-Forwarded-Proto'),
            'x_forwarded_host' => $request->headers->get('X-Forwarded-Host'),
            'https_server' => $request->server->get('HTTPS'),
            'server_port' => $request->server->get('SERVER_PORT'),
            'request_uri' => $request->server->get('REQUEST_URI'),
            'script_name' => $request->server->get('SCRIPT_NAME'),
        ];

        if ($request->hasFile('files')) {
            $context['uploaded_files'] = collect($request->file('files'))->map(fn ($file) => [
                'client_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
            ])->values()->all();
        }

        if ($e) {
            $context['exception'] = [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return $context;
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SanitizeInput::class,
            LogLivewireUploadResponse::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'register',
            'login',
            'logout',
            'two-factor-login',
            'user/two-factor-authentication',
            'user/confirmed-password-status',
            'user/confirm-password',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'admin' => CheckIfAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            try {
                $request = request();
            } catch (Throwable) {
                return;
            }

            if (! $request instanceof Request || ! isLivewireDiagnosticRequest($request)) {
                return;
            }

            Log::error('Livewire request exception', livewireDiagnosticContext($request, $e));
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! isLivewireDiagnosticRequest($request) || ! config('app.debug')) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        });

        // Tratamento elegante para erros de Banco de Dados
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof QueryException || $e instanceof PDOException) {
                if (isLivewireDiagnosticRequest($request) && config('app.debug')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'exception' => $e::class,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], 500);
                }

                return back()->withErrors([
                    'email' => 'Estamos passando por uma instabilidade técnica no momento. Por favor, tente novamente em instantes.',
                ])->withInput();
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403);
        });
    })->create();
