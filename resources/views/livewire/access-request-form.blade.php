<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $sent = false;

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Insira um e-mail válido.',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'ip' => request()->ip(),
            'requested_at' => now()->toDateTimeString(),
        ];

        // Salva em storage/app/access_requests.json (fora do diretório public)
        $filePath = 'access_requests.json';
        
        $requests = [];
        if (Storage::exists($filePath)) {
            try {
                $requests = json_decode(Storage::get($filePath), true) ?: [];
            } catch (\Exception $e) {
                $requests = [];
            }
        }
        
        $requests[] = $data;
        
        Storage::put($filePath, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->sent = true;
        $this->reset(['name', 'email']);
    }
};
?>

<div>
    @if($sent)
        <div class="text-center py-4 animate__animated animate__fadeIn">
            <div class="mb-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">Solicitação Enviada!</h4>
            <p class="text-secondary mb-4">Em breve seu acesso será liberado, você receberá a confirmação por e-mail.</p>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    @else
        <p class="text-secondary small mb-4">Insira seus dados abaixo para entrar na fila de espera. Entraremos em contato assim que liberarmos novos acessos.</p>
        
        <form wire:submit.prevent="submit">
            <div class="mb-3">
                <label for="req_name" class="form-label text-light small fw-bold">Seu Nome</label>
                <input type="text" id="req_name" wire:model="name" class="form-control bg-dark text-white border-secondary @error('name') is-invalid @enderror" placeholder="Ex: João Silva" style="border-radius: 12px; padding: 12px;">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label for="req_email" class="form-label text-light small fw-bold">E-mail para Contato</label>
                <input type="email" id="req_email" wire:model="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" placeholder="seu@email.com" style="border-radius: 12px; padding: 12px;">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm" style="border-radius: 50px;">
                    <i class="bi bi-send me-2"></i>Solicitar Acesso Agora
                </button>
            </div>
        </form>
    @endif
</div>