<div>
    @if (session('profile_success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('profile_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('profile_error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('profile_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="updateProfileInformation">
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome</label>
            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" required autofocus style="border-radius: 10px; padding: 10px 15px;">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input type="email" class="form-control" id="email" value="{{ auth()->user()->email }}" disabled style="border-radius: 10px; padding: 10px 15px;">
            <small class="text-muted">Você não pode alterar o seu e-mail.</small>
        </div>

        <hr class="border-secondary my-4">

        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-bell me-2"></i>Preferências de Alerta</h5>
        
        <div class="mb-3">
            <div class="form-check form-switch mb-2">
                <input wire:model="alerts_enabled" class="form-check-input" type="checkbox" role="switch" id="alerts_enabled">
                <label class="form-check-label fw-semibold" for="alerts_enabled">Receber alertas de vencimento</label>
            </div>
            <p class="text-muted small">Se desativado, você não receberá e-mails ou notificações in-app sobre assinaturas próximas ao vencimento.</p>
        </div>

        <div class="mb-3" @if(!$alerts_enabled) style="opacity: 0.5; pointer-events: none;" @endif>
            <label for="alert_days_before" class="form-label fw-semibold">Dias de antecedência para o alerta</label>
            <div class="input-group" style="max-width: 200px;">
                <input wire:model="alert_days_before" type="number" class="form-control @error('alert_days_before') is-invalid @enderror" id="alert_days_before" min="1" max="60" style="border-radius: 10px 0 0 10px; padding: 10px 15px;">
                <span class="input-group-text bg-dark border-secondary text-secondary" style="border-radius: 0 10px 10px 0;">dias</span>
            </div>
            @error('alert_days_before')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted">Padrão: 7 dias antes da renovação.</small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 50px;">
                <i class="bi bi-check-circle me-1"></i> Salvar Alterações
            </button>
        </div>
    </form>

    <hr class="border-secondary my-5">

    <div class="mt-4">
        <h4 class="fw-bold mb-3"><i class="bi bi-database me-2"></i>Gestão de Dados</h4>
        <livewire:settings.data-management />
    </div>
</div>
