<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());
    }

    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();
            return;
        }

        $this->setupComplete = true;
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;
    }

    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $wasComplete = $this->setupComplete;

        $this->reset(
            'code',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();

        if ($wasComplete) {
            $this->dispatch('two-factor-enabled');
        }
    }

    #[Computed]
    public function modalConfig(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => 'Autenticação 2FA Ativada',
                'description' => 'A autenticação de duas etapas agora está ativada. Volte à página anterior para gerar seus códigos de recuperação de backup caso perca seu celular.',
                'buttonText' => 'Fechar',
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => 'Verificar Código de Autenticação',
                'description' => 'Insira o código de 6 dígitos gerado no seu aplicativo.',
                'buttonText' => 'Confirmar',
            ];
        }

        return [
            'title' => 'Ativar Autenticação de Duas Etapas',
            'description' => 'Para finalizar a ativação do 2FA, escaneie o código QR abaixo ou insira a chave de configuração manualmente no seu aplicativo autenticador.',
            'buttonText' => 'Continuar',
        ];
    }

    #[Computed]
    public function qrCodeSvg(): string
    {
        $user = auth()->user()?->fresh();

        if (! $user || ! $user->two_factor_secret) {
            return '';
        }

        try {
            return $user->twoFactorQrCodeSvg();
        } catch (\Throwable) {
            return '';
        }
    }

    #[Computed]
    public function manualSetupKey(): string
    {
        $user = auth()->user()?->fresh();

        if (! $user || ! $user->two_factor_secret) {
            return '';
        }

        try {
            return decrypt($user->two_factor_secret);
        } catch (\Throwable) {
            return '';
        }
    }
}; ?>

<div class="modal fade" id="twoFactorModal" tabindex="-1" aria-labelledby="twoFactorModalLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="twoFactorModalLabel">{{ $this->modalConfig['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeModal"></button>
            </div>
            
            <div class="modal-body p-4 text-center">
                <p class="text-secondary small mb-4">{{ $this->modalConfig['description'] }}</p>

                @if ($setupComplete)
                    <div class="text-success my-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                    </div>
                @else
                    @if ($showVerificationStep)
                        <div class="mb-4">
                            <label for="code" class="form-label fw-semibold">Código do Aplicativo</label>
                            <input wire:model="code" type="text" class="form-control text-center mx-auto fs-4 tracking-widest @error('code') is-invalid @enderror" style="width: 200px; letter-spacing: 5px; border-radius: 10px;" maxlength="6" autofocus>
                            @error('code')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="d-flex justify-content-center mb-4">
                            <div class="bg-white p-3 rounded d-inline-block shadow-sm two-factor-qr-wrapper">
                                @if(blank($this->qrCodeSvg))
                                    <div class="d-flex align-items-center justify-center p-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                    </div>
                                @else
                                    {!! $this->qrCodeSvg !!}
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="text-secondary small mb-2">ou insira o código manualmente:</p>
                            <div class="input-group mb-3">
                                <input type="text" readonly class="form-control font-monospace" value="{{ $this->manualSetupKey }}" id="manualSetupKeyInput">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                    <i class="bi bi-clipboard"></i> Copiar
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="modal-footer border-top">
                @if ($setupComplete)
                    <button type="button" class="btn btn-primary w-100 fw-bold" data-bs-dismiss="modal" wire:click="closeModal" style="border-radius: 50px;">
                        {{ $this->modalConfig['buttonText'] }}
                    </button>
                @elseif ($showVerificationStep)
                    <button type="button" class="btn btn-outline-secondary flex-grow-1" wire:click="resetVerification" style="border-radius: 50px;">Voltar</button>
                    <button type="button" class="btn btn-primary flex-grow-1 fw-bold" wire:click="confirmTwoFactor" style="border-radius: 50px;">
                        {{ $this->modalConfig['buttonText'] }}
                    </button>
                @else
                    <button type="button" class="btn btn-primary w-100 fw-bold" wire:click="showVerificationIfNecessary" style="border-radius: 50px;">
                        {{ $this->modalConfig['buttonText'] }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Mantém o QR legível mesmo com estilos globais de ícones. */
    #twoFactorModal .two-factor-qr-wrapper svg {
        width: 180px !important;
        height: 180px !important;
        min-width: 180px;
        min-height: 180px;
    }
</style>

<script>
    function copyToClipboard() {
        var copyText = document.getElementById("manualSetupKeyInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        let btn = copyText.nextElementSibling;
        let originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i> Copiado';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('hide-two-factor-modal', () => {
            const modalEl = document.getElementById('twoFactorModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            }
        });
    });
</script>
