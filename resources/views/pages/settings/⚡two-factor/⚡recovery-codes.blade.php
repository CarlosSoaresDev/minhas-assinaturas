<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Falha ao carregar os códigos de recuperação.');
                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div class="card border-secondary bg-dark text-white shadow-sm mt-4" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-2">
            <i class="bi bi-shield-lock me-2 text-warning"></i>Códigos de Recuperação 2FA
        </h5>
        <p class="text-secondary small mb-4">
            Os códigos de recuperação permitem que você recupere o acesso à sua conta caso perca o dispositivo de autenticação. 
            Guarde-os em um local seguro, como um gerenciador de senhas.
        </p>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-primary fw-bold px-4" x-show="!showRecoveryCodes" @click="showRecoveryCodes = true;" style="border-radius: 50px;">
                <i class="bi bi-eye me-1"></i> Ver Códigos de Recuperação
            </button>

            <button class="btn btn-outline-light px-4" x-show="showRecoveryCodes" @click="showRecoveryCodes = false" style="border-radius: 50px;" x-cloak>
                <i class="bi bi-eye-slash me-1"></i> Ocultar Códigos
            </button>

            @if (filled($recoveryCodes))
                <button class="btn btn-outline-warning px-4" x-show="showRecoveryCodes" wire:click="regenerateRecoveryCodes" style="border-radius: 50px;" x-cloak>
                    <i class="bi bi-arrow-clockwise me-1"></i> Gerar Novos Códigos
                </button>
            @endif
        </div>

        <div x-show="showRecoveryCodes" x-transition x-cloak class="mt-4">
            @error('recoveryCodes')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @if (filled($recoveryCodes))
                <div class="bg-black bg-opacity-25 border border-secondary rounded p-3 mb-2 font-monospace tracking-widest text-center" style="column-count: 2; column-gap: 20px; letter-spacing: 2px;">
                    @foreach($recoveryCodes as $code)
                        <div class="py-1" wire:loading.class="opacity-50 user-select-all">{{ $code }}</div>
                    @endforeach
                </div>
                <small class="text-secondary">
                    <i class="bi bi-info-circle me-1"></i> Cada código só pode ser usado uma vez. Quando acabarem, lembre-se de gerar novos.
                </small>
            @endif
        </div>
    </div>
</div>
