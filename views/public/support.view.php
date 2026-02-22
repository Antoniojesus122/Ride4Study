<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte - Ride4Study</title>
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
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Centro de Soporte</h1>
            <p class="text-xl text-gray-400">Estamos aquí para ayudarte. Cuéntanos qué sucede.</p>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="flex-grow bg-surface">
        <div class="mx-auto max-w-4xl px-6 py-12">
            
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-6 rounded-2xl flex items-center gap-4 mb-12 animate-fade-in-down">
                <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                     <i class="fas fa-check text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">¡Mensaje enviado correctamente!</h3>
                    <p class="text-green-400/80"><?php echo htmlspecialchars($_GET['msg']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-gray-900 rounded-3xl p-8 md:p-12 border border-white/10 shadow-2xl">
                <form action="support.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="contact">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium text-gray-300">Nombre completo</label>
                            <input type="text" id="name" name="name" required 
                                class="w-full bg-surface border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="Tu nombre">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium text-gray-300">Correo electrónico</label>
                            <input type="email" id="email" name="email" required 
                                class="w-full bg-surface border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="tu@email.com">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="subject" class="text-sm font-medium text-gray-300">Asunto</label>
                        <div class="relative">
                            <select id="subject" name="subject" class="w-full bg-surface border border-white/10 rounded-xl px-5 py-4 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all appearance-none cursor-pointer">
                                <option value="Consulta General">Consulta General</option>
                                <option value="Problema Técnico">Problema Técnico</option>
                                <option value="Reportar Usuario">Reportar Usuario</option>
                                <option value="Sugerencia">Sugerencia</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="message" class="text-sm font-medium text-gray-300">Mensaje</label>
                        <textarea id="message" name="message" rows="6" required 
                            class="w-full bg-surface border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"
                            placeholder="Describe tu consulta con detalle..."></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-secondary font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-primary/20 transform hover:-translate-y-0.5 text-lg">
                            Enviar mensaje
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                 <div class="p-6 rounded-2xl bg-gray-900/50 border border-white/5">
                     <i class="fas fa-envelope text-2xl text-primary mb-3"></i>
                     <p class="text-sm text-gray-400">ride4study@outlook.es</p>
                 </div>
                 <div class="p-6 rounded-2xl bg-gray-900/50 border border-white/5">
                     <i class="fas fa-clock text-2xl text-blue-400 mb-3"></i>
                     <p class="text-sm text-gray-400">Lunes a Viernes, 9h - 18h</p>
                 </div>
                 <div class="p-6 rounded-2xl bg-gray-900/50 border border-white/5">
                     <i class="fas fa-map-marker-alt text-2xl text-purple-400 mb-3"></i>
                     <p class="text-sm text-gray-400">Lepe, Huelva</p>
                 </div>
            </div>

        </div>
    </main>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>