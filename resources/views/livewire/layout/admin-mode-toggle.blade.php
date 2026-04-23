<?php

use Livewire\Component;

new class extends Component {
    public bool $adminMode = true;

    public function mount(): void
    {
        $this->adminMode = session('admin_mode', true);
    }

    public function toggle(): void
    {
        $this->adminMode = !$this->adminMode;
        session(['admin_mode' => $this->adminMode]);
        
        $this->redirect(request()->header('Referer') ?? route('dashboard'));
    }
}; ?>

<div class="form-check form-switch d-flex align-items-center gap-2 bg-dark bg-opacity-50 px-3 py-1 border border-secondary rounded-pill shadow-sm" style="cursor: pointer;" wire:click="toggle">
    <input class="form-check-input ms-0" type="checkbox" role="switch" id="adminModeSwitch" style="cursor: pointer; width: 2.2em; height: 1.1em;" {{ $adminMode ? 'checked' : '' }}>
    <label class="form-check-label small fw-bold text-white mb-0" for="adminModeSwitch" style="cursor: pointer; user-select: none;">
        Modo Admin: <span class="{{ $adminMode ? 'text-primary' : 'text-secondary' }}">{{ $adminMode ? 'ON' : 'OFF' }}</span>
    </label>
</div>
