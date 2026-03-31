<!DOCTYPE html>
    <html lang="<?= currentLang() ?>" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= t('inst_public.page_title') ?> - Ride4Study</title>

            <script src="https://cdn.tailwindcss.com"></script>
            <script src="public/js/tailwind-config.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

            <style>
                body { font-family: 'Inter', sans-serif; }
                .autocomplete-list { max-height: 200px; overflow-y: auto; }
                .autocomplete-list li:hover { background-color: rgba(96, 165, 250, 0.15); }
                .hero-glow {
                    background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
                }
                .benefit-card {
                    transition: all 0.3s ease;
                }
                .benefit-card:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
                }
                .stat-glow {
                    background: radial-gradient(circle at center, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
                }
            </style>
        </head>
        <body class="h-full text-white flex flex-col">

            <!-- Barra de navegacion -->
            <nav class="fixed w-full z-50 px-4 sm:px-6 py-4 sm:py-5 bg-gray-900/80 backdrop-blur-xl border-b border-white/5">
                <div class="flex justify-between items-center max-w-7xl mx-auto">
                    <a href="<?= url('/') ?>" class="flex items-center gap-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-xl flex items-center justify-center text-secondary font-bold text-lg sm:text-xl shadow-lg shadow-primary/20">
                            R
                        </div>
                        <span class="font-bold text-lg sm:text-2xl tracking-tight">
                            Ride4Study
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="<?= url('/login') ?>" class="text-xs sm:text-sm md:text-base text-white hover:text-primary font-medium px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 transition-colors whitespace-nowrap">
                            <?= t('landing.login') ?>
                        </a>
                        <a href="<?= url('/register') ?>" class="bg-white text-secondary hover:bg-gray-200 text-xs sm:text-sm md:text-base font-bold px-3 sm:px-4 md:px-6 py-1.5 sm:py-2 rounded-full transition-all duration-200 transform hover:scale-105 whitespace-nowrap">
                            <?= t('landing.register') ?>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Hero -->
            <header class="relative pt-36 pb-24 lg:pt-48 lg:pb-32 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-gray-900 to-surface -z-10"></div>
                <div class="hero-glow absolute inset-0 -z-10"></div>
                <div class="absolute top-20 right-10 w-72 h-72 bg-blue-500/8 rounded-full blur-3xl -z-10"></div>
                <div class="absolute bottom-10 left-10 w-64 h-64 bg-primary/8 rounded-full blur-3xl -z-10"></div>

                <div class="mx-auto max-w-5xl px-6 text-center">
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-sm font-semibold mb-10">
                        <i class="fas fa-university"></i> <?= t('inst_public.badge') ?>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-7xl font-extrabold mb-8 leading-[1.1] tracking-tight">
                        <?= t('inst_public.hero_title_1') ?><br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-blue-300 to-primary"><?= t('inst_public.hero_title_2') ?></span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed mb-12">
                        <?= t('inst_public.hero_desc') ?>
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#formulario" class="inline-flex items-center gap-3 px-8 py-4 bg-blue-500 text-white font-bold rounded-2xl hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/25 text-lg group">
                            <i class="fas fa-handshake group-hover:scale-110 transition-transform"></i> <?= t('inst_public.cta_request') ?>
                        </a>
                        <a href="#beneficios" class="inline-flex items-center gap-2 px-6 py-4 text-gray-300 hover:text-white font-medium transition-colors text-lg">
                            <?= t('inst_public.benefits_title') ?> <i class="fas fa-arrow-down text-sm animate-bounce"></i>
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-grow">

                <!-- Numeros destacados -->
                <section class="relative py-6 bg-surface border-y border-white/5">
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                            <div>
                                <p class="text-3xl md:text-4xl font-extrabold text-blue-400">100%</p>
                                <p class="text-sm text-gray-500 mt-1"><?= t('inst_public.stat_free') ?? 'Gratuito' ?></p>
                            </div>
                            <div>
                                <p class="text-3xl md:text-4xl font-extrabold text-primary">24/7</p>
                                <p class="text-sm text-gray-500 mt-1"><?= t('inst_public.stat_access') ?? 'Acceso al panel' ?></p>
                            </div>
                            <div>
                                <p class="text-3xl md:text-4xl font-extrabold text-purple-400">RGPD</p>
                                <p class="text-sm text-gray-500 mt-1"><?= t('inst_public.stat_privacy') ?? 'Datos protegidos' ?></p>
                            </div>
                            <div>
                                <p class="text-3xl md:text-4xl font-extrabold text-pink-400">&lt;48h</p>
                                <p class="text-sm text-gray-500 mt-1"><?= t('inst_public.stat_response') ?? 'Tiempo de respuesta' ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Beneficios para instituciones -->
                <section id="beneficios" class="py-24 bg-gray-900">
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="text-center mb-16">
                            <span class="text-blue-400 font-semibold text-sm uppercase tracking-widest"><?= t('inst_public.benefits_subtitle') ?? 'Ventajas' ?></span>
                            <h2 class="text-3xl md:text-5xl font-extrabold mt-3 mb-5"><?= t('inst_public.benefits_title') ?></h2>
                            <p class="text-gray-400 text-lg max-w-2xl mx-auto"><?= t('inst_public.benefits_desc') ?></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Panel de control -->
                            <div class="benefit-card bg-surface p-8 rounded-2xl border border-white/5 hover:border-blue-400/30 text-center group">
                                <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-500/20 transition-colors">
                                    <i class="fas fa-chart-line text-2xl text-blue-400"></i>
                                </div>
                                <h3 class="text-lg font-bold mb-3"><?= t('inst_public.benefit_dashboard_title') ?></h3>
                                <p class="text-sm text-gray-400 leading-relaxed"><?= t('inst_public.benefit_dashboard_desc') ?></p>
                            </div>

                            <!-- Estudiantes -->
                            <div class="benefit-card bg-surface p-8 rounded-2xl border border-white/5 hover:border-primary/30 text-center group">
                                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-primary/20 transition-colors">
                                    <i class="fas fa-users text-2xl text-primary"></i>
                                </div>
                                <h3 class="text-lg font-bold mb-3"><?= t('inst_public.benefit_students_title') ?></h3>
                                <p class="text-sm text-gray-400 leading-relaxed"><?= t('inst_public.benefit_students_desc') ?></p>
                            </div>

                            <!-- Sostenibilidad -->
                            <div class="benefit-card bg-surface p-8 rounded-2xl border border-white/5 hover:border-purple-400/30 text-center group">
                                <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-purple-500/20 transition-colors">
                                    <i class="fas fa-leaf text-2xl text-purple-400"></i>
                                </div>
                                <h3 class="text-lg font-bold mb-3"><?= t('inst_public.benefit_eco_title') ?></h3>
                                <p class="text-sm text-gray-400 leading-relaxed"><?= t('inst_public.benefit_eco_desc') ?></p>
                            </div>

                            <!-- Comunicacion -->
                            <div class="benefit-card bg-surface p-8 rounded-2xl border border-white/5 hover:border-pink-400/30 text-center group">
                                <div class="w-16 h-16 bg-pink-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-pink-500/20 transition-colors">
                                    <i class="fas fa-comments text-2xl text-pink-400"></i>
                                </div>
                                <h3 class="text-lg font-bold mb-3"><?= t('inst_public.benefit_comm_title') ?></h3>
                                <p class="text-sm text-gray-400 leading-relaxed"><?= t('inst_public.benefit_comm_desc') ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Como funciona -->
                <section class="py-24 bg-surface">
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="text-center mb-16">
                            <span class="text-primary font-semibold text-sm uppercase tracking-widest"><?= t('inst_public.how_subtitle') ?? 'Proceso' ?></span>
                            <h2 class="text-3xl md:text-5xl font-extrabold mt-3 mb-5"><?= t('inst_public.how_title') ?? 'Como empezar' ?></h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-blue-500/15 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-blue-500/20">
                                    <span class="text-2xl font-extrabold text-blue-400">1</span>
                                </div>
                                <h3 class="font-bold text-lg mb-2"><?= t('inst_public.step_1_title') ?? 'Solicita acceso' ?></h3>
                                <p class="text-sm text-gray-400"><?= t('inst_public.step_1_desc') ?? 'Completa el formulario de contacto con los datos de tu centro educativo.' ?></p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-primary/15 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-primary/20">
                                    <span class="text-2xl font-extrabold text-primary">2</span>
                                </div>
                                <h3 class="font-bold text-lg mb-2"><?= t('inst_public.step_2_title') ?? 'Recibe credenciales' ?></h3>
                                <p class="text-sm text-gray-400"><?= t('inst_public.step_2_desc') ?? 'Nuestro equipo validara tu solicitud y te enviara las credenciales de acceso.' ?></p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-purple-500/15 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-purple-500/20">
                                    <span class="text-2xl font-extrabold text-purple-400">3</span>
                                </div>
                                <h3 class="font-bold text-lg mb-2"><?= t('inst_public.step_3_title') ?? 'Accede al panel' ?></h3>
                                <p class="text-sm text-gray-400"><?= t('inst_public.step_3_desc') ?? 'Inicia sesion en tu panel y comienza a ver la actividad de tus estudiantes.' ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Metricas que veran -->
                <section class="py-24 bg-gray-900 stat-glow">
                    <div class="mx-auto max-w-7xl px-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                            <div>
                                <span class="text-blue-400 font-semibold text-sm uppercase tracking-widest"><?= t('inst_public.metrics_subtitle') ?? 'Panel exclusivo' ?></span>
                                <h2 class="text-3xl md:text-4xl font-extrabold mt-3 mb-6"><?= t('inst_public.metrics_title') ?></h2>
                                <p class="text-gray-400 text-lg mb-10 leading-relaxed"><?= t('inst_public.metrics_desc') ?></p>
                                <ul class="space-y-5">
                                    <li class="flex items-start gap-4">
                                        <div class="w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center mt-0.5 shrink-0">
                                            <i class="fas fa-check text-xs text-blue-400"></i>
                                        </div>
                                        <span class="text-gray-300"><?= t('inst_public.metric_1') ?></span>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="w-7 h-7 rounded-lg bg-primary/20 flex items-center justify-center mt-0.5 shrink-0">
                                            <i class="fas fa-check text-xs text-primary"></i>
                                        </div>
                                        <span class="text-gray-300"><?= t('inst_public.metric_2') ?></span>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="w-7 h-7 rounded-lg bg-purple-500/20 flex items-center justify-center mt-0.5 shrink-0">
                                            <i class="fas fa-check text-xs text-purple-400"></i>
                                        </div>
                                        <span class="text-gray-300"><?= t('inst_public.metric_3') ?></span>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="w-7 h-7 rounded-lg bg-pink-500/20 flex items-center justify-center mt-0.5 shrink-0">
                                            <i class="fas fa-check text-xs text-pink-400"></i>
                                        </div>
                                        <span class="text-gray-300"><?= t('inst_public.metric_4') ?></span>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="w-7 h-7 rounded-lg bg-yellow-500/20 flex items-center justify-center mt-0.5 shrink-0">
                                            <i class="fas fa-check text-xs text-yellow-400"></i>
                                        </div>
                                        <span class="text-gray-300"><?= t('inst_public.metric_5') ?></span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Mockup visual del panel -->
                            <div class="relative">
                                <div class="absolute -inset-4 bg-gradient-to-br from-blue-500/10 to-primary/10 rounded-[2rem] blur-2xl"></div>
                                <div class="relative bg-surface backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl">
                                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                                        <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-university text-blue-400"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm">IES La Arboleda</p>
                                            <p class="text-xs text-gray-500"><?= t('inst_public.mockup_panel') ?></p>
                                        </div>
                                        <div class="ml-auto flex gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-red-500/60"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-500/60"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-500/60"></div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 mb-5">
                                        <div class="bg-gray-900/60 p-4 rounded-xl border border-white/5">
                                            <p class="text-2xl font-extrabold text-blue-400">127</p>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= t('inst_public.mockup_students') ?></p>
                                        </div>
                                        <div class="bg-gray-900/60 p-4 rounded-xl border border-white/5">
                                            <p class="text-2xl font-extrabold text-primary">342</p>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= t('inst_public.mockup_trips') ?></p>
                                        </div>
                                        <div class="bg-gray-900/60 p-4 rounded-xl border border-white/5">
                                            <p class="text-2xl font-extrabold text-purple-400">4.7 <i class="fas fa-star text-xs"></i></p>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= t('inst_public.mockup_rating') ?></p>
                                        </div>
                                        <div class="bg-gray-900/60 p-4 rounded-xl border border-white/5">
                                            <p class="text-2xl font-extrabold text-green-400">1.2t</p>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= t('inst_public.mockup_co2') ?></p>
                                        </div>
                                    </div>
                                    <div class="bg-gray-900/60 p-4 rounded-xl border border-white/5">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="text-sm font-semibold"><?= t('inst_public.mockup_chart') ?></p>
                                            <span class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">2026</span>
                                        </div>
                                        <div class="flex items-end gap-1.5 h-24">
                                            <div class="flex-1 bg-gradient-to-t from-blue-500/40 to-blue-500/20 rounded-t-md" style="height: 40%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-blue-500/50 to-blue-500/25 rounded-t-md" style="height: 55%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-blue-500/60 to-blue-500/30 rounded-t-md" style="height: 70%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-blue-500/65 to-blue-500/35 rounded-t-md" style="height: 60%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-blue-500/75 to-blue-500/40 rounded-t-md" style="height: 85%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-blue-400 to-blue-500/50 rounded-t-md" style="height: 100%"></div>
                                        </div>
                                        <div class="flex justify-between mt-2 text-[10px] text-gray-600">
                                            <span>Oct</span><span>Nov</span><span>Dic</span><span>Ene</span><span>Feb</span><span>Mar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Formulario de contacto -->
                <section id="formulario" class="py-24 bg-surface">
                    <div class="mx-auto max-w-4xl px-6">
                        <div class="text-center mb-12">
                            <span class="text-blue-400 font-semibold text-sm uppercase tracking-widest"><?= t('inst_public.form_subtitle') ?? 'Contacto' ?></span>
                            <h2 class="text-3xl md:text-4xl font-extrabold mt-3 mb-4"><?= t('inst_public.form_title') ?></h2>
                            <p class="text-gray-400 text-lg max-w-xl mx-auto"><?= t('inst_public.form_desc') ?></p>
                        </div>

                        <?php $flashData = $flashData ?? getFlash(); ?>
                        <?php if ($flashData && $flashData['type'] === 'success'): ?>
                            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-6 rounded-2xl flex items-center gap-4 mb-10">
                                <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg"><?= t('inst_public.success_title') ?></h3>
                                    <p class="text-green-400/80"><?= t('inst_public.success_msg') ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($flashData && $flashData['type'] === 'error'): ?>
                            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-6 rounded-2xl flex items-center gap-4 mb-10">
                                <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg"><?= t('inst_public.error_title') ?></h3>
                                    <p class="text-red-400/80"><?= htmlspecialchars($flashData['message']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="bg-gray-900 rounded-2xl p-8 md:p-10 border border-white/10 shadow-2xl">
                            <form action="<?= url('/instituciones') ?>" method="POST" class="space-y-8">
                                <input type="hidden" name="action" value="contact">

                                <!-- Datos de la institucion -->
                                <div>
                                    <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                                        <div class="w-8 h-8 bg-blue-500/15 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-university text-blue-400 text-sm"></i>
                                        </div>
                                        <?= t('inst_public.form_section_inst') ?>
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Nombre de la institucion con autocompletado -->
                                        <div class="space-y-2 relative">
                                            <label for="inst_nombre" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_name') ?> <span class="text-red-400">*</span></label>
                                            <input type="text" id="inst_nombre" name="inst_nombre" required autocomplete="off"
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="<?= t('inst_public.form_inst_name_ph') ?>">
                                            <ul id="inst-autocomplete" class="autocomplete-list hidden absolute z-20 w-full bg-gray-800 border border-gray-600 rounded-xl mt-1 shadow-lg"></ul>
                                        </div>

                                        <!-- Tipo de institucion -->
                                        <div class="space-y-2">
                                            <label for="inst_tipo" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_type') ?> <span class="text-red-400">*</span></label>
                                            <div class="relative">
                                                <select id="inst_tipo" name="inst_tipo" required
                                                    class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                                                    <option value=""><?= t('inst_public.form_select') ?></option>
                                                    <option value="universidad"><?= t('inst_public.type_university') ?></option>
                                                    <option value="instituto"><?= t('inst_public.type_highschool') ?></option>
                                                    <option value="fp"><?= t('inst_public.type_fp') ?></option>
                                                    <option value="otro"><?= t('inst_public.type_other') ?></option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                                    <i class="fas fa-chevron-down text-sm"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Email institucional -->
                                        <div class="space-y-2">
                                            <label for="inst_correo" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_email') ?> <span class="text-red-400">*</span></label>
                                            <input type="email" id="inst_correo" name="inst_correo" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="secretaria@micentro.es">
                                        </div>

                                        <!-- Telefono institucional -->
                                        <div class="space-y-2">
                                            <label for="inst_telefono" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_phone') ?> <span class="text-red-400">*</span></label>
                                            <input type="tel" id="inst_telefono" name="inst_telefono" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="959 000 000">
                                        </div>

                                        <!-- Direccion -->
                                        <div class="space-y-2 md:col-span-2">
                                            <label for="inst_direccion" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_address') ?> <span class="text-red-400">*</span></label>
                                            <input type="text" id="inst_direccion" name="inst_direccion" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="<?= t('inst_public.form_inst_address_ph') ?>">
                                        </div>

                                        <!-- Numero aproximado de estudiantes -->
                                        <div class="space-y-2">
                                            <label for="inst_estudiantes" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_num_students') ?> <span class="text-red-400">*</span></label>
                                            <div class="relative">
                                                <select id="inst_estudiantes" name="inst_estudiantes" required
                                                    class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                                                    <option value=""><?= t('inst_public.form_select') ?></option>
                                                    <option value="menos_100"><?= t('inst_public.students_less_100') ?></option>
                                                    <option value="100_500"><?= t('inst_public.students_100_500') ?></option>
                                                    <option value="500_1000"><?= t('inst_public.students_500_1000') ?></option>
                                                    <option value="1000_5000"><?= t('inst_public.students_1000_5000') ?></option>
                                                    <option value="mas_5000"><?= t('inst_public.students_more_5000') ?></option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                                    <i class="fas fa-chevron-down text-sm"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Web de la institucion -->
                                        <div class="space-y-2">
                                            <label for="inst_web" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_inst_web') ?></label>
                                            <input type="url" id="inst_web" name="inst_web"
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="https://www.micentro.es">
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos de la persona de contacto -->
                                <div class="pt-6 border-t border-white/5">
                                    <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                                        <div class="w-8 h-8 bg-primary/15 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-user-tie text-primary text-sm"></i>
                                        </div>
                                        <?= t('inst_public.form_section_contact') ?>
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Nombre del responsable -->
                                        <div class="space-y-2">
                                            <label for="contacto_nombre" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_contact_name') ?> <span class="text-red-400">*</span></label>
                                            <input type="text" id="contacto_nombre" name="contacto_nombre" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="<?= t('inst_public.form_contact_name_ph') ?>">
                                        </div>

                                        <!-- Cargo -->
                                        <div class="space-y-2">
                                            <label for="contacto_cargo" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_contact_role') ?> <span class="text-red-400">*</span></label>
                                            <input type="text" id="contacto_cargo" name="contacto_cargo" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="<?= t('inst_public.form_contact_role_ph') ?>">
                                        </div>

                                        <!-- Email del responsable -->
                                        <div class="space-y-2">
                                            <label for="contacto_email" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_contact_email') ?> <span class="text-red-400">*</span></label>
                                            <input type="email" id="contacto_email" name="contacto_email" required
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="responsable@micentro.es">
                                        </div>

                                        <!-- Telefono del responsable -->
                                        <div class="space-y-2">
                                            <label for="contacto_telefono" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_contact_phone') ?></label>
                                            <input type="tel" id="contacto_telefono" name="contacto_telefono"
                                                class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="600 000 000">
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje adicional -->
                                <div class="pt-6 border-t border-white/5">
                                    <div class="space-y-2">
                                        <label for="mensaje" class="text-sm font-medium text-gray-300"><?= t('inst_public.form_message') ?></label>
                                        <textarea id="mensaje" name="mensaje" rows="4"
                                            class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                            placeholder="<?= t('inst_public.form_message_ph') ?>"></textarea>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-blue-500/25 transform hover:-translate-y-0.5 text-lg">
                                        <i class="fas fa-paper-plane mr-2"></i> <?= t('inst_public.form_submit') ?>
                                    </button>
                                    <p class="text-center text-xs text-gray-500 mt-4"><?= t('inst_public.form_note') ?></p>
                                </div>
                            </form>
                        </div>

                        <!-- Info de contacto -->
                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6 text-center">
                            <div class="p-6 rounded-2xl bg-gray-900/50 border border-white/5 hover:border-blue-500/20 transition-colors">
                                <i class="fas fa-envelope text-2xl text-blue-400 mb-3"></i>
                                <p class="font-semibold mb-1"><?= t('inst_public.contact_email') ?></p>
                                <p class="text-sm text-gray-400">ride4study@outlook.es</p>
                            </div>
                            <div class="p-6 rounded-2xl bg-gray-900/50 border border-white/5 hover:border-primary/20 transition-colors">
                                <i class="fas fa-clock text-2xl text-primary mb-3"></i>
                                <p class="font-semibold mb-1"><?= t('inst_public.contact_response') ?></p>
                                <p class="text-sm text-gray-400"><?= t('inst_public.contact_response_time') ?></p>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

            <!-- Script de autocompletado de instituciones -->
            <script>
                const input = document.getElementById('inst_nombre');
                const list = document.getElementById('inst-autocomplete');
                let debounceTimer;

                input.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        list.classList.add('hidden');
                        list.innerHTML = '';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch('<?= url('/api/instituciones-search') ?>?q=' + encodeURIComponent(query))
                            .then(r => r.json())
                            .then(data => {
                                list.innerHTML = '';
                                if (data.length === 0) {
                                    list.classList.add('hidden');
                                    return;
                                }
                                data.forEach(item => {
                                    const li = document.createElement('li');
                                    li.textContent = item.nombre;
                                    li.className = 'px-4 py-3 text-sm text-gray-300 cursor-pointer border-b border-gray-700/50 last:border-0 transition-colors';
                                    li.addEventListener('click', () => {
                                        input.value = item.nombre;
                                        list.classList.add('hidden');
                                    });
                                    list.appendChild(li);
                                });
                                list.classList.remove('hidden');
                            })
                            .catch(() => list.classList.add('hidden'));
                    }, 300);
                });

                // Cerrar autocompletado al hacer clic fuera
                document.addEventListener('click', function (e) {
                    if (!input.contains(e.target) && !list.contains(e.target)) {
                        list.classList.add('hidden');
                    }
                });
            </script>
        </body>
    </html>
