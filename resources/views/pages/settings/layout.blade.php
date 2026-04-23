<x-layouts::app :title="$heading ?? 'Configurações'">
    <div class="row">
        <!-- Menu lateral de navegação das configurações -->
        <div class="col-12 col-md-3 mb-4">
            <div class="card shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-3">
                    <h6 class="text-uppercase text-secondary small fw-bold mb-3 px-2">Configurações</h6>
                    <nav class="nav flex-column gap-1">
                        <a href="{{ route('profile.edit') }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs('profile.edit') ? 'bg-primary text-white' : 'text-body' }}" wire:navigate>
                            <i class="bi bi-person me-2"></i>Perfil
                        </a>
                        <a href="{{ route('password.edit') }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs('password.edit') ? 'bg-primary text-white' : 'text-body' }}" wire:navigate>
                            <i class="bi bi-key me-2"></i>Alterar Senha
                        </a>
                        <a href="{{ route('two-factor.edit') }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs('two-factor.edit') ? 'bg-primary text-white' : 'text-body' }}" wire:navigate>
                            <i class="bi bi-shield-lock me-2"></i>Ativar 2FA
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Conteúdo da configuração -->
        <div class="col-12 col-md-9">
            <div class="card shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    @if(isset($heading))
                        <h4 class="fw-bold mb-1">{{ $heading }}</h4>
                    @endif
                    @if(isset($subheading))
                        <p class="text-secondary small mb-4">{{ $subheading }}</p>
                    @endif
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
