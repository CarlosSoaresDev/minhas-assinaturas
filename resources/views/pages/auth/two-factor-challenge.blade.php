@extends('layouts.auth.simple')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card bg-dark text-white border-secondary shadow" style="width: 100%; max-width: 450px; border-radius: 14px;">
        <div class="card-body p-4 p-sm-5" x-cloak x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;
                    this.code = '';
                    this.recovery_code = '';
                    $nextTick(() => {
                        if (this.showRecoveryInput) {
                            this.$refs.recovery_code.focus();
                        } else {
                            this.$refs.code.focus();
                        }
                    });
                }
            }">
            
            <div class="text-center mb-4">
                <h4 class="fw-bold text-white mb-2" x-show="!showRecoveryInput">Código de Autenticação</h4>
                <p class="text-secondary small" x-show="!showRecoveryInput">
                    Insira o código de 6 dígitos gerado pelo seu aplicativo autenticador.
                </p>

                <h4 class="fw-bold text-white mb-2" x-show="showRecoveryInput">Código de Recuperação</h4>
                <p class="text-secondary small" x-show="showRecoveryInput">
                    Confirme o acesso à sua conta usando um dos seus códigos de recuperação de emergência.
                </p>
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div x-show="!showRecoveryInput" class="mb-4 text-center">
                    <input type="text" name="code" x-ref="code" x-model="code" x-bind:required="!showRecoveryInput" class="form-control bg-dark text-white border-secondary text-center mx-auto fs-4 tracking-widest @error('code') is-invalid @enderror" style="width: 200px; letter-spacing: 5px; border-radius: 10px;" maxlength="6" autofocus>
                    @error('code')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div x-show="showRecoveryInput" class="mb-4">
                    <label for="recovery_code" class="form-label text-light fw-semibold">Código de Recuperação</label>
                    <input type="text" name="recovery_code" id="recovery_code" x-ref="recovery_code" x-model="recovery_code" x-bind:required="showRecoveryInput" class="form-control bg-dark text-white border-secondary fs-5 text-center @error('recovery_code') is-invalid @enderror" autocomplete="one-time-code" style="letter-spacing: 2px; border-radius: 10px;">
                    @error('recovery_code')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3" style="border-radius: 50px;">
                    Continuar
                </button>

                <div class="text-center mt-3">
                    <span class="text-secondary small">ou você pode</span>
                    <button type="button" class="btn btn-link text-primary p-0 m-0 small text-decoration-none fw-semibold" @click="toggleInput()">
                        <span x-show="!showRecoveryInput">usar um código de recuperação</span>
                        <span x-show="showRecoveryInput">usar o código do aplicativo</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
