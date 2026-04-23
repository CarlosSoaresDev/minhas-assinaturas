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

        <!-- Exclusão de Conta Collapsible -->
        <div class="col-md-12">
            @if(session()->has('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                </div>
            @endif

            <button @click="showDelete = !showDelete" class="btn btn-outline-danger fw-bold shadow-sm d-flex justify-content-between align-items-center w-100" style="border-radius: 12px; padding: 12px 20px;">
                <span><i class="bi bi-exclamation-triangle-fill me-2"></i> Excluir Minha Conta Permanentemente</span>
                <i class="bi" :class="showDelete ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>

            <div x-show="showDelete" x-collapse x-cloak class="mt-3">
                <div class="card border-danger shadow-sm">
                    <div class="card-header border-danger text-danger py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cone-striped me-2"></i>Zona de Risco</h6>
                    </div>
                    <div class="card-body">
                        <p>Ao excluir sua conta, todas as suas assinaturas, tokens de privacidade e informações serão deletadas permanentemente. Esta ação não poderá ser desfeita.</p>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input border-secondary" type="checkbox" id="confirmDeleteAccount" x-data="{ checked: false }" x-model="checked" @change="$dispatch('toggle-delete-btn', checked)">
                            <label class="form-check-label text-secondary small" for="confirmDeleteAccount">
                                Eu entendo que esta ação é irreversível e desejo excluir minha conta permanentemente.
                            </label>
                        </div>

                        <button wire:click="requestAccountDeletion" wire:confirm="VOCÊ TEM CERTEZA? Esta ação deletará sua conta imediatamente." class="btn btn-danger fw-bold" x-data="{ disabled: true }" @toggle-delete-btn.window="disabled = !$event.detail" x-bind:disabled="disabled">
                            <i class="bi bi-trash-fill me-2"></i>Confirmar Exclusão
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
