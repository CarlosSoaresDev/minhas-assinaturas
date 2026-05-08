<div x-data="{ showDelete: false }">
    <div class="row g-4">
        <!-- Exportação de Dados (LGPD) -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="bi bi-file-earmark-arrow-down me-2"></i>Exportar Meus Dados</h6>
                        <p class="text-secondary small mb-0">Baixe uma cópia de todas as suas informações e assinaturas (Formato JSON).</p>
                    </div>
                    <button wire:click="downloadLgpdData" class="btn btn-outline-primary fw-bold" style="border-radius: 10px;">
                        <i class="bi bi-download me-2"></i>Baixar Dados
                    </button>
                </div>
            </div>
        </div>

        <!-- Exclusão de Conta -->
        <div class="col-md-12">
            @if(session()->has('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                </div>
            @endif

            <livewire:settings.delete-user-form />
        </div>
    </div>
</div>
