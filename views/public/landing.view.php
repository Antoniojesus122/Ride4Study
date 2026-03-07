<!DOCTYPE html>
    <html lang="es" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ride4Study - Tu viaje, tu comunidad</title>

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

                <div class="hidden md:flex gap-8 items-center">
                    <a href="#como-funciona" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                        Cómo funciona
                    </a>
                    <a href="#ventajas" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">
                        Ventajas
                    </a>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="<?= url('/login') ?>" class="text-xs sm:text-sm md:text-base text-white hover:text-primary font-medium px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 transition-colors whitespace-nowrap">
                        Entrar
                    </a>
                    <a href="<?= url('/register') ?>" class="bg-white text-secondary hover:bg-gray-200 text-xs sm:text-sm md:text-base font-bold px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-full transition-all duration-200 transform hover:scale-105 whitespace-nowrap">
                        Registrarse
                    </a>
                </div>
            </nav>

            <!-- Contenido Principal -->
            <main class="flex-grow">
                
                <!-- Sección Hero -->
                <section class="relative overflow-hidden pt-32 pb-20 lg:pt-48 lg:pb-32">
                    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-900 to-primary/20 -z-10"></div>
                    <!-- Decoracion -->
                    <div class="absolute top-1/4 right-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -z-10 animate-pulse"></div>
                    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-3xl -z-10"></div>
            
                    <div class="mx-auto max-w-7xl px-6 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div class="space-y-8">
                            
                            <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
                                Viaja mejor.<br>
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-400">Gasta menos.</span>
                            </h1>
                            
                            <p class="text-xl text-gray-400 max-w-lg leading-relaxed">
                                Conecta con estudiantes de tu universidad o instituto. Comparte gastos, reduce tu huella de carbono y haz amigos en el camino.
                            </p>
            
                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <a href="<?= url('/register') ?>" class="px-8 py-4 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-[0_0_20px_rgba(110,231,183,0.4)] text-center text-lg">
                                    Empezar ahora
                                </a>
                                <a href="<?= url('/login') ?>" class="px-8 py-4 bg-surface border border-white/10 text-white font-medium rounded-xl hover:bg-surface/80 transition-all flex items-center justify-center gap-2 group">
                                    <i class="fas fa-search text-primary group-hover:scale-110 transition-transform"></i> Buscar viaje
                                </a>
                            </div>
                            
                            <div class="pt-8 border-t border-white/5 flex gap-12 text-center sm:text-left">
                                <div>
                                    <p class="text-3xl font-bold text-white">0€</p>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Comisiones</p>
                                </div>
                                <div>
                                    <p class="text-3xl font-bold text-white">+50</p>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Rutas diarias</p>
                                </div>
                                <div>
                                    <p class="text-3xl font-bold text-white">100%</p>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Verificado</p>
                                </div>
                            </div>
                        </div>
            
                        <div class="relative hidden lg:block">
                            <!-- Mockup visual -->
                            <div class="relative z-10 bg-surface/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl transform rotate-2 hover:rotate-0 transition-all duration-500">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <p class="text-sm text-gray-400">Próximo viaje</p>
                                        <h3 class="text-xl font-bold">Lepe <i class="fas fa-arrow-right text-sm mx-2 text-primary"></i> Huelva</h3>
                                    </div>
                                    <span class="bg-primary text-secondary text-xs font-bold px-2 py-1 rounded">HOY</span>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4 bg-surface/60 p-3 rounded-xl">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold">JD</div>
                                        <div>
                                            <p class="text-sm font-bold">Juan Diego</p>
                                            <p class="text-xs text-gray-400"> ★ 4.9 • Conductor experto</p>
                                        </div>
                                        <div class="ml-auto text-primary font-bold">15€</div>
                                    </div>
                                    <div class="flex items-center gap-4 bg-surface/60 p-3 rounded-xl border border-primary/30">
                                        <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center font-bold">AS</div>
                                        <div>
                                            <p class="text-sm font-bold">Ana Sofía</p>
                                            <p class="text-xs text-gray-400"> ★ 5.0 • Estudiante UBU</p>
                                        </div>
                                        <div class="ml-auto text-primary font-bold">15€</div>
                                    </div>
                                </div>
                                
                                <button class="w-full mt-6 bg-secondary hover:bg-black text-white py-3 rounded-lg font-medium transition-colors">
                                    Reservar plaza
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            
                <!-- Sección Cómo funciona -->
                <section id="como-funciona" class="py-24 bg-gray-900 relative">
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="text-center mb-16">
                            <h2 class="text-3xl md:text-5xl font-bold mb-6">Tu viaje en 3 simples pasos</h2>
                            <p class="text-gray-400 text-lg max-w-2xl mx-auto">Ride4Study hace que compartir coche sea fácil, seguro y rápido para estudiantes como tú.</p>
                        </div>
            
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                            <!-- Línea -->
                            <div class="hidden md:block absolute top-12 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-primary/50 to-transparent -z-10"></div>
            
                            <!-- Paso 1 -->
                            <div class="relative group">
                                <div class="w-24 h-24 bg-surface rounded-2xl border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/5 group-hover:scale-110 transition-transform duration-300 z-10 relative">
                                    <i class="fas fa-search text-4xl text-primary"></i>
                                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-primary rounded-full flex items-center justify-center font-bold text-secondary border-4 border-gray-900">1</div>
                                </div>
                                <h3 class="text-xl font-bold text-center mb-4">Busca tu viaje</h3>
                                <p class="text-gray-400 text-center">Introduce tu origen, destino y fecha. Encuentra compañeros de tu universidad en segundos.</p>
                            </div>
            
                            <!-- Paso 2 -->
                            <div class="relative group">
                                <div class="w-24 h-24 bg-surface rounded-2xl border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/5 group-hover:scale-110 transition-transform duration-300 z-10 relative">
                                    <i class="fas fa-check-circle text-4xl text-blue-400"></i>
                                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center font-bold text-secondary border-4 border-gray-900">2</div>
                                </div>
                                <h3 class="text-xl font-bold text-center mb-4">Reserva tu plaza</h3>
                                <p class="text-gray-400 text-center">Verifica el perfil del conductor y reserva al instante. ¡Sin comisiones ocultas!</p>
                            </div>
            
                            <!-- Paso 3 -->
                            <div class="relative group">
                                <div class="w-24 h-24 bg-surface rounded-2xl border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary/5 group-hover:scale-110 transition-transform duration-300 z-10 relative">
                                    <i class="fas fa-car-side text-4xl text-purple-400"></i>
                                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-purple-400 rounded-full flex items-center justify-center font-bold text-secondary border-4 border-gray-900">3</div>
                                </div>
                                <h3 class="text-xl font-bold text-center mb-4">Ahorra viajando</h3>
                                <p class="text-gray-400 text-center">Disfruta del viaje, conoce gente nueva y llega a clase cómodamente gastando menos.</p>
                            </div>
                        </div>
                    </div>
                </section>
            
                <!-- Sección Ventajas -->
                <section id="ventajas" class="py-24 bg-surface relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                    
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                            <div class="order-2 lg:order-1 relative">
                                <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full -z-10"></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="bg-gray-900/80 backdrop-blur-sm p-6 rounded-2xl border border-white/5 hover:border-primary/50 transition-colors">
                                        <i class="fas fa-wallet text-3xl text-primary mb-4"></i>
                                        <h4 class="font-bold text-lg mb-2">Ahorro garantizado</h4>
                                        <p class="text-sm text-gray-400">Comparte gastos de gasolina y peajes. Viajar acompañado es mucho más barato.</p>
                                    </div>
                                    <div class="bg-gray-900/80 backdrop-blur-sm p-6 rounded-2xl border border-white/5 hover:border-blue-400/50 transition-colors sm:mt-8">
                                        <i class="fas fa-shield-alt text-3xl text-blue-400 mb-4"></i>
                                        <h4 class="font-bold text-lg mb-2">Seguridad total</h4>
                                        <p class="text-sm text-gray-400">Perfiles verificados con email institucional. Sabes con quién viajas.</p>
                                    </div>
                                    <div class="bg-gray-900/80 backdrop-blur-sm p-6 rounded-2xl border border-white/5 hover:border-purple-400/50 transition-colors">
                                        <i class="fas fa-leaf text-3xl text-purple-400 mb-4"></i>
                                        <h4 class="font-bold text-lg mb-2">Sostenible</h4>
                                        <p class="text-sm text-gray-400">Menos coches en la carretera = menos emisiones. Ayuda al planeta.</p>
                                    </div>
                                    <div class="bg-gray-900/80 backdrop-blur-sm p-6 rounded-2xl border border-white/5 hover:border-pink-400/50 transition-colors sm:mt-8">
                                        <i class="fas fa-users text-3xl text-pink-400 mb-4"></i>
                                        <h4 class="font-bold text-lg mb-2">Comunidad</h4>
                                        <p class="text-sm text-gray-400">Conecta con compañeros de tu misma institución o campus.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-1 lg:order-2">
                                <h2 class="text-3xl md:text-5xl font-bold mb-6">Por qué elegir <span class="text-primary">Ride4Study</span>?</h2>
                                <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                                    No somos solo una app de carpooling. Somos la mayor comunidad de movilidad estudiantil. Diseñada por estudiantes, para estudiantes. Olvídate de los horarios rígidos del bus o de viajar solo.
                                </p>
                                
                                <ul class="space-y-4">
                                    <li class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center">
                                            <i class="fas fa-check text-xs text-primary"></i>
                                        </div>
                                        <span>Exclusivo para comunidad estudiantil</span>
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center">
                                            <i class="fas fa-check text-xs text-primary"></i>
                                        </div>
                                        <span>Sin comisiones de reserva</span>
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center">
                                            <i class="fas fa-check text-xs text-primary"></i>
                                        </div>
                                        <span>Flexibilidad horaria total</span>
                                    </li>
                                </ul>
            
                                <div class="mt-10">
                                    <a href="<?= url('/register') ?>" class="inline-block px-8 py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-200 transition-all shadow-lg transform hover:-translate-y-1">
                                        Únete gratis hoy
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
            
            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
        </body>
    </html>