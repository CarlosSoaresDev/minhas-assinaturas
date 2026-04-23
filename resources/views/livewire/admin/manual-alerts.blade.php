<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Disparo Manual de Alertas</h2>
            <p class="text-secondary mb-0">Varra o sistema em busca de vencimentos e envie notificações controladas.</p>
        </div>
    </div>

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

    {{-- Filtros de Varredura --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #111827;">
        <div class="card-body p-4">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">PERÍODO DE VENCIMENTO</label>
                    <select wire:model="timeframe" class="form-select border-secondary bg-dark text-white" style="border-radius: 10px;">
                        <option value="1">Vencendo amanhã (24h)</option>
                        <option value="3">Próximos 3 dias</option>
                        <option value="7">Próxima semana (7 dias)</option>
                        <option value="15">Próximos 15 dias</option>
                        <option value="30">Próximo mês (30 dias)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="scan" class="btn btn-primary w-100 fw-bold py-2" style="border-radius: 10px;">
                        <i class="bi bi-search me-2"></i>Varrer Sistema
                    </button>
                </div>
                @if($hasScanned && $subscriptions->count() > 0)
                    <div class="col-md-3">
                        <button wire:click="sendSelected" wire:confirm="Deseja disparar as notificações para os itens selecionados?" class="btn btn-success w-100 fw-bold py-2" style="border-radius: 10px;">
                            <i class="bi bi-send-fill me-2"></i>Enviar Selecionados
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($hasScanned)
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-dark bg-opacity-50">
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-4 py-3" style="width: 50px;">
                                    {{-- Checkbox Select All (opcional) --}}
                                </th>
                                <th class="py-3">Serviço / Item</th>
                                <th class="py-3">Categoria</th>
                                <th class="py-3">Data Vencimento</th>
                                <th class="py-3">Valor Estimado</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $sub)
                                <tr>
                                    <td class="ps-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $sub->id }}" wire:model="selectedSubscriptions">
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-bold text-white">{{ $sub->name }}</span>
                                        <br>
                                        {{-- ID Ocultado --}}
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill">
                                            <i class="bi {{ str_starts_with($sub->category?->icon ?? 'tag', 'bi-') ? $sub->category->icon : 'bi-' . ($sub->category->icon ?? 'tag') }} me-1"></i>
                                            {{ $sub->category?->name ?? 'Geral' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-white">
                                        {{ $sub->next_billing_date->format('d/m/Y') }}
                                        <br>
                                        @php
                                            $diff = now()->startOfDay()->diffInDays($sub->next_billing_date->startOfDay(), false);
                                        @endphp
                                        <small class="{{ $diff <= 1 ? 'text-danger' : 'text-warning' }} small">
                                            {{ $diff == 0 ? 'Vence HOJE' : "Em {$diff} dia(s)" }}
                                        </small>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-white">
                                            {{ $sub->currency ?? 'BRL' }} {{ number_format($sub->amount, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Ativa</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-secondary">
                                            <i class="bi bi-calendar-check fs-1 opacity-25 d-block mb-3"></i>
                                            Nenhum item encontrado para o período de <strong>{{ $timeframe }} dias</strong>.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($subscriptions->hasPages())
                <div class="card-footer bg-transparent border-0 py-3">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>
        
        <div class="mt-4 p-3 bg-dark bg-opacity-25 rounded-3 border border-secondary border-opacity-10">
            <p class="text-secondary small mb-0">
                <i class="bi bi-shield-lock-fill me-2 text-primary"></i>
                <strong>Privacidade:</strong> O sistema oculta a identidade dos usuários nesta visualização. 
                Os disparos são processados internamente vinculando o item ao respectivo token de privacidade.
            </p>
        </div>
    @endif
</div>
