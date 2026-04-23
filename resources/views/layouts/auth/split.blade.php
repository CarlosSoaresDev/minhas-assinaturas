<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-dark text-white min-vh-100">
        <div class="container-fluid p-0 d-flex min-vh-100 flex-column flex-lg-row">
            <div class="col-lg-6 d-none d-lg-flex flex-column p-5 justify-content-between position-relative bg-dark border-end border-secondary">
                <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 overflow-hidden">
                    <x-app-logo-icon width="32" height="32" class="text-primary" />
                    <span class="fs-4 fw-bold">SignManager</span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="mt-auto">
                    <blockquote class="blockquote">
                        <p class="fs-4">"{{ trim($message) }}"</p>
                        <footer class="blockquote-footer mt-2">{{ trim($author) }}</footer>
                    </blockquote>
                </div>
            </div>
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-5">
                <div class="w-100" style="max-width: 400px;">
                    <div class="text-center mb-4 d-lg-none">
                        <a href="{{ route('home') }}" class="text-decoration-none">
                            <x-app-logo-icon width="48" height="48" class="text-primary mb-2" />
                            <h4 class="text-white fw-bold">SignManager</h4>
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
