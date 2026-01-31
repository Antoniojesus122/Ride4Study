<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Ride4Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="public/js/tailwind-config.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full text-white flex flex-col">

    <!-- Barra de navegación -->
    <nav class="absolute w-full z-10 px-6 py-6 flex justify-between items-center max-w-7xl mx-auto left-0 right-0">
        <div class="flex items-center gap-2">
            <a href="index.php" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-secondary font-bold text-xl shadow-lg shadow-primary/20">R</div>
                <span class="font-bold text-2xl tracking-tight text-white">Ride4Study</span>
            </a>
        </div>
        <div class="flex gap-4">
            <a href="login.php" class="text-white hover:text-primary font-medium px-4 py-2 transition-colors">Entrar</a>
            <a href="register.php" class="bg-white text-secondary hover:bg-gray-200 font-bold px-6 py-2 rounded-full transition-all transform hover:scale-105">Registrarse</a>
        </div>
    </nav>

    <!-- Encabezado -->
    <header class="pt-32 pb-16 bg-gradient-to-b from-gray-900 via-gray-900 to-surface">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Términos y Condiciones</h1>
            <p class="text-xl text-gray-400">Las reglas del juego para una comunidad segura.</p>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="flex-grow bg-surface">
        <div class="mx-auto max-w-4xl px-6 py-12">
             <section class="bg-gray-900/50 p-8 rounded-3xl border border-white/5">
                <div class="prose prose-invert prose-indigo max-w-none text-gray-400">
                    <p>Al utilizar Ride4Study, aceptas cumplir con estos términos y condiciones. Por favor, léelos detenidamente.</p>
                        <h3 class="text-white font-bold mt-6 mb-2">1. Uso Acceptable</h3>
                    <p>Te comprometes a utilizar nuestra plataforma solo para fines legales y de acuerdo con estos términos. No debes usar el servicio para enviar spam, acosar a otros usuarios o realizar actividades fraudulentas.</p>
                        <h3 class="text-white font-bold mt-6 mb-2">2. Cuentas de Usuario</h3>
                    <p>Eres responsable de mantener la confidencialidad de tu cuenta y contraseña. Debes notificarnos inmediatamente cualquier uso no autorizado de tu cuenta.</p>
                        <h3 class="text-white font-bold mt-6 mb-2">3. Cancelaciones y Reembolsos</h3>
                    <p>Las políticas de cancelación son establecidas por los conductores. Ride4Study no se hace responsable de las disputas entre conductores y pasajeros, aunque facilitaremos la mediación.</p>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black py-12 border-t border-white/5">
        <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2 opacity-50 hover:opacity-100 transition-opacity">
                <div class="w-6 h-6 bg-white rounded-md flex items-center justify-center text-black font-bold text-xs">R</div>
                <span class="font-bold tracking-tight text-white">Ride4Study</span>
            </div>
            <div class="flex gap-8 text-sm text-gray-400">
                <a href="privacy.php" class="hover:text-primary transition-colors">Privacidad</a>
                <a href="terms.php" class="text-primary font-bold">Condiciones</a>
                <a href="support.php" class="hover:text-primary transition-colors">Soporte</a>
            </div>
            <p class="text-xs text-gray-600">© 2025 Ride4Study. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>