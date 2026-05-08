<?php

namespace App\Support;

use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class LivewireUploadUrlGenerator extends GenerateSignedUploadUrl
{
    public function forLocal()
    {
        $expires = now()->addMinutes(FileUploadConfiguration::maxUploadTime())->getTimestamp();
        $publicUrl = $this->publicUploadUrl().'?expires='.$expires;
        $signatureUrl = $this->signatureUploadUrl().'?expires='.$expires;

        return $publicUrl.'&signature='.$this->signature($signatureUrl);
    }

    private function publicUploadUrl(): string
    {
        return rtrim((string) config('app.url'), '/').EndpointResolver::uploadPath();
    }

    private function signatureUploadUrl(): string
    {
        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
        $port = parse_url($appUrl, PHP_URL_PORT);

        return $scheme.'://'.$host.($port ? ':'.$port : '').EndpointResolver::uploadPath();
    }

    private function signature(string $url): string
    {
        $key = config('app.key');
        $key = is_array($key) ? $key[0] : $key;

        return hash_hmac('sha256', $url, $key);
    }
}
