<x-layouts::app :title="$heading ?? 'Configurações'">
    <div class="row">
        <!-- Menu lateral de navegação das configurações -->
        <div class="col-12 col-md-3 mb-4">
            @include('pages.settings.includes.menu')
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
