@extends('layouts.auth.simple')

@section('content')
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 py-5">
        <div class="col-12 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-lg border-secondary bg-dark bg-opacity-50" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="text-white fw-bold mb-2 text-center">Confirmar senha</h5>
                    <p class="text-secondary small text-center mb-4">Por segurança, confirme sua senha para continuar.</p>

                    <form method="POST" action="{{ route('password.confirm.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="password" class="form-label text-light fw-medium">Senha</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold py-2" style="border-radius: 50px;">
                                Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
