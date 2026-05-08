<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogLivewireUploadResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('*livewire*/upload-file')) {
            return $response;
        }

        $context = [
            'status' => $response->getStatusCode(),
            'method' => $request->method(),
            'url' => $request->url(),
            'full_url' => $request->fullUrl(),
            'has_valid_signature' => $request->hasValidSignature(),
            'content_type' => $request->headers->get('Content-Type'),
            'content_length' => $request->headers->get('Content-Length'),
            'origin' => $request->headers->get('Origin'),
            'referer' => $request->headers->get('Referer'),
            'request_uri' => $request->server->get('REQUEST_URI'),
            'script_name' => $request->server->get('SCRIPT_NAME'),
            'files_present' => $request->hasFile('files'),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
        ];

        if ($request->hasFile('files')) {
            $context['files'] = collect($request->file('files'))->map(fn ($file) => [
                'client_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
            ])->values()->all();
        }

        if ($response->getStatusCode() >= 400) {
            $context['response_body'] = mb_substr((string) $response->getContent(), 0, 2000);
            Log::error('Livewire upload response failed', $context);

            return $response;
        }

        Log::info('Livewire upload response completed', $context);

        return $response;
    }
}
