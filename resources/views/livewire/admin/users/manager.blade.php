<div>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Usuários</h2>
            <p class="text-secondary mb-0">Gestão completa de contas com visual do sistema.</p>
        </div>
        <button type="button" class="btn btn-primary" wire:click="openCreateModal">
            <i class="bi bi-plus-lg me-1"></i> Adicionar usuário
        </button>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 mb-3">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 mb-3">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm" style="border-radius: 14px;">
        <div class="card-body pb-0">
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Pesquisar por nome ou e-mail..."
                            wire:model.live.debounce.300ms="search"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="all">Todos os status</option>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                        <option value="blocked">Bloqueado</option>
                        <option value="deleted">Excluído</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 responsive-table">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-4 py-3">Nome</th>
                        <th class="py-3">E-mail</th>
                        <th class="py-3">Perfil</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Cadastro</th>
                        <th class="py-3 text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4 py-3 fw-semibold" data-label="Nome">{{ $user->name }}</td>
                            <td class="py-3 text-secondary" data-label="E-mail">{{ $user->email }}</td>
                            <td class="py-3" data-label="Perfil">{{ $user->role_label }}</td>
                            <td class="py-3" data-label="Status">
                                @if($user->trashed())
                                    <span class="badge bg-secondary">Excluído</span>
                                @elseif(isset($user->is_throttled) && $user->is_throttled)
                                    <span class="badge bg-danger shadow-sm d-flex align-items-center gap-1" title="Bloqueado por excesso de tentativas">
                                        <i class="bi bi-shield-exclamation"></i> Throttle: {{ $user->throttle_mins }} min
                                    </span>
                                @elseif($user->status === 'active')
                                    <span class="badge bg-success">Ativo</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-warning text-dark">Inativo</span>
                                @else
                                    <span class="badge bg-danger">Bloqueado</span>
                                @endif
                            </td>
                            <td class="py-3 text-secondary" data-label="Cadastro">{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pe-4 text-end" data-label="Ações">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($user->id === 1 && (int) auth()->id() !== 1)
                                        <span class="badge bg-danger d-flex align-items-center gap-1" title="Administrador Principal">
                                            <i class="bi bi-shield-lock-fill"></i> Protegido
                                        </span>
                                    @else
                                        <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" wire:click="unlockUser({{ $user->id }})" title="Resetar tentativas de login">
                                            <i class="bi bi-unlock-fill"></i> <span class="d-none d-md-inline">Resetar Login</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" wire:click="downloadLgpdReport({{ $user->id }})" title="Gerar Relatório LGPD">
                                            <i class="bi bi-file-earmark-lock"></i>
                                        </button>
                                        @if($user->trashed())
                                            <button class="btn btn-sm btn-outline-light" wire:click="openEditModal({{ $user->id }})">
                                                Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" wire:click="restoreUser({{ $user->id }})">
                                                Restaurar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" wire:click="confirmDeleteUser({{ $user->id }})" title="Excluir Permanentemente">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-light" wire:click="openEditModal({{ $user->id }})">
                                                Editar
                                            </button>
                                            @if((int) auth()->id() !== (int) $user->id)
                                                <button class="btn btn-sm btn-outline-warning" wire:click="softDeleteUser({{ $user->id }})" title="Desativar">
                                                    Desativar
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" wire:click="confirmDeleteUser({{ $user->id }})" title="Excluir Permanentemente">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <span class="badge bg-secondary d-flex align-items-center">Você</span>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-transparent py-3">
            {{ $users->links() }}
        </div>
    </div>

    @if($showFormModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.65); z-index: 1050;">
            <div class="card shadow-lg" style="width: min(640px, 92vw);">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $editing ? 'Editar usuário' : 'Novo usuário' }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeFormModal">Fechar</button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" wire:model.defer="name">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control" wire:model.defer="email">
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha {{ $editing ? '(opcional)' : '' }}</label>
                            <input type="password" class="form-control" wire:model.defer="password">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar senha</label>
                            <input type="password" class="form-control" wire:model.defer="password_confirmation">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perfil</label>
                            <select class="form-select" wire:model.defer="role" @if($this->isEditingSelf()) disabled @endif>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuário</option>
                            </select>
                            @if($this->isEditingSelf())
                                <small class="text-warning"><i class="bi bi-lock me-1"></i>Você não pode alterar seu próprio perfil.</small>
                            @endif
                            @error('role') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" wire:model.defer="status" @if($this->isEditingSelf()) disabled @endif>
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
                                <option value="blocked">Bloqueado</option>
                            </select>
                            @if($this->isEditingSelf())
                                <small class="text-warning"><i class="bi bi-lock me-1"></i>Você não pode alterar seu próprio status.</small>
                            @endif
                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer border-secondary d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeFormModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Salvar</button>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0, 0, 0, 0.65); z-index: 1060;">
            <div class="card shadow-lg" style="width: min(500px, 92vw);">
                <div class="card-header border-danger d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">Confirmar exclusão</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="cancelDelete">Fechar</button>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        Você está prestes a excluir permanentemente o usuário
                        <strong>{{ $deletingUserName }}</strong>.
                    </p>
                    <p class="mb-0 text-danger fw-semibold">
                        Essa ação é irreversível e não poderá ser desfeita.
                    </p>
                </div>
                <div class="card-footer border-danger d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cancelDelete">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteUser({{ (int) $deletingUserId }})">Excluir permanentemente</button>
                </div>
            </div>
        </div>
    @endif
</div>
