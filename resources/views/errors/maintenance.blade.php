<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema em Preparação - Minhas Assinaturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #38bdf8;
        }
        h1 { font-weight: 700; margin-bottom: 1rem; }
        p { color: #94a3b8; line-height: 1.6; }
        .btn-primary {
            background-color: #38bdf8;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        .btn-primary:hover { background-color: #0ea5e9; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚙️</div>
        <h1>Estamos quase lá!</h1>
        <p>{{ $message ?? 'O sistema está passando por uma atualização técnica e estará disponível em instantes.' }}</p>
        <p class="small mt-3">Se você for o administrador, certifique-se de que o banco de dados foi inicializado corretamente.</p>
        <button onclick="window.location.reload()" class="btn btn-primary">Tentar Novamente</button>
    </div>
</body>
</html>
