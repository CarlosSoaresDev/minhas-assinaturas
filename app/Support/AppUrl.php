<?php

namespace App\Support;

class AppUrl
{
    public static function normalizeInternalRedirect(?string $target, string $fallbackRoute = 'dashboard'): string
    {
        if (blank($target) || self::isAuthPath($target)) {
            return route($fallbackRoute);
        }

        $target = self::removePublicPath((string) $target);
        $appUrl = self::removePublicPath((string) config('app.url'));
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $appPath = trim(parse_url($appUrl, PHP_URL_PATH) ?? '', '/');

        if ($appUrl === '' || $appPath === '') {
            return $target;
        }

        if (str_starts_with($target, '/')) {
            return $appUrl.$target;
        }

        $targetHost = parse_url($target, PHP_URL_HOST);
        $targetPath = trim(parse_url($target, PHP_URL_PATH) ?? '', '/');

        if ($targetHost === $appHost && $targetPath !== '' && ! str_starts_with($targetPath, $appPath.'/') && $targetPath !== $appPath) {
            $scheme = parse_url($target, PHP_URL_SCHEME) ?: parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
            $query = parse_url($target, PHP_URL_QUERY);
            $fragment = parse_url($target, PHP_URL_FRAGMENT);

            return $scheme.'://'.$appHost.'/'.$appPath.'/'.$targetPath
                .($query ? '?'.$query : '')
                .($fragment ? '#'.$fragment : '');
        }

        return $target;
    }

    private static function isAuthPath(string $target): bool
    {
        $path = trim(parse_url(self::removePublicPath($target), PHP_URL_PATH) ?? $target, '/');
        $appPath = trim(parse_url(self::removePublicPath((string) config('app.url')), PHP_URL_PATH) ?? '', '/');

        if ($appPath !== '' && str_starts_with($path, $appPath.'/')) {
            $path = trim(substr($path, strlen($appPath)), '/');
        }

        return in_array($path, [
            'login',
            'register',
            'logout',
            'two-factor-challenge',
            'user/confirm-password',
        ], true);
    }

    private static function removePublicPath(string $urlOrPath): string
    {
        return preg_replace('#/public(?=/|$)#i', '', rtrim($urlOrPath, '/')) ?: '';
    }
}
