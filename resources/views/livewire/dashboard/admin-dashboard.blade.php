<div wire:poll.10s="loadMetrics">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Painel Administrativo</h2>
            <p class="text-secondary mb-0">Visão geral da plataforma — dados anonimizados</p>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-white-50 small mb-1 fw-semibold text-uppercase">Usuários</p>
                            <h3 class="fw-bold text-white mb-0">{{ $totalUsers }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-people-fill text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-white-50 small mb-1 fw-semibold text-uppercase">Total de Serviços</p>
                            <h3 class="fw-bold text-white mb-0">{{ $totalServices }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-credit-card-fill text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-white-50 small mb-1 fw-semibold text-uppercase">Sessões Ativas</p>
                            <h3 class="fw-bold text-white mb-0">{{ $onlineUsers }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.1);">
                            <i class="bi bi-broadcast text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-white-50 small mb-1 fw-semibold text-uppercase">Saúde do Sistema</p>
                            <h3 class="fw-bold text-white mb-0">{{ $systemHealth }}%</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.1);">
                            <i class="bi bi-shield-check text-white fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Novas métricas: Categorias Populares e Últimos Usuários -->
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill text-info me-3"></i>Distribuição de Categorias</h5>
                </div>
                <div class="card-body">
                    @forelse($popularCategories as $cat)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-3 d-flex justify-content-center align-items-center" style="width: 35px; height: 35px; background-color: {{ $cat['color'] }}20; border: 1px solid {{ $cat['color'] }}40;">
                                    <i class="bi {{ str_starts_with($cat['icon'], 'bi-') ? $cat['icon'] : 'bi-' . $cat['icon'] }}" style="color: {{ $cat['color'] }};"></i>
                                </div>
                                <span class="fw-semibold">{{ $cat['name'] }}</span>
                            </div>
                            <span class="badge bg-secondary rounded-pill">{{ $cat['count'] }} assinaturas</span>
                        </div>
                    @empty
                        <p class="text-secondary text-center my-4">Nenhuma categoria em uso ainda.</p>
                    @endforelse
                </div>
            </div>
        </div>

    <!-- Últimos Usuários -->
        <div class="col-lg-7">
            <div class="card shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill text-primary me-3"></i>Últimos Usuários Cadastrados</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="text-secondary small text-uppercase">
                                    <th class="py-3 ps-4">Nome</th>
                                    <th class="py-3">E-mail</th>
                                    <th class="py-3">Cadastro</th>
                                    <th class="py-3 text-center">Tem Itens?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $u)
                                    <tr>
                                        <td class="py-3 ps-4 fw-semibold">{{ $u['name'] }}</td>
                                        <td class="py-3 text-secondary">{{ $u['email'] }}</td>
                                        <td class="py-3 text-secondary">{{ $u['created_at'] }}</td>
                                        <td class="py-3 text-center">
                                            @if($u['has_subscriptions'])
                                                <span class="badge bg-success bg-opacity-25 text-success">Sim</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary">Não</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">Nenhum usuário cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <div class="mt-3">
        <p class="text-secondary small mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Os dados individuais dos usuários são protegidos por Privacy Tokens. 
            A coluna "Tem Itens?" mostra apenas se existem registros, sem revelar conteúdo.
        </p>
    </div>
</div>
