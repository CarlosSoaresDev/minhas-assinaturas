@extends('layouts.auth.simple')
@section('content')
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 py-5">
        <div class="col-12 col-md-8 col-lg-5 col-xl-4">
            
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-check text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <h2 class="text-white fw-bold h4 mb-1">Minhas Assinaturas</h2>
                    <p class="text-primary opacity-75 small">Ofuscamento imediato dos seus dados.</p>
                </a>
            </div>

            <div class="card shadow-lg auth-card-glass" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="text-white fw-bold mb-4 text-center">Criar Conta</h5>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label text-light fw-medium">Nome</label>
                            <input id="name" type="text" 
                                class="form-control bg-dark text-white border-secondary @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label text-light fw-medium">E-mail</label>
                            <input id="email" type="email" 
                                class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email') }}" required autocomplete="username"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Senha -->
                        <div class="mb-3">
                            <label for="password" class="form-label text-light fw-medium">Senha</label>
                            <input id="password" type="password" 
                                class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror" 
                                name="password" required autocomplete="new-password"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-secondary x-small mt-1">
                                <i class="bi bi-info-circle me-1"></i> Mínimo 8 caracteres, uma letra maiúscula e um símbolo.
                            </div>
                        </div>

                        <!-- Confirmar Senha -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label text-light fw-medium">Confirmar Senha</label>
                            <input id="password_confirmation" type="password" 
                                class="form-control bg-dark text-white border-secondary" 
                                name="password_confirmation" required autocomplete="new-password"
                                style="border-radius: 12px; padding: 12px 16px;">
                        </div>

                        <!-- LGPD -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input bg-dark border-secondary @error('lgpd_consent') is-invalid @enderror" type="checkbox" name="lgpd_consent" value="1" id="lgpd_consent" required>
                                <label class="form-check-label text-secondary small" for="lgpd_consent">
                                    Li e aceito os <a href="#" class="text-primary text-decoration-none">Termos de Privacidade</a>. Os dados serão ofuscados irreversivelmente.
                                </label>
                                @error('lgpd_consent')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm" style="border-radius: 50px;">
                                <i class="bi bi-person-plus me-2"></i>Criar Conta
                            </button>
                        </div>

                        <div class="text-center">
                            <span class="text-secondary small">Já possui uma conta?</span>
                            <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none small ms-1">Entrar Agora</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-secondary opacity-50 x-small">
                    &copy; {{ date('Y') }} Minhas Assinaturas. Segurança e Privacidade em primeiro lugar.
                </p>
            </div>
        </div>
    </div>
@endsection
