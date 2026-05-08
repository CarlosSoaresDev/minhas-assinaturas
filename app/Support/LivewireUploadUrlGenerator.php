<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class LivewireUploadUrlGenerator extends GenerateSignedUploadUrl
{
    public function forLocal()
    {
        $expires = now()->addMinutes(FileUploadConfiguration::maxUploadTime())->getTimestamp();
        $url = $this->uploadUrl().'?expires='.$expires;

        return $url.'&signature='.$this->signature($url);
    }

    private function uploadUrl(): string
    {
        return $this->origin().EndpointResolver::uploadPath();
    }

    private function origin(): string
    {
        if (! app()->runningInConsole() || app()->runningUnitTests()) {
            return Request::getSchemeAndHttpHost();
        }

        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
        $port = parse_url($appUrl, PHP_URL_PORT);

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    private function signature(string $url): string
    {
        $key = config('app.key');
        $key = is_array($key) ? $key[0] : $key;

        return hash_hmac('sha256', $url, $key);
    }
}
