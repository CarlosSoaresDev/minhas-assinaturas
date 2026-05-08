<?php

use App\Concerns\PasswordValidationRules;
use App\Services\PasswordSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use function Livewire\Volt\{state, mount, on, layout, title};

layout('pages::settings.layout');
title('Segurança');

state([
    'current_password' => '',
    'password' => '',
    'password_confirmation' => '',
    'needsCurrentPassword' => true,
    'canManageTwoFactor' => false,
    'twoFactorEnabled' => false,
    'requiresConfirmation' => false,
]);

mount(function (DisableTwoFactorAuthentication $disableTwoFactorAuthentication) {
    $user = auth()->user();
    $this->needsCurrentPassword = ! (bool) ($user->created_via_google ?? false);
    $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

    if ($this->canManageTwoFactor) {
        if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($user);
        }

        $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }
});

$updatePassword = function () {
    $rules = [
        'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::default(), 'confirmed'],
    ];

    if ($this->needsCurrentPassword) {
        $rules['current_password'] = [
            'required',
            'string',
            function (string $attribute, mixed $value, \Closure $fail) {
                if (!\App\Services\PasswordSecurityService::checkPassword($value, auth()->user()->password)) {
                    $fail(__('A senha atual está incorreta.'));
                }
            }
        ];
    }

    try {
        $validated = $this->validate($rules);
    } catch (ValidationException $e) {
        $this->reset('current_password', 'password', 'password_confirmation');
        throw $e;
    }

    $user = Auth::user();

    $user->update([
        'password' => PasswordSecurityService::hashPassword($validated['password']),
        'created_via_google' => false,
    ]);

    $this->reset('current_password', 'password', 'password_confirmation');
    $this->needsCurrentPassword = true; // Agora tem senha local
    session()->flash('password_success', 'Senha atualizada com sucesso!');
};

on(['two-factor-enabled' => function () {
    $this->twoFactorEnabled = true;
}]);

$disable = function (DisableTwoFactorAuthentication $disableTwoFactorAuthentication) {
    $disableTwoFactorAuthentication(auth()->user());
    $this->twoFactorEnabled = false;
};

?>

<div>
    @if (session('password_success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('password_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Alterar Senha -->
    <h5 class="fw-bold mb-3"><i class="bi bi-key me-2 text-primary"></i>Alterar Senha</h5>

    @if (! $needsCurrentPassword)
        <div class="alert alert-info border-0 mb-3" role="alert">
            Sua conta foi criada com Google. Defina uma senha para também poder entrar com e-mail e senha.
        </div>
    @endif

    <form wire:submit="updatePassword">
        @if ($needsCurrentPassword)
            <div class="mb-3">
                <label for="current_password" class="form-label text-light fw-semibold">Senha Atual</label>
                <input wire:model="current_password" type="password" class="form-control bg-dark text-white border-secondary @error('current_password') is-invalid @enderror" id="current_password" required style="border-radius: 10px; padding: 10px 15px;">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="mb-3">
            <label for="password" class="form-label text-light fw-semibold">Nova Senha</label>
            <input wire:model="password" type="password" class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror" id="password" required style="border-radius: 10px; padding: 10px 15px;">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label text-light fw-semibold">Confirmar Nova Senha</label>
            <input wire:model="password_confirmation" type="password" class="form-control bg-dark text-white border-secondary" id="password_confirmation" required style="border-radius: 10px; padding: 10px 15px;">
        </div>

        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 50px;">
            <i class="bi bi-check-circle me-1"></i> Salvar Senha
        </button>
    </form>

    @if ($canManageTwoFactor)
        <hr class="border-secondary my-4">

        <!-- 2FA -->
        <h5 class="fw-bold mb-2"><i class="bi bi-shield-lock me-2 text-warning"></i>Autenticação em Duas Etapas (2FA)</h5>

        @if ($twoFactorEnabled)
            <p class="text-secondary small mb-3">
                A autenticação de dois fatores está <strong class="text-success">ativada</strong>. 
                Você será solicitado a inserir um código ao fazer login.
            </p>
            <button wire:click="disable" class="btn btn-outline-danger px-4">
                <i class="bi bi-x-circle me-1"></i> Desativar 2FA
            </button>

            <div class="mt-3">
                <livewire:settings.⚡two-factor.⚡recovery-codes :$requiresConfirmation />
            </div>
        @else
            <p class="text-secondary small mb-3">
                Proteja sua conta adicionando uma camada extra de segurança. 
                Você precisará de um aplicativo TOTP (como Google Authenticator) no seu celular.
            </p>
            <button wire:click="$dispatch('start-two-factor-setup')" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#twoFactorModal">
                <i class="bi bi-shield-check me-1"></i> Ativar 2FA
            </button>

            <livewire:settings.⚡two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
        @endif
    @endif
</div>
