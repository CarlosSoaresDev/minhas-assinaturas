<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Minhas Assinaturas') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        body {
            overflow: hidden;
            background-color: #0b0e14 !important;
        }
        #auth-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .auth-card-container {
            z-index: 10;
            backdrop-filter: blur(8px);
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-body-tertiary">
    <canvas id="auth-canvas"></canvas>

    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3.5rem; filter: drop-shadow(0 0 15px rgba(13, 110, 253, 0.5));"></i>
                <h2 class="mt-3 fw-bold text-white">Minhas Assinaturas</h2>
                <p class="text-secondary small">Gestão inteligente de serviços recorrentes</p>
            </div>
            
            <div class="card shadow-lg border-0 auth-card-container" style="border-radius: 20px;">
                <div class="card-body p-4 p-md-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('auth-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function init() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            particles = [];
            for (let i = 0; i < 80; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * 2 + 1,
                    speedX: Math.random() * 0.5 - 0.25,
                    speedY: Math.random() * 0.5 - 0.25,
                    color: Math.random() > 0.5 ? '#10b981' : '#0d6efd'
                });
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#0b0e14';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            particles.forEach(p => {
                p.x += p.speedX;
                p.y += p.speedY;

                if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
                if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = 0.3;
                ctx.fill();
            });

            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', init);
        init();
        animate();
    </script>
</body>
</html>
