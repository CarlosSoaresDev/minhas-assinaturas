<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileForm extends Component
{
    public string $name = '';
    public bool $alerts_enabled = true;
    public int $alert_days_before = 7;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->alerts_enabled = $user->alerts_enabled;
        $this->alert_days_before = $user->alert_days_before;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'alerts_enabled' => ['boolean'],
            'alert_days_before' => ['integer', 'min:1', 'max:60'],
        ]);

        if (blank($validated['name'] ?? null)) {
            session()->flash('profile_error', 'Informe um nome para atualizar o perfil.');
            return;
        }

        $user->name = $validated['name'];
        $user->alerts_enabled = $validated['alerts_enabled'];
        $user->alert_days_before = $validated['alert_days_before'];
        $user->save();

        session()->flash('profile_success', 'Perfil e preferências atualizados com sucesso!');
    }

    public function render()
    {
        return view('livewire.settings.profile-form');
    }
}
