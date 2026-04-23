<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Alterar Senha')] #[Layout('pages::settings.layout', ['heading' => 'Alterar Senha', 'subheading' => 'Gerencie sua senha de acesso'])] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        session()->flash('password_success', 'Senha atualizada com sucesso!');
    }
}; ?>

<div>
    @if (session('password_success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('password_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="updatePassword">
        <div class="mb-3">
            <label for="current_password" class="form-label text-light fw-semibold">Senha Atual</label>
            <input wire:model="current_password" type="password" class="form-control bg-dark text-white border-secondary @error('current_password') is-invalid @enderror" id="current_password" required style="border-radius: 10px; padding: 10px 15px;">
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

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
</div>
