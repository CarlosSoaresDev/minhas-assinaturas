<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Página não encontrada</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-dark text-white" style="min-height:100vh; background: radial-gradient(circle at 20% 15%, rgba(0, 255, 255, 0.12), transparent 35%), radial-gradient(circle at 70% 20%, rgba(255, 180, 0, 0.18), transparent 40%), #11131a;">
    <main class="container py-5 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <section class="text-center" style="max-width: 540px;">
            <h1 class="display-1 fw-bold mb-3" style="background: linear-gradient(135deg, #7062f4, #9187f8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">404</h1>
            <h2 class="display-5 fw-bold mb-4">Página Não Encontrada</h2>
            <p class="text-secondary mb-5">O caminho que você está tentando acessar parece ter se perdido no vácuo espacial ou nunca existiu.</p>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary px-5 py-3 fw-bold shadow-lg" style="border-radius: 50px; background: linear-gradient(135deg, #7062f4, #5c4df2); border: none; transition: transform 0.2s;">Voltar para o Dashboard</a>
            @else
                <a href="{{ route('home') }}" class="btn btn-primary px-5 py-3 fw-bold shadow-lg" style="border-radius: 50px; background: linear-gradient(135deg, #7062f4, #5c4df2); border: none; transition: transform 0.2s;">Voltar para a Home</a>
            @endauth
        </section>
    </main>
</body>
</html>
