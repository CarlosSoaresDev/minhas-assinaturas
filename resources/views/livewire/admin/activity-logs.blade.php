<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Logs do Sistema</h2>
            <p class="text-secondary mb-0">Rastro de atividades e auditoria da plataforma</p>
        </div>
    </div>

    <style>
        .pagination svg { width: 16px !important; height: 16px !important; }
        .page-link { padding: 0.5rem 0.75rem !important; }
    </style>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <div class="input-group" style="max-width: 350px;">
                <span class="input-group-text text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Buscar nos logs...">
            </div>
            <div>
                <select wire:model.live="perPage" class="form-select shadow-none">
                    <option value="15">15 por página</option>
                    <option value="30">30 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 responsive-table">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th class="py-3 ps-4" style="width: 140px;">Data/Hora</th>
                            <th class="py-3" style="width: 160px;">Usuário</th>
                            <th class="py-3" style="width: 180px;">Módulo / Tipo</th>
                            <th class="py-3">Atividade</th>
                            <th class="py-3" style="width: 120px;">IP</th>
                            <th class="py-3" style="width: 140px;">Dispositivo</th>
                            <th class="py-3 pe-4 text-end" style="width: 160px;">Navegador</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="py-4 ps-4">
                                    @php
                                        $isSubscription = str_contains($log->subject_type, 'Subscription');
                                        $displayTime = $isSubscription ? $log->created_at->format('H') . 'h (Oculto)' : $log->created_at->format('H:i:s');
                                    @endphp
                                    <div class="text-white fw-bold" style="font-size: 1.05rem;">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="text-info opacity-75 fw-medium" style="font-size: 0.85rem;">{{ $displayTime }}</div>
                                </td>
                                <td class="py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: {{ $log->causer ? '#10b981' : '#94a3b8' }}; box-shadow: 0 0 8px {{ $log->causer ? '#10b98166' : '#94a3b866' }};"></div>
                                        <span class="text-white fw-bold" style="font-size: 1rem;">{{ $log->causer ? $log->causer->name : 'Sistema' }}</span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @php
                                        $logType = match($log->log_name) {
                                            'auth' => ['text' => 'AUTENTICAÇÃO', 'class' => 'primary'],
                                            'security' => ['text' => 'SEGURANÇA', 'class' => 'warning'],
                                            default => ['text' => 'SISTEMA', 'class' => 'secondary']
                                        };
                                        $moduleTranslations = [
                                            'Subscription' => 'Assinatura',
                                            'User' => 'Usuário',
                                            'Category' => 'Categoria'
                                        ];
                                        $moduleName = $log->subject_type ? class_basename($log->subject_type) : 'Sistema';
                                        $translatedModule = $moduleTranslations[$moduleName] ?? $moduleName;
                                        $color = $logType['class'];
                                    @endphp
                                    <div class="d-flex flex-column gap-1">
                                        <span class="text-white fw-bold" style="font-size: 1rem;">{{ $translatedModule }}</span>
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-50" style="font-size: 0.7rem; width: fit-content; letter-spacing: 0.5px;">
                                            {{ $logType['text'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @php
                                        $eventLabel = match($log->event) {
                                            'created' => 'CRIADO',
                                            'updated' => 'EDITADO',
                                            'deleted' => 'EXCLUÍDO',
                                            default => null
                                        };
                                        $desc = $log->description;
                                        if($desc === 'created') $desc = 'Novo registro criado';
                                        elseif($desc === 'updated') $desc = 'Registro atualizado';
                                        elseif($desc === 'deleted') $desc = 'Registro removido';
                                    @endphp
                                    <div style="font-size: 0.95rem;">
                                        @if($eventLabel)
                                            <span class="badge bg-white bg-opacity-10 text-secondary border border-white border-opacity-10 me-2" style="font-size: 0.65rem;">{{ $eventLabel }}</span>
                                        @endif
                                        <span class="text-white-50">{{ $desc }}</span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <span class="text-info fw-bold" style="font-size: 1rem;">
                                        {{ $log->properties['ip'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    @php
                                        $ua = $log->properties['user_agent'] ?? '';
                                        $os = 'Desconhecido';
                                        if (str_contains($ua, 'Windows')) $os = 'Windows';
                                        elseif (str_contains($ua, 'Android')) $os = 'Android';
                                        elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
                                        elseif (str_contains($ua, 'Macintosh')) $os = 'MacOS';
                                        elseif (str_contains($ua, 'Linux')) $os = 'Linux';
                                    @endphp
                                    <span class="text-white-50 fw-medium" style="font-size: 0.95rem;">
                                        <i class="bi bi-device-ssd me-1 opacity-50"></i>{{ $os }}
                                    </span>
                                </td>
                                <td class="py-4 pe-4 text-end">
                                    @php
                                        $browser = 'Navegador';
                                        if (str_contains($ua, 'Edg')) $browser = 'Edge';
                                        elseif (str_contains($ua, 'Chrome')) $browser = 'Chrome';
                                        elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
                                        elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
                                        elseif (str_contains($ua, 'Opera') || str_contains($ua, 'OPR')) $browser = 'Opera';
                                    @endphp
                                    <div class="text-secondary fw-medium" style="font-size: 0.95rem;" title="{{ $ua }}">
                                        {{ $browser }} <i class="bi bi-browser-chrome ms-1 opacity-50"></i>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                                    Nenhum log encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($totalPages > 1)
            <div class="card-footer bg-transparent py-3">
                {{-- UI de Paginação Manual (Puro Livewire, sem URL) --}}
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-secondary small">
                        Mostrando {{ ($page - 1) * $perPage + 1 }} a {{ min($page * $perPage, $total) }} de {{ $total }} resultados
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item {{ $page <= 1 ? 'disabled' : '' }}">
                                <button type="button" class="page-link" wire:click="previousPage">« Anterior</button>
                            </li>
                            
                            @for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                                <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                    <button type="button" class="page-link" wire:click="gotoPage({{ $i }})">{{ $i }}</button>
                                </li>
                            @endfor

                            <li class="page-item {{ $page >= $totalPages ? 'disabled' : '' }}">
                                <button type="button" class="page-link" wire:click="nextPage">Próximo »</button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
