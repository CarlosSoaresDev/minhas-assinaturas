<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public bool $showDeleteModal = false;
    public string $password = '';

    public function openDeleteModal(): void
    {
        $this->resetErrorBag();
        $this->password = '';
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->password = '';
    }

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        if (Auth::user()->hasRole('admin')) {
            $this->addError('password', 'Administradores não podem excluir a própria conta por motivos de segurança. Peça a outro administrador.');
            return;
        }

        tap(Auth::user(), function ($user) use ($logout) {
            $logout($user);
            $user->update(['status' => 'inactive']);
            $user->delete();
        });

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h5 class="text-danger fw-bold mb-1">Excluir conta</h5>
            <p class="text-secondary small mb-0">Ao excluir sua conta, todos os seus dados serão perdidos permanentemente.</p>
        </div>
        <button type="button" class="btn btn-outline-danger" wire:click="openDeleteModal" data-test="delete-user-button">
            Excluir conta
        </button>
    </div>

    @if($showDeleteModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,.65); z-index:1060;">
            <div class="card bg-dark border-danger shadow-lg" style="width: min(520px, 92vw);">
                <div class="card-header border-danger d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">Confirmar exclusão da conta</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="cancelDelete">Fechar</button>
                </div>
                <form wire:submit="deleteUser">
                    <div class="card-body">
                        <p class="text-secondary mb-3">Digite sua senha para confirmar a exclusão da conta.</p>
                        <input
                            type="password"
                            wire:model.defer="password"
                            class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                            placeholder="Sua senha"
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer border-danger d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelDelete">Cancelar</button>
                        <button type="submit" class="btn btn-danger" data-test="confirm-delete-user-button">Excluir conta</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
