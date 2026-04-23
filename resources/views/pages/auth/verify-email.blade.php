@extends('layouts.auth.simple')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card bg-dark text-white border-secondary shadow" style="width: 100%; max-width: 450px; border-radius: 14px;">
        <div class="card-body p-4 p-sm-5 text-center">
            
            <h4 class="fw-bold mb-3">Verifique seu E-mail</h4>
            <p class="text-secondary small mb-4">
                Antes de continuar, precisamos confirmar seu endereço de e-mail. Por favor, verifique sua caixa de entrada e clique no link de confirmação que enviamos para você.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success small fw-semibold mb-4 border-success text-success bg-transparent">
                    Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3" style="border-radius: 50px;">
                    Reenviar E-mail de Verificação
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link text-danger p-0 m-0 text-decoration-none small" data-test="logout-button">
                    <i class="bi bi-box-arrow-right me-1"></i> Sair da Conta
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
