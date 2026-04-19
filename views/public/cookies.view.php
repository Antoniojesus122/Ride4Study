<!DOCTYPE html>
    <html lang="<?= currentLang() ?>" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= t('cookies.title') ?> - Ride4Study</title>

            <script src="https://cdn.tailwindcss.com"></script>
            <script src="public/js/tailwind-config.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

            <style>
                body { font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class="h-full text-white flex flex-col">

            <!-- Barra de navegacion -->
            <nav class="absolute w-full z-10 px-4 sm:px-6 py-4 sm:py-6 flex justify-between items-center max-w-7xl mx-auto left-0 right-0">
                <a href="<?= url('/') ?>" class="flex items-center gap-2">
                    <img src="public/img/logo.png" alt="" aria-hidden="true" class="w-8 h-8 sm:w-10 sm:h-10 object-contain">
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
            </nav>

            <!-- Encabezado -->
            <header class="pt-32 pb-16 bg-gradient-to-b from-gray-900 via-gray-900 to-surface">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6"><?= t('cookies.heading') ?></h1>
                    <p class="text-xl text-gray-400"><?= t('cookies.subheading') ?></p>
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="flex-grow bg-surface">
                <div class="mx-auto max-w-4xl px-6 py-12">
                    <section class="bg-gray-900/50 p-8 rounded-3xl border border-white/5">
                        <div class="prose prose-invert prose-indigo max-w-none text-gray-400">
                            <p><?= t('cookies.intro') ?></p>

                            <h3 class="text-white font-bold mt-6 mb-2"><?= t('cookies.section1_title') ?></h3>
                            <p><?= t('cookies.section1_content') ?></p>

                            <h3 class="text-white font-bold mt-6 mb-2"><?= t('cookies.section2_title') ?></h3>
                            <p><?= t('cookies.section2_content') ?></p>

                            <!-- Tabla de cookies -->
                            <div class="overflow-x-auto mt-4 mb-4">
                                <table class="w-full text-sm border border-gray-700 rounded-xl overflow-hidden">
                                    <thead>
                                        <tr class="bg-gray-800/80">
                                            <th class="text-left px-4 py-3 text-white font-semibold"><?= t('cookies.table_name') ?></th>
                                            <th class="text-left px-4 py-3 text-white font-semibold"><?= t('cookies.table_purpose') ?></th>
                                            <th class="text-left px-4 py-3 text-white font-semibold"><?= t('cookies.table_duration') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        <tr>
                                            <td class="px-4 py-3 text-gray-300 font-mono text-xs">PHPSESSID</td>
                                            <td class="px-4 py-3"><?= t('cookies.cookie_session') ?></td>
                                            <td class="px-4 py-3"><?= t('cookies.duration_session') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 text-gray-300 font-mono text-xs">lang</td>
                                            <td class="px-4 py-3"><?= t('cookies.cookie_lang') ?></td>
                                            <td class="px-4 py-3">1 <?= t('cookies.duration_year') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 text-gray-300 font-mono text-xs">cookie_consent</td>
                                            <td class="px-4 py-3"><?= t('cookies.cookie_consent') ?></td>
                                            <td class="px-4 py-3"><?= t('cookies.duration_permanent') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-white font-bold mt-6 mb-2"><?= t('cookies.section3_title') ?></h3>
                            <p><?= t('cookies.section3_content') ?></p>

                            <h3 class="text-white font-bold mt-6 mb-2"><?= t('cookies.section4_title') ?></h3>
                            <p><?= t('cookies.section4_content') ?></p>

                            <h3 class="text-white font-bold mt-6 mb-2"><?= t('cookies.section5_title') ?></h3>
                            <p><?= t('cookies.section5_content') ?></p>
                        </div>
                    </section>
                </div>
            </main>

            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
        </body>
    </html>
