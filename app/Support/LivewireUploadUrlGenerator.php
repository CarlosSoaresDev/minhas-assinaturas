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
        $url = $this->uploadUrl().'?expires='.$expires;

        return $url.'&signature='.$this->signature($url);
    }

    private function uploadUrl(): string
    {
        return rtrim((string) config('app.url'), '/').EndpointResolver::uploadPath();
    }

    private function signature(string $url): string
    {
        $key = config('app.key');
        $key = is_array($key) ? $key[0] : $key;

        return hash_hmac('sha256', $url, $key);
    }
}
