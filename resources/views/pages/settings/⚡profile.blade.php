<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Configurações do Perfil')] #[Layout('pages::settings.layout', ['heading' => 'Perfil', 'subheading' => 'Atualize seu nome e e-mail'])] class extends Component {
    public string $name = '';
    public function mount(): void
    {
        $this->name = Auth::user()->name;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($validated['name'] ?? null)) {
            session()->flash('profile_error', 'Informe um nome para atualizar o perfil.');
            return;
        }

        $user->name = $validated['name'];
        $user->save();

        session()->flash('profile_success', 'Perfil atualizado com sucesso!');
    }
}; ?>
<div>
    @if (session('profile_success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('profile_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('profile_error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('profile_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="updateProfileInformation">
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome</label>
            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" required autofocus style="border-radius: 10px; padding: 10px 15px;">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input type="email" class="form-control" id="email" value="{{ Auth::user()->email }}" disabled style="border-radius: 10px; padding: 10px 15px;">
            <small class="text-muted">Você não pode alterar o seu e-mail.</small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 50px;">
                <i class="bi bi-check-circle me-1"></i> Salvar Alterações
            </button>
        </div>
    </form>

    <hr class="border-secondary my-5">

    <div class="mt-4">
        <h4 class="fw-bold mb-3"><i class="bi bi-database me-2"></i>Gestão de Dados</h4>
        <livewire:settings.data-management />
    </div>
</div>
