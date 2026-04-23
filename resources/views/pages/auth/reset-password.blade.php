@extends('layouts.auth.simple')

@section('content')
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 py-5">
        <div class="col-12 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-lg border-secondary bg-dark bg-opacity-50" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="text-white fw-bold mb-4 text-center">Redefinir senha</h5>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ request()->route('token') }}">

                        <div class="mb-3">
                            <label for="email" class="form-label text-light fw-medium">E-mail</label>
                            <input id="email" type="email" name="email" value="{{ request('email') }}" required autocomplete="email"
                                class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-light fw-medium">Nova senha</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                                style="border-radius: 12px; padding: 12px 16px;">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label text-light fw-medium">Confirmar nova senha</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                class="form-control bg-dark text-white border-secondary"
                                style="border-radius: 12px; padding: 12px 16px;">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold py-2" style="border-radius: 50px;">
                                Atualizar senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
