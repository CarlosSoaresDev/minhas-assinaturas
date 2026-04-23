<?php

namespace App\Livewire\Domains;

use App\Models\Domain;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $token = current(auth()->user()->privacyToken()->pluck('token')->toArray());
        
        $domains = Domain::byPrivacyToken($token)
                        ->orderBy('expiration_date', 'asc')
                        ->get();

        return view('livewire.domains.index', [
            'domains' => $domains
        ]);
    }
}
