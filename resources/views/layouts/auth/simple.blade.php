<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        @include('partials.head')
        <style>
            body {
                overflow-x: hidden;
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
            .auth-card-glass {
                background: rgba(17, 24, 39, 0.7) !important;
                backdrop-filter: blur(12px) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            }
        </style>
    </head>
    <body class="bg-dark text-white min-vh-100">
        <canvas id="auth-canvas"></canvas>
        @yield('content')

        <script>
            const canvas = document.getElementById('auth-canvas');
            const ctx = canvas.getContext('2d');
            let particles = [];

            function init() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                particles = [];
                for (let i = 0; i < 70; i++) {
                    particles.push({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height,
                        size: Math.random() * 2 + 1,
                        speedX: Math.random() * 0.4 - 0.2,
                        speedY: Math.random() * 0.4 - 0.2,
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
                    ctx.globalAlpha = 0.25;
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
