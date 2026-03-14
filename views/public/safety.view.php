<!DOCTYPE html>
    <html lang="<?= currentLang() ?>" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= t('safety.title') ?> - Ride4Study</title>

            <script src="https://cdn.tailwindcss.com"></script>
            <script src="public/js/tailwind-config.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

            <style>
                body { font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class="h-full text-white flex flex-col bg-gray-900">
            <?php include __DIR__ . '/../layouts/header.php'; ?>
            
            <!-- Encabezado -->
            <header class="pt-32 pb-16 bg-gradient-to-b from-gray-900 via-gray-900 to-surface">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 mb-6">
                        <i class="fas fa-shield-alt text-2xl text-primary"></i>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('safety.heading') ?></h1>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                        <?= t('safety.subheading') ?>
                    </p>
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="flex-grow bg-surface">
                <div class="mx-auto max-w-4xl px-6 py-12 space-y-6">

                    <!-- Bloque 1: Antes de reservar -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-search text-primary"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white"><?= t('safety.s1_title') ?></h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-star text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s1_i1_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s1_i1_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-shield-alt text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s1_i2_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s1_i2_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-comments text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s1_i3_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s1_i3_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-user-circle text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s1_i4_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s1_i4_content') ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 2: Durante el viaje -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-cyan-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-car text-cyan-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white"><?= t('safety.s2_title') ?></h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-map-marker-alt text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s2_i1_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s2_i1_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-id-card text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s2_i2_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s2_i2_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-mobile-alt text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s2_i3_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s2_i3_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-route text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s2_i4_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s2_i4_content') ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 3: Para conductores -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-green-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-steering-wheel text-green-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white"><?= t('safety.s3_title') ?></h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-check-circle text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s3_i1_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s3_i1_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-euro-sign text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s3_i2_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s3_i2_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-tools text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s3_i3_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s3_i3_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-phone text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s3_i4_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s3_i4_content') ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 4: Después del viaje -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-yellow-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-star text-yellow-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white"><?= t('safety.s4_title') ?></h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-pen text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s4_i1_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s4_i1_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-flag text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s4_i2_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s4_i2_content') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-lock text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1"><?= t('safety.s4_i3_title') ?></h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        <?= t('safety.s4_i3_content') ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 5: Emergencias -->
                    <section class="bg-red-950/30 rounded-3xl border border-red-500/20 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-red-500/10">
                            <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white"><?= t('safety.s5_title') ?></h2>
                        </div>
                        <div class="px-8 py-6">
                            <p class="text-gray-400 text-sm leading-relaxed mb-5">
                                <?= t('safety.s5_intro') ?>
                            </p>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-phone-alt text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-lg">112</p>
                                    <p class="text-gray-400 text-xs mt-1"><?= t('safety.s5_emergency') ?></p>
                                </div>
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-map-pin text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-sm leading-tight"><?= t('safety.s5_share_location') ?></p>
                                    <p class="text-gray-400 text-xs mt-1"><?= t('safety.s5_trusted_person') ?></p>
                                </div>
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-flag text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-sm leading-tight"><?= t('safety.s5_report') ?></p>
                                    <p class="text-gray-400 text-xs mt-1"><?= t('safety.s5_report_desc') ?></p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CTA bottom -->
                    <div class="text-center py-6">
                        <p class="text-gray-500 text-sm mb-4"><?= t('safety.cta_question') ?></p>
                        <a href="<?= url('/support') ?>" class="inline-flex items-center gap-2 bg-primary text-secondary font-bold px-6 py-3 rounded-full hover:bg-primary-dark transition-all transform hover:scale-105 shadow-lg shadow-primary/20">
                            <i class="fas fa-headset"></i> <?= t('safety.cta_button') ?>
                        </a>
                    </div>

                </div>
            </main>

            <!-- Footer -->
            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
        </body>
    </html>