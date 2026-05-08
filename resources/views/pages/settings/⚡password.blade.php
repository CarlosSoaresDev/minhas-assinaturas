<?php

use App\Concerns\PasswordValidationRules;
use App\Services\PasswordSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $isGoogleUser = false;

    public function mount(): void
    {
        $this->isGoogleUser = (bool) (auth()->user()->created_via_google ?? false);
    }

    public function updatePassword(): void
    {
        $rules = [
            'password' => $this->passwordRules(),
        ];

        // Só exige senha atual se NÃO for usuário Google (ou se já tiver criado senha antes)
        if (!$this->isGoogleUser) {
            $rules['current_password'] = $this->currentPasswordRules();
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
            'created_via_google' => false, // Agora ele tem uma senha real
        ]);

        $this->isGoogleUser = false;
        $this->reset('current_password', 'password', 'password_confirmation');
        session()->flash('password_success', 'Senha definida com sucesso!');
    }

    #[Title('Senha')]
    #[Layout('pages::settings.layout', ['heading' => 'Segurança da Conta', 'subheading' => 'Gerencie sua senha de acesso'])]
    public function rendering($view)
    {
        $view->title($this->isGoogleUser ? 'Criar Senha' : 'Alterar Senha');
    }
}; ?>

<div>
    @if (session('password_success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('password_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h5 class="fw-bold text-white">
            <i class="bi {{ $isGoogleUser ? 'bi-plus-circle' : 'bi-shield-lock' }} me-2 text-primary"></i>
            {{ $isGoogleUser ? 'Criar sua primeira senha' : 'Alterar sua senha atual' }}
        </h5>
        @if($isGoogleUser)
            <p class="text-secondary small">Sua conta está vinculada ao Google. Defina uma senha para poder entrar usando seu e-mail diretamente.</p>
        @endif
    </div>

    <form wire:submit="updatePassword">
        @if (!$isGoogleUser)
            <div class="mb-3">
                <label for="current_password" class="form-label text-light fw-semibold">Senha Atual</label>
                <input wire:model="current_password" type="password" class="form-control bg-dark text-white border-secondary @error('current_password') is-invalid @enderror" id="current_password" required style="border-radius: 10px; padding: 10px 15px;">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="mb-3">
            <label for="password" class="form-label text-light fw-semibold">{{ $isGoogleUser ? 'Definir Senha' : 'Nova Senha' }}</label>
            <input wire:model="password" type="password" class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror" id="password" required style="border-radius: 10px; padding: 10px 15px;">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text text-secondary small mt-2">
                Use pelo menos 8 caracteres com letras, números e símbolos.
            </div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label text-light fw-semibold">Confirmar {{ $isGoogleUser ? 'Senha' : 'Nova Senha' }}</label>
            <input wire:model="password_confirmation" type="password" class="form-control bg-dark text-white border-secondary" id="password_confirmation" required style="border-radius: 10px; padding: 10px 15px;">
        </div>

        <hr class="border-secondary my-4">

        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 50px;">
            <i class="bi bi-save me-1"></i> {{ $isGoogleUser ? 'Criar Senha' : 'Salvar Alterações' }}
        </button>
    </form>
</div>
