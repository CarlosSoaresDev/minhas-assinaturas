<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Erro Interno do Servidor</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-dark text-white" style="min-height:100vh; background: radial-gradient(circle at 20% 15%, rgba(255, 0, 0, 0.1), transparent 35%), radial-gradient(circle at 70% 20%, rgba(255, 100, 0, 0.15), transparent 40%), #11131a;">
    <main class="container py-5 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <section class="text-center" style="max-width: 540px;">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
            </div>
            <h1 class="display-1 fw-bold mb-3" style="background: linear-gradient(135deg, #ff416c, #ff4b2b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">500</h1>
            <h2 class="display-5 fw-bold mb-4">Ops! Algo deu errado</h2>
            <p class="text-secondary mb-5">Ocorreu um erro inesperado em nossos servidores. Nossa equipe técnica já foi notificada e está trabalhando para resolver isso o mais rápido possível.</p>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary px-5 py-3 fw-bold shadow-lg" style="border-radius: 50px; background: linear-gradient(135deg, #7062f4, #5c4df2); border: none; transition: transform 0.2s;">Voltar para o Dashboard</a>
            @else
                <a href="{{ route('home') }}" class="btn btn-primary px-5 py-3 fw-bold shadow-lg" style="border-radius: 50px; background: linear-gradient(135deg, #7062f4, #5c4df2); border: none; transition: transform 0.2s;">Voltar para a Home</a>
            @endauth
        </section>
    </main>
</body>
</html>
