<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $theme = 'dark';

    public function mount()
    {
        $this->theme = Auth::user()->theme ?? 'dark';
    }

    public function toggleTheme()
    {
        $this->theme = $this->theme === 'dark' ? 'light' : 'dark';
        
        $user = Auth::user();
        $user->theme = $this->theme;
        $user->save();

        $this->dispatch('theme-updated', theme: $this->theme);
    }
}; ?>

<div>
    <button wire:click="toggleTheme" class="nav-link nav-link-top border-0 bg-transparent d-flex align-items-center" title="Alternar Tema">
        @if($theme === 'dark')
            <i class="bi bi-moon-stars-fill text-warning me-1"></i> <span class="d-lg-none">Modo Escuro</span>
        @else
            <i class="bi bi-sun-fill text-warning me-1"></i> <span class="d-lg-none">Modo Claro</span>
        @endif
    </button>
</div>
