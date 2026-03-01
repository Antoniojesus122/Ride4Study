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
            <nav class="absolute w-full z-10 px-4 sm:px-6 py-4 sm:py-6 flex justify-between items-center max-w-7xl mx-auto left-0 right-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-xl flex items-center justify-center text-secondary font-bold text-lg sm:text-xl shadow-lg shadow-primary/20">
                        R
                    </div>
                    <span class="font-bold text-lg sm:text-2xl tracking-tight">
                        Ride4Study
                    </span>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="login.php" class="text-xs sm:text-sm md:text-base text-white hover:text-primary font-medium px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 transition-colors whitespace-nowrap">
                        Entrar
                    </a>
                    <a href="register.php" class="bg-white text-secondary hover:bg-gray-200 text-xs sm:text-sm md:text-base font-bold px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-full transition-all duration-200 transform hover:scale-105 whitespace-nowrap">
                        Registrarse
                    </a>
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
            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
        </body>
    </html>