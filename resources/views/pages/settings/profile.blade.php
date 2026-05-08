<x-layouts::app :title="'Perfil'">
    <div class="row">
        <div class="col-12 col-md-3 mb-4">
            @include('pages.settings.includes.menu')
        </div>

        <div class="col-12 col-md-9">
            <div class="card shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1">Perfil</h4>
                    <p class="text-secondary small mb-4">Atualize seu nome e e-mail</p>
                    @include('pages.settings.includes.profile-content')
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
