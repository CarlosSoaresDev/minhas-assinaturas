<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Url;

class ActivityLogs extends Component
{
    // Removido WithPagination para evitar que o Livewire force a URL
    
    public $page = 1;
    public $search = '';
    public $perPage = 15;

    public function updatingSearch()
    {
        $this->page = 1;
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function render()
    {
        $query = Activity::with('causer', 'subject')
            ->latest();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('log_name', 'like', '%' . $this->search . '%')
                  ->orWhere('event', 'like', '%' . $this->search . '%');
            });
        }

        $total = $query->count();
        $logs = $query->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get();

        return view('livewire.admin.activity-logs', [
            'logs' => $logs,
            'total' => $total,
            'totalPages' => ceil($total / $this->perPage)
        ]);
    }
}
