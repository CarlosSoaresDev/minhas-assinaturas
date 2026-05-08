<?php

namespace App\Livewire\Admin;

use App\Models\Subscription;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class AllSubscriptions extends Component
{
    // use WithPagination; // Removido para evitar que o Livewire force a URL
    public int $page = 1;
    public int $perPage = 15;

    public $search = '';
    public $statusFilter = 'all';
    public $categoryFilter = 'all';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function updatingSearch() { $this->page = 1; }
    public function updatingStatusFilter() { $this->page = 1; }
    public function updatingCategoryFilter() { $this->page = 1; }

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

    public function sortBy($field)
    {
        $allowedFields = ['name', 'amount', 'next_billing_date', 'status', 'created_at'];
        $field = in_array($field, $allowedFields) ? $field : 'name';

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $allowedFields = ['name', 'amount', 'next_billing_date', 'status', 'created_at'];
        $this->sortField = in_array($this->sortField, $allowedFields) ? $this->sortField : 'name';

        $query = Subscription::with(['category'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter !== 'all', fn($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection);

        $total = $query->count();
        $subscriptions = $query->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('livewire.admin.all-subscriptions', [
            'subscriptions' => $subscriptions,
            'categories' => $categories,
            'totalPages' => ceil($total / $this->perPage),
            'totalRecords' => $total
        ]);
    }
}
