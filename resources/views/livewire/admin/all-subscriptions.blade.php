<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Todos os Serviços</h2>
            <p class="text-secondary mb-0">Visualização global de todas as assinaturas e domínios do sistema.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group shadow-sm border border-secondary border-opacity-25 rounded-3 overflow-hidden">
                        <span class="input-group-text bg-dark border-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-dark border-0 text-white p-3" placeholder="Buscar por nome do serviço...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="categoryFilter" class="form-select bg-dark border-secondary border-opacity-25 text-white p-3 shadow-sm h-100" style="border-radius: 8px;">
                        <option value="all">Todas as Categorias</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select bg-dark border-secondary border-opacity-25 text-white p-3 shadow-sm h-100" style="border-radius: 8px;">
                        <option value="all">Todos os Status</option>
                        <option value="active">Ativo</option>
                        <option value="paused">Pausado</option>
                        <option value="cancelled">Cancelado</option>
                        <option value="expired">Expirado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 responsive-table">
                    <thead class="bg-dark bg-opacity-50">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-4 py-3 cursor-pointer" wire:click="sortBy('name')">
                                Serviço 
                                @if($sortField === 'name')
                                    <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-primary"></i>
                                @else
                                    <i class="bi bi-arrow-down-up opacity-25"></i>
                                @endif
                            </th>
                            <th class="py-3">Categoria</th>
                            <th class="py-3 cursor-pointer" wire:click="sortBy('billing_cycle')">
                                Ciclo
                                @if($sortField === 'billing_cycle')
                                    <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-primary"></i>
                                @else
                                    <i class="bi bi-arrow-down-up opacity-25"></i>
                                @endif
                            </th>
                            <th class="py-3 cursor-pointer" wire:click="sortBy('amount')">
                                Valor
                                @if($sortField === 'amount')
                                    <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-primary"></i>
                                @else
                                    <i class="bi bi-arrow-down-up opacity-25"></i>
                                @endif
                            </th>
                            <th class="py-3">Início</th>
                            <th class="py-3 cursor-pointer" wire:click="sortBy('next_billing_date')">
                                Vencimento
                                @if($sortField === 'next_billing_date')
                                    <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-primary"></i>
                                @else
                                    <i class="bi bi-arrow-down-up opacity-25"></i>
                                @endif
                            </th>
                            <th class="py-3">Status</th>
                            <th class="pe-4 py-3">Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td class="ps-4 py-3" data-label="Serviço">
                                    <div class="d-flex align-items-center">
                                        @if($sub->logo_url)
                                            <img src="{{ $sub->logo_url }}" class="rounded me-2" style="width: 32px; height: 32px; object-fit: contain;">
                                        @else
                                            <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <i class="bi bi-box"></i>
                                            </div>
                                        @endif
                                        <div>
                                            @if($sub->service_url)
                                                <a href="{{ $sub->service_url }}" target="_blank" class="fw-bold text-info text-decoration-none d-block">
                                                    {{ $sub->name }} <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                                </a>
                                            @else
                                                <span class="fw-bold text-white d-block">{{ $sub->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3" data-label="Categoria">
                                    @php $catColor = $sub->category?->color ?? '#6c757d'; @endphp
                                    <span class="badge rounded-pill border" style="background-color: {{ $catColor }}20; color: {{ $catColor }}; border-color: {{ $catColor }}40 !important;">
                                        {{ $sub->category?->name ?? 'Geral' }}
                                    </span>
                                </td>
                                <td class="py-3 text-secondary small" data-label="Ciclo">
                                    {{ match($sub->billing_cycle) {
                                        'monthly' => 'Mensal',
                                        'quarterly' => 'Trimestral',
                                        'semiannual' => 'Semestral',
                                        'yearly' => 'Anual',
                                        'custom' => 'Personalizado',
                                        default => $sub->billing_cycle
                                    } }}
                                </td>
                                <td class="py-3 fw-semibold text-white" data-label="Valor">
                                    {{ $sub->currency ?? 'BRL' }} {{ number_format($sub->amount, 2, ',', '.') }}
                                </td>
                                <td class="py-3 text-secondary small" data-label="Início">
                                    {{ $sub->start_date?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="py-3 text-secondary" data-label="Vencimento">
                                    {{ $sub->next_billing_date?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="py-3" data-label="Status">
                                    @php
                                        $statusClasses = [
                                            'active' => 'bg-success text-success',
                                            'cancelled' => 'bg-danger text-danger',
                                            'expired' => 'bg-warning text-warning',
                                            'paused' => 'bg-info text-info',
                                        ];
                                        $class = $statusClasses[$sub->status] ?? 'bg-secondary text-secondary';
                                        $statusLabel = match($sub->status) {
                                            'active' => 'Ativo',
                                            'cancelled' => 'Cancelado',
                                            'expired' => 'Expirado',
                                            'paused' => 'Pausado',
                                            default => ucfirst($sub->status)
                                        };
                                    @endphp
                                    <span class="badge {{ $class }} bg-opacity-10 border border-opacity-25 rounded-pill">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3" data-label="Notas">
                                    <span class="text-secondary small d-inline-block text-truncate" style="max-width: 150px;" title="{{ $sub->notes }}">
                                        {{ $sub->notes ?: '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    Nenhuma assinatura encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($totalPages > 1)
            <div class="card-footer bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <div class="text-secondary small">
                    Mostrando {{ count($subscriptions) }} de {{ $totalRecords }} registros
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $page <= 1 ? 'disabled' : '' }}">
                            <button class="page-link bg-dark border-secondary text-light" wire:click="previousPage" wire:loading.attr="disabled">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </li>
                        
                        @for($i = 1; $i <= $totalPages; $i++)
                            @if($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2))
                                <li class="page-item {{ $page == $i ? 'active' : '' }}">
                                    <button class="page-link {{ $page == $i ? 'bg-primary border-primary' : 'bg-dark border-secondary text-light' }}" wire:click="gotoPage({{ $i }})">{{ $i }}</button>
                                </li>
                            @elseif($i == $page - 3 || $i == $page + 3)
                                <li class="page-item disabled"><span class="page-link bg-dark border-secondary text-light">...</span></li>
                            @endif
                        @endfor

                        <li class="page-item {{ $page >= $totalPages ? 'disabled' : '' }}">
                            <button class="page-link bg-dark border-secondary text-light" wire:click="nextPage" wire:loading.attr="disabled">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <style>
        .cursor-pointer { cursor: pointer; transition: all 0.2s; }
        .cursor-pointer:hover { background-color: rgba(255,255,255,0.02); }
    </style>
</div>
