<x-layouts::app title="Confirmar Senha">
    <div class="row">
        <!-- Menu lateral (itens na esquerda) -->
        <div class="col-12 col-md-3 mb-4">
            @include('pages.settings.includes.menu')
        </div>

        <!-- Conteúdo central -->
        <div class="col-12 col-md-9">
            <div class="card shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-center py-4">
                        <div class="col-12 col-xl-8">
                            <div class="card shadow-lg border-secondary bg-dark bg-opacity-50" style="border-radius: 20px;">
                                <div class="card-body p-4 p-md-5 text-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px;">
                                        <i class="bi bi-shield-lock text-primary" style="font-size: 1.8rem;"></i>
                                    </div>
                                    
                                    <h5 class="text-white fw-bold mb-2">Área Restrita</h5>
                                    <p class="text-secondary small mb-4">Por segurança, confirme sua senha para continuar.</p>

                                    <form method="POST" action="{{ route('password.confirm.store') }}">
                                        @csrf
                                        <div class="mb-4 text-start">
                                            <label for="password" class="form-label text-light fw-medium">Senha</label>
                                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                                class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                                                style="border-radius: 12px; padding: 12px 16px;">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary fw-bold py-2" style="border-radius: 50px;">
                                                <i class="bi bi-check-circle me-2"></i>Confirmar Senha
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
