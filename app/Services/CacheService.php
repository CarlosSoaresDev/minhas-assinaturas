<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Puxa valores customizados do usuário mantendo isolamento da chave por Token the Privacidade.
     */
    public function getUserCache(string $privacyToken, string $metric, Closure $callback, int $ttl = 3600)
    {
        $version = $this->getCacheVersion($privacyToken);
        $key = "user_{$privacyToken}_v{$version}_{$metric}";

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalida globalmente o cache incrementando a versão do usuário.
     */
    public function invalidateUserCache(string $privacyToken): void
    {
        $version = $this->getCacheVersion($privacyToken);
        Cache::put("user_{$privacyToken}_cache_version", $version + 1, 86400 * 30);
    }

    private function getCacheVersion(string $privacyToken): int
    {
        return (int) Cache::get("user_{$privacyToken}_cache_version", 1);
    }
}
