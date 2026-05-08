<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Gerenciador de Assinaturas') : config('app.name', 'Gerenciador de Assinaturas') }}
</title>

<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

@vite(['resources/css/app.scss', 'resources/js/app.js'])

