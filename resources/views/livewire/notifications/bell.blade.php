<div class="nav-item dropdown" wire:poll.60000ms="loadNotifications">
    <a class="nav-link text-white position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill fs-5"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65em;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                <span class="visually-hidden">notificações não lidas</span>
            </span>
        @endif
    </a>
    
    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg py-0" style="width: 350px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
        <li><h6 class="dropdown-header text-white bg-dark bg-opacity-50 py-3 rounded-top-2 border-bottom border-secondary mb-0">Central de Alertas</h6></li>
        
        <div class="notification-scroll" style="max-height: 400px; overflow-y: auto;">
            {{-- NÃO LIDAS --}}
            @if(count($unreadNotifications) > 0)
                <li><small class="dropdown-header text-secondary fw-bold bg-dark bg-opacity-25 py-2 uppercase">Não Lidas</small></li>
                @foreach($unreadNotifications as $notification)
                    <li class="border-bottom border-secondary border-opacity-10">
                        <div class="dropdown-item py-3 px-3 position-relative" style="white-space: normal;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-2 flex-grow-1" style="font-size: 0.85rem;">
                                    <div wire:click.prevent="readAndRedirect('{{ $notification->id }}')" class="text-decoration-none d-block cursor-pointer" style="cursor: pointer;">
                                        <span class="fw-bold text-primary">{{ $notification->data['message'] ?? 'Novo Aviso' }}</span>
                                    </div>
                                    
                                    <div class="mt-2 d-flex gap-2">
                                        @if(isset($notification->data['service_url']))
                                            <a href="{{ $notification->data['service_url'] }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.7rem;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Ir para o serviço
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none" style="font-size: 0.7rem;" wire:click="markAsRead('{{ $notification->id }}')">
                                            <i class="bi bi-check2-all me-1"></i>Lido
                                        </button>
                                    </div>

                                    <div class="text-secondary mt-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif

            {{-- LIDAS --}}
            @if(count($readNotifications) > 0)
                <li><small class="dropdown-header text-secondary fw-bold bg-dark bg-opacity-25 py-2 uppercase">Lidas Recentemente</small></li>
                @foreach($readNotifications as $notification)
                    <li class="border-bottom border-secondary border-opacity-10 opacity-75">
                        <div class="dropdown-item py-2 px-3" style="white-space: normal; background-color: rgba(255,255,255,0.02);">
                            <div style="font-size: 0.8rem;">
                                <span class="text-secondary text-decoration-line-through">{{ $notification->data['message'] ?? 'Aviso Lido' }}</span>
                                <div class="text-secondary mt-1" style="font-size: 0.65rem;">
                                    <i class="bi bi-check2 me-1"></i>Lido {{ $notification->read_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif

            @if(count($unreadNotifications) === 0 && count($readNotifications) === 0)
                <li><div class="dropdown-item text-secondary text-center py-4" style="font-size: 0.85rem;">
                    <i class="bi bi-bell-slash d-block fs-2 mb-2 opacity-25"></i>
                    Tudo em dia por aqui.
                </div></li>
            @endif
        </div>

        <li><a class="dropdown-item text-center py-2 border-top border-secondary small text-primary fw-bold" href="{{ route('front.subscriptions.index') }}">Ver Todas as Assinaturas</a></li>
    </ul>
</div>
