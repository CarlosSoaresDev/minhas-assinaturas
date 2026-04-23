<div class="container-fluid py-4" x-data="{ open: false }" 
     x-on:open-category-modal.window="new bootstrap.Modal(document.getElementById('category-modal')).show()" 
     x-on:close-category-modal.window="bootstrap.Modal.getInstance(document.getElementById('category-modal')).hide()">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Categorias do Sistema</h2>
            <p class="text-secondary mb-0">Gerencie as categorias padrões disponíveis para todos os usuários.</p>
        </div>
        <button wire:click="create" class="btn btn-primary fw-bold px-4 shadow-sm" style="border-radius: 50px;">
            <i class="bi bi-plus-lg me-2"></i>Nova Categoria
        </button>
    </div>

    {{-- Alertas --}}
    @if (session()->has('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header bg-transparent py-3 border-0">
            <div class="input-group shadow-sm" style="max-width: 350px;">
                <span class="input-group-text text-secondary border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0" placeholder="Buscar categoria...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 responsive-table">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th class="py-3 ps-4" style="width: 80px;">Ícone</th>
                            <th class="py-3">Nome da Categoria</th>
                            <th class="py-3">Slug</th>
                            <th class="py-3 text-center">Cor</th>
                            <th class="py-3 text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr wire:key="cat-{{ $category->id }}">
                                <td class="ps-4 py-3" data-label="Ícone">
                                    <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 rounded-circle" style="width: 40px; height: 40px; border: 1px solid {{ $category->color }}33;">
                                        <i class="bi {{ str_starts_with($category->icon, 'bi-') ? $category->icon : 'bi-' . $category->icon }}" style="color: {{ $category->color }}; font-size: 1.2rem;"></i>
                                    </div>
                                </td>
                                <td class="fw-medium py-3" data-label="Categoria">{{ $category->name }}</td>
                                <td class="text-secondary small py-3" data-label="Slug">/{{ $category->slug }}</td>
                                <td class="text-center py-3" data-label="Cor">
                                    <span class="badge rounded-pill" style="background-color: {{ $category->color }}22; color: {{ $category->color }}; border: 1px solid {{ $category->color }}44;">
                                        {{ $category->color }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <button wire:click="edit({{ $category->id }})" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})" class="btn btn-sm btn-outline-danger rounded-circle ms-1" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-secondary">Nenhuma categoria encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($totalPages > 1)
            <div class="card-footer bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <div class="text-secondary small">
                    Mostrando {{ count($categories) }} de {{ $totalRecords }} categorias
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

    {{-- Modal Form --}}
    <div wire:ignore.self class="modal fade" id="category-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">{{ $isEditing ? 'Editar Categoria' : 'Nova Categoria' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome da Categoria</label>
                            <input wire:model="name" type="text" class="form-control" placeholder="Ex: Streaming">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-8 mb-3">
                                <label class="form-label fw-semibold">Ícone (Bootstrap Icons)</label>
                                <input wire:model="icon" type="text" class="form-control" placeholder="ex: tv">
                                <div class="form-text small">Use nomes como: tv, wallet, controller, music-note.</div>
                                @error('icon') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label fw-semibold">Cor</label>
                                <input wire:model="color" type="color" class="form-control form-control-color w-100 shadow-sm" style="height: 38px;">
                                @error('color') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salvar Categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Delete Confirmation --}}
    @if($showDeleteModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.7); z-index: 1060; backdrop-filter: blur(4px);">
            <div class="card shadow-lg" style="width: min(500px, 92vw); border-radius: 16px;">
                <div class="card-header border-0 pt-4 px-4 bg-transparent">
                    <h5 class="mb-0 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Exclusão</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-1">Você está prestes a excluir permanentemente a categoria:</p>
                    <h4 class="fw-bold mb-3">{{ $deletingName }}</h4>
                    <p class="mb-0 text-secondary small">Esta ação não poderá ser desfeita. Categorias com assinaturas vinculadas não podem ser excluídas.</p>
                </div>
                <div class="card-footer border-0 d-flex justify-content-end gap-2 pb-4 px-4 bg-transparent">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="cancelDelete">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" wire:click="delete">
                        <i class="bi bi-trash-fill me-1"></i> Excluir Categoria
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
