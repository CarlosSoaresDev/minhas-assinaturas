<?php

use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Autenticação 2FA')] #[Layout('pages::settings.layout', ['heading' => 'Autenticação 2FA', 'subheading' => 'Adicione uma camada extra de segurança à sua conta'])] class extends Component {
    public bool $canManageTwoFactor;
    public bool $twoFactorEnabled;
    public bool $requiresConfirmation;

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
    }

    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());
        $this->twoFactorEnabled = false;
    }
}; ?>

<div>
    @if ($canManageTwoFactor)
        @if ($twoFactorEnabled)
            <p class="text-secondary small mb-3">
                A autenticação de dois fatores está <strong class="text-success">ativada</strong>. 
                Você será solicitado a inserir um código ao fazer login.
            </p>
            <button wire:click="disable" class="btn btn-outline-danger px-4" style="border-radius: 50px;">
                <i class="bi bi-x-circle me-1"></i> Desativar 2FA
            </button>

            <div class="mt-4">
                <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
            </div>
        @else
            <p class="text-secondary small mb-4">
                Proteja sua conta adicionando uma camada extra de segurança. 
                Você precisará de um aplicativo TOTP (como Google Authenticator) no seu celular para escanear um código QR.
            </p>
            <button wire:click="$dispatch('start-two-factor-setup')" class="btn btn-primary px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#twoFactorModal" style="border-radius: 50px;">
                <i class="bi bi-shield-check me-1"></i> Ativar 2FA
            </button>

        @endif
    @endif

    @if ($canManageTwoFactor)
        <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
    @endif
    

</div>
