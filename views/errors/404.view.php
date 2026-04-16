<!DOCTYPE html>
    <html lang="<?= currentLang() ?>" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Ride4Study</title>

            <base href="<?= url('/') ?>/">
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="public/js/tailwind-config.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

            <style>
                body { font-family: 'Inter', sans-serif; }
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                }
                .float { animation: float 3s ease-in-out infinite; }
            </style>
        </head>
        <body class="h-full text-white flex flex-col">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-900 to-primary/20 -z-10"></div>
            <div class="absolute top-1/4 right-0 w-96 h-96 bg-primary/10 rounded-full blur-3xl -z-10 animate-pulse"></div>
            <div class="absolute bottom-1/3 left-0 w-[400px] h-[400px] bg-blue-500/10 rounded-full blur-3xl -z-10"></div>

            <div class="flex-grow flex items-center justify-center px-6">
                <div class="text-center max-w-lg">

                    <div class="float mb-8">
                        <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm">
                            <i class="fas fa-road text-5xl text-primary/80" aria-hidden="true"></i>
                        </div>
                    </div>

                    <h1 class="text-8xl font-bold text-primary mb-2 tracking-tight">404</h1>

                    <h2 class="text-2xl font-semibold text-white mb-3"><?= t('404.title') ?></h2>

                    <p class="text-gray-400 mb-10 leading-relaxed">
                        <?= t('404.desc') ?><br>
                        <?= t('404.desc2') ?>
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="<?= url('/') ?>"
                        class="px-8 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 transform hover:-translate-y-0.5">
                            <i class="fas fa-home mr-2" aria-hidden="true"></i> <?= t('404.home') ?>
                        </a>
                        <a href="<?= url('/dashboard') ?>"
                        class="px-8 py-3 bg-white/5 border border-white/10 text-white font-medium rounded-xl hover:bg-white/10 transition-all">
                            <i class="fas fa-search mr-2" aria-hidden="true"></i> <?= t('404.search') ?>
                        </a>
                    </div>

                </div>
            </div>

            <footer class="py-6 text-center text-gray-500 text-sm">
                Ride4Study
            </footer>
        </body>
    </html>
