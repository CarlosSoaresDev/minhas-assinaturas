<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-dark text-white min-vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="container py-5 d-flex flex-column align-items-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                            <x-app-logo-icon width="40" height="40" class="text-primary" />
                        </div>
                        <h5 class="text-white fw-bold mb-0">SignManager</h5>
                    </a>
                </div>

                <div class="card shadow-lg border-secondary bg-dark bg-opacity-50 p-4" style="border-radius: 16px;">
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
