<div>
    <style>
        .responsive-table th, .responsive-table td {
            vertical-align: middle;
        }

        .category-icon-circle {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
        }

        .category-icon-circle i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            width: 100%;
        }

        .table-active {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }
    </style>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
        <div>
            <h1 class="h2 mb-0 fw-bold">Assinaturas</h1>
            <p class="text-secondary small">Gerencie todas as suas assinaturas em um só lugar.</p>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-info rounded-pill px-4 shadow-sm" wire:click="exportCsv">
                    <i class="bi bi-download"></i><span>Exportar</span>
                </button>
                <div class="position-relative">
                    <button class="btn btn-outline-success rounded-pill px-4 shadow-sm" onclick="document.getElementById('csv_input').click()">
                        <i class="bi bi-file-earmark-spreadsheet"></i><span>Importar CSV</span>
                    </button>
                    <input type="file" id="csv_input" class="d-none" wire:model="csvFile" accept=".csv">
                </div>
                
                @if(count($selectedIds) > 0)
                    <button class="btn btn-danger rounded-pill px-4 shadow-lg animate__animated animate__fadeIn" 
                            onclick="confirm('Tem certeza que deseja excluir {{ count($selectedIds) }} assinaturas?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected">
                        <i class="bi bi-trash-fill"></i><span>Excluir ({{ count($selectedIds) }})</span>
                    </button>
                @endif
            </div>
            
            <button class="btn btn-custom-primary rounded-pill px-4 py-2 shadow-sm fw-bold" wire:click="openCreateModal">
                <i class="bi bi-plus-circle"></i><span>Nova Assinatura</span>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div wire:key="alert-success" class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill"></i> <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div wire:key="alert-error" class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill"></i> <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="card shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body pb-0">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Buscar assinatura..."
                            wire:model.live.debounce.300ms="search"
                            maxlength="100"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="categoryFilter">
                        <option value="all">Todas as Categorias</option>
                        <option value="none">Sem Categoria</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="statusFilter">
                        <option value="all">Todos os Status</option>
                        <option value="active">Ativo</option>
                        <option value="paused">Pausado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 responsive-table">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input class="form-check-input" type="checkbox" wire:model.live="selectAll">
                        </th>
                        <th class="py-3" style="cursor: pointer;" wire:click="sortBy('name')">
                            <div class="d-inline-flex align-items-center">
                                <span>Serviço</span>
                                @if($sortColumn === 'name') <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </div>
                        </th>
                        <th class="py-3"><span>Categoria</span></th>
                        <th class="py-3"><span>Início</span></th>
                        <th class="py-3" style="cursor: pointer;" wire:click="sortBy('amount')">
                            <div class="d-inline-flex align-items-center">
                                <span>Valor</span>
                                @if($sortColumn === 'amount') <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </div>
                        </th>
                        <th class="py-3" style="cursor: pointer;" wire:click="sortBy('next_billing_date')">
                            <div class="d-inline-flex align-items-center">
                                <span>Vencimento</span>
                                @if($sortColumn === 'next_billing_date') <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </div>
                        </th>
                        <th class="py-3"><span>Renovação</span></th>
                        <th class="py-3" style="cursor: pointer;" wire:click="sortBy('status')">
                            <div class="d-inline-flex align-items-center">
                                <span>Status</span>
                                @if($sortColumn === 'status') <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </div>
                        </th>
                        <th class="py-3 text-end pe-4"><span>Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr wire:key="subscription-{{ $sub->id }}" class="{{ in_array($sub->id, $selectedIds) ? 'table-active' : '' }}">
                            <td class="ps-4 py-3">
                                <input class="form-check-input" type="checkbox" value="{{ $sub->id }}" wire:model.live="selectedIds">
                            </td>
                            <td class="py-3" data-label="Serviço">
                                <div class="d-flex align-items-center">
                                    <div class="category-icon-circle rounded-circle bg-secondary bg-opacity-25">
                                        <i class="bi {{ $sub->category->icon ?? 'bi-box' }} fs-5"></i>
                                    </div>
                                    <div class="ms-3">
                                        @if($sub->service_url)
                                            <a href="{{ $sub->service_url }}" target="_blank" class="fw-bold text-info text-decoration-none">
                                                <span>{{ $sub->name }}</span> <i class="bi bi-box-arrow-up-right small ms-1" style="font-size: 0.7rem;"></i>
                                            </a>
                                        @else
                                            <div class="fw-bold text-white"><span>{{ $sub->name }}</span></div>
                                        @endif
                                        @if($sub->notes)
                                            <div class="text-secondary small text-truncate" style="max-width: 200px;" title="{{ $sub->notes }}">
                                                <i class="bi bi-sticky me-1"></i> {{ $sub->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3" data-label="Categoria">
                                <span class="badge px-3 py-2 rounded-pill border" style="background-color: {{ $sub->category->color ?? '#6c757d' }}20; color: {{ $sub->category->color ?? '#fff' }}; border-color: {{ $sub->category->color ?? '#6c757d' }}40 !important;">
                                    <i class="bi {{ $sub->category->icon ?? 'bi-tag' }}"></i>
                                    <span>{{ $sub->category->name ?? 'Sem categoria' }}</span>
                                </span>
                            </td>
                            <td class="py-3 text-secondary small" data-label="Início">
                                {{ $sub->start_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="py-3" data-label="Valor">
                                <div class="fw-semibold">
                                    {{ $sub->currency === 'USD' ? 'US$' : ($sub->currency === 'EUR' ? '€' : 'R$') }} 
                                    {{ number_format($sub->amount, 2, ',', '.') }}
                                </div>
                                <div class="text-secondary small">
                                    @if($sub->billing_cycle === 'custom')
                                        {{ $sub->custom_cycle_interval }} {{ str_replace(['days','months','years'], ['Dias','Meses','Anos'], $sub->custom_cycle_period) }}
                                    @else
                                        {{ match($sub->billing_cycle) {
                                            'monthly' => 'Mensal',
                                            'quarterly' => 'Trimestral',
                                            'semiannual' => 'Semestral',
                                            'yearly' => 'Anual',
                                            default => $sub->billing_cycle
                                        } }}
                                    @endif
                                </div>
                            </td>
                            <td class="py-3" data-label="Vencimento">
                                @if($sub->status === 'cancelled')
                                    @if($sub->next_billing_date)
                                        @if($sub->next_billing_date->isFuture())
                                            <div class="text-info fw-bold small">Termina em</div>
                                            <div class="text-info">{{ $sub->next_billing_date->format('d/m/Y') }}</div>
                                        @else
                                            <div class="text-secondary fw-bold small">Encerrado</div>
                                            <div class="text-secondary small">{{ $sub->next_billing_date->format('d/m/Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                @elseif($sub->next_billing_date)
                                    <div class="{{ $sub->next_billing_date->isPast() && $sub->status == 'active' ? 'text-danger fw-bold' : '' }}">
                                        {{ $sub->next_billing_date->format('d/m/Y') }}
                                    </div>
                                    <div class="text-secondary small">
                                        {{ $sub->next_billing_date->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="py-3" data-label="Renovação">
                                @if($sub->auto_renew)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill" title="Cobrança automática ligada">
                                        <i class="bi bi-arrow-repeat"></i><span>Auto</span>
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill" title="Cobrança automática desligada">
                                        <i class="bi bi-slash-circle"></i><span>Manual</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3" data-label="Status">
                                @if($sub->status === 'active')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1 rounded-pill">
                                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i><span>Ativo</span>
                                    </span>
                                @elseif($sub->status === 'paused')
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 rounded-pill">
                                        <i class="bi bi-pause-fill"></i><span>Pausado</span>
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-50 px-2 py-1 rounded-pill">
                                        <i class="bi bi-x-circle"></i><span>Cancelado</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 pe-4 text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-circle" wire:click="openEditModal('{{ $sub->id }}')" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-circle ms-1" wire:click="confirmDelete('{{ $sub->id }}')" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr wire:key="empty-subscriptions">
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-body-tertiary border rounded-circle" style="width: 80px; height: 80px;">
                                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                                    </div>
                                </div>
                                <h5 class="fw-semibold text-white">Nenhuma assinatura encontrada</h5>
                                <p class="mb-4">Você ainda não registrou nenhuma assinatura.</p>
                                <button class="btn btn-primary fw-bold px-4 rounded-pill" wire:click="openCreateModal">
                                    Adicionar a Primeira
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($totalPages > 1)
            <div class="card-footer bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
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

    <!-- Form Modal -->
    @if($showFormModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.7); z-index: 1050; backdrop-filter: blur(4px);">
            <div class="card shadow-lg" style="width: min(700px, 95vw); max-height: 90vh; overflow-y: auto;">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">{{ $editingId ? 'Editar Assinatura' : 'Nova Assinatura' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeFormModal"></button>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="save">
                        <div class="row g-4">
                            <!-- Header Toggle: Removido conforme solicitação -->

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nome da Assinatura</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" placeholder="Ex: Netflix Premium" list="service-templates" maxlength="255">
                                <datalist id="service-templates">
                                    <option value="Netflix">
                                    <option value="Spotify">
                                    <option value="Amazon Prime">
                                    <option value="YouTube Premium">
                                    <option value="Disney+">
                                    <option value="Max (HBO)">
                                    <option value="Globoplay">
                                    <option value="Apple TV">
                                    <option value="Apple Music">
                                    <option value="ChatGPT">
                                    <option value="GitHub Copilot">
                                </datalist>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">URL do Serviço</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary"><i class="bi bi-link-45deg"></i></span>
                                    <input type="url" class="form-control @error('service_url') is-invalid @enderror" wire:model.blur="service_url" placeholder="https://www.netflix.com">
                                </div>
                                @error('service_url') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    Categoria
                                    <a href="#" class="text-primary small text-decoration-none" wire:click.prevent="$toggle('isCreatingCategory')">
                                        {{ $isCreatingCategory ? 'Selecionar existente' : '+ Nova Categoria' }}
                                    </a>
                                </label>
                                @if($isCreatingCategory)
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control @error('newCategoryName') is-invalid @enderror" wire:model="newCategoryName" placeholder="Ex: Academia" maxlength="50">
                                        </div>
                                        <div>
                                            <input type="color" class="form-control form-control-color p-1" wire:model="newCategoryColor" title="Cor da categoria" style="width: 42px; height: 38px;">
                                        </div>
                                    </div>
                                    @error('newCategoryName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select class="form-select @error('category_id') is-invalid @enderror" wire:model.live="category_id">
                                                <option value="">Selecione uma categoria...</option>
                                                @foreach($categories as $category)
                                                    <option wire:key="category-{{ $category->id }}" value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div x-show="$wire.category_id">
                                            <input type="color" class="form-control form-control-color p-1" wire:model="selectedCategoryColor" title="Alterar cor desta categoria" style="width: 42px; height: 38px;">
                                        </div>
                                    </div>
                                    @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    @error('selectedCategoryColor') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Valor e Moeda</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 90px;" wire:model="currency">
                                        <option value="BRL">R$</option>
                                        <option value="USD">US$</option>
                                        <option value="EUR">€</option>
                                    </select>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" wire:model="amount" placeholder="44.90">
                                </div>
                                @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ciclo de Cobrança</label>
                                <select class="form-select @error('billing_cycle') is-invalid @enderror" wire:model.live="billing_cycle">
                                    <option value="monthly">Mensal</option>
                                    <option value="quarterly">Trimestral</option>
                                    <option value="semiannual">Semestral</option>
                                    <option value="yearly">Anual</option>
                                    <option value="custom">Personalizado...</option>
                                </select>
                                @error('billing_cycle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($billing_cycle === 'custom')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Repetir a cada</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('custom_cycle_interval') is-invalid @enderror" wire:model="custom_cycle_interval" placeholder="Ex: 10">
                                        <select class="form-select" wire:model="custom_cycle_period">
                                            <option value="days">Dias</option>
                                            <option value="months">Meses</option>
                                            <option value="years">Anos</option>
                                        </select>
                                    </div>
                                    @error('custom_cycle_interval') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Data de Início</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" wire:model="start_date">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Próximo Vencimento</label>
                                <input type="date" class="form-control @error('next_billing_date') is-invalid @enderror" wire:model="next_billing_date">
                                @error('next_billing_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" wire:model.live="status">
                                    <option value="active">Ativo</option>
                                    <option value="paused">Pausado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($status === 'cancelled')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Data de Cancelamento</label>
                                    <input type="date" class="form-control @error('cancelled_at') is-invalid @enderror" wire:model="cancelled_at">
                                    @error('cancelled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Renovação Automática</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="auto_renew" wire:model="auto_renew" style="width: 2.5em; height: 1.25em;">
                                    <label class="form-check-label ms-2" for="auto_renew">Renovar automaticamente</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Anotações / Notas</label>
                                <textarea class="form-control" wire:model="notes" rows="2" placeholder="Informações adicionais..." maxlength="1000"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="closeFormModal" wire:loading.attr="disabled">
                                <span>Cancelar</span>
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" wire:loading.attr="disabled">
                                <span wire:loading class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i wire:loading.remove class="bi bi-save"></i>
                                <span wire:loading.remove>Salvar</span>
                                <span wire:loading>Salvando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.7); z-index: 1060; backdrop-filter: blur(4px);">
            <div class="card shadow-lg" style="width: min(500px, 92vw);">
                <div class="card-header border-danger py-3">
                    <h5 class="mb-0 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Exclusão</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-1">Você está prestes a excluir permanentemente a assinatura:</p>
                    <h4 class="fw-bold mb-3">{{ $deletingName }}</h4>
                    <p class="mb-0 text-secondary small">Esta ação não poderá ser desfeita. Tem certeza que deseja prosseguir?</p>
                </div>
                <div class="card-footer border-danger d-flex justify-content-end gap-2 py-3">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" wire:click="cancelDelete">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" wire:click="deleteSubscription">
                        <i class="bi bi-trash-fill me-1"></i> Excluir Permanentemente
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showImportModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.7); z-index: 1060;">
            <div class="card shadow-lg" style="width: min(500px, 92vw);">
                <div class="card-header border-secondary">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Confirmar Importação</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary mb-4">Analisamos seu arquivo e encontramos o seguinte:</p>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total de registros:</span>
                        <span class="fw-bold text-white">{{ $importSummary['total'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Assinaturas novas:</span>
                        <span class="fw-bold text-success">{{ $importSummary['new'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span>Duplicatas detectadas:</span>
                        <span class="fw-bold text-warning">{{ $importSummary['duplicates'] }}</span>
                    </div>

                    <hr class="border-secondary">

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="ignoreDupModal" wire:model.live="ignoreDuplicates">
                        <label class="form-check-label text-light" for="ignoreDupModal">Ignorar duplicados (não importar o que já existe)</label>
                    </div>
                    <p class="small text-secondary mt-1">Se desmarcado, o sistema criará cópias das assinaturas já existentes.</p>
                </div>
                <div class="card-footer border-secondary d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cancelImport">Cancelar</button>
                    <button type="button" class="btn btn-success px-4" wire:click="confirmImport">Importar Agora</button>
                </div>
            </div>
        </div>
    @endif
</div>
