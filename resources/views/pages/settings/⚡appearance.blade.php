<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Title('Aparência')] #[Layout('pages::settings.layout', ['heading' => 'Aparência', 'subheading' => 'Personalize a aparência visual da plataforma'])] class extends Component {
    //
}; ?>

<div>
    <h5 class="fw-bold mb-3"><i class="bi bi-palette me-2 text-primary"></i>Tema da Interface</h5>
    <p class="text-secondary small mb-4">Escolha o tema visual de sua preferência. Atualmente, a plataforma opera exclusivamente em modo escuro para conforto visual.</p>
    
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-primary bg-dark bg-opacity-75 shadow-sm text-center p-4" style="border-radius: 14px; border-width: 2px;">
                <i class="bi bi-moon-stars-fill text-primary fs-1 mb-3"></i>
                <h6 class="fw-bold text-white">Escuro</h6>
                <p class="text-secondary small mb-2">Tema padrão otimizado para uso noturno.</p>
                <span class="badge bg-primary">Ativo</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary bg-dark bg-opacity-25 shadow-sm text-center p-4 opacity-50" style="border-radius: 14px;">
                <i class="bi bi-sun-fill text-warning fs-1 mb-3"></i>
                <h6 class="fw-bold text-white">Claro</h6>
                <p class="text-secondary small mb-2">Tema claro para uso diurno.</p>
                <span class="badge bg-secondary">Em breve</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary bg-dark bg-opacity-25 shadow-sm text-center p-4 opacity-50" style="border-radius: 14px;">
                <i class="bi bi-laptop text-info fs-1 mb-3"></i>
                <h6 class="fw-bold text-white">Sistema</h6>
                <p class="text-secondary small mb-2">Segue a preferência do seu SO.</p>
                <span class="badge bg-secondary">Em breve</span>
            </div>
        </div>
    </div>
</div>
