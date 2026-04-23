<div>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Meus Domínios</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" wire:click="$dispatch('openModal', 'domains.create')">
                <i class="bi bi-plus-circle me-1"></i> Novo Domínio
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Domínio</th>
                    <th scope="col">Registrar</th>
                    <th scope="col">Vencimento</th>
                    <th scope="col">Custo Anual</th>
                    <th scope="col">Auto-Renovação</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($domains as $dom)
                    <tr>
                        <td class="fw-bold">{{ $dom->domain_name }}</td>
                        <td>{{ $dom->registrar ?? 'Desconhecido' }}</td>
                        <td>
                            @if($dom->expiration_date)
                                <span class="{{ $dom->expiration_date->diffInDays(now()) < 30 ? 'text-danger fw-bold' : '' }}">
                                    {{ $dom->expiration_date->format('d/m/Y') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>R$ {{ number_format($dom->annual_cost, 2, ',', '.') }}</td>
                        <td>
                            @if($dom->auto_renew)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle text-danger"></i>
                            @endif
                        </td>
                        <td>
                            @if($dom->status === 'active')
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">{{ $dom->status }}</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 bg-body-tertiary text-body-secondary">
                            <i class="bi bi-globe fs-2 d-block mb-2 text-muted"></i>
                            Nenhum domínio cadastrado ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
