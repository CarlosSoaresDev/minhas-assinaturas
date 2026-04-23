@extends('layouts.auth.simple')

@section('content')
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 py-5">
        <div class="col-12 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-lg border-secondary bg-dark bg-opacity-50" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="text-white fw-bold mb-2 text-center">Recuperar senha</h5>
                    <p class="text-secondary small text-center mb-4">Informe seu e-mail para receber o link de redefinição.</p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                        
                        {{-- Debug: Mostrar link na tela em ambiente local --}}
                        @if (config('app.env') === 'local')
                            <div class="mt-3 p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded" style="border-radius: 12px;">
                                <p class="text-primary small mb-1 fw-bold">Ambiente de Teste: Link de Recuperação</p>
                                <p class="text-secondary small mb-2">Como o e-mail não é enviado em localhost, use o link abaixo:</p>
                                @php
                                    // Pega o último token gerado para o e-mail informado
                                    $tokenData = DB::table('password_reset_tokens')->where('email', old('email'))->first();
                                    $resetUrl = $tokenData ? route('password.reset', ['token' => $tokenData->token, 'email' => old('email')]) : '#';
                                @endphp
                                <a href="{{ $resetUrl }}" class="text-white small text-break">{{ $resetUrl }}</a>
                            </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label text-light fw-medium">E-mail</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror"
                                style="border-radius: 12px; padding: 12px 16px;"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary fw-bold py-2" style="border-radius: 50px;">
                                Enviar link de recuperação
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-primary text-decoration-none small fw-medium">Voltar para login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
