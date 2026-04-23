<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title><?= t('login.title') ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="icon" href="public/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="public/favicon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="public/favicon-16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="public/apple-touch-icon.png">
        <link rel="manifest" href="public/site.webmanifest">
        <meta name="theme-color" content="#111827">
        <link rel="stylesheet" href="public/css/accessibility.css">
        <style>
            body { font-family: 'Inter', sans-serif; }
            @keyframes blob { 0%,100% { transform: translate(0,0) scale(1) } 33% { transform: translate(20px,-20px) scale(1.05) } 66% { transform: translate(-10px,10px) scale(0.98) } }
            .blob { animation: blob 14s ease-in-out infinite; }
            .grid-pattern {
                background-image:
                    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                background-size: 32px 32px;
            }
        </style>
    </head>
    <body class="h-full text-text">
        <div class="flex min-h-full">

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- PANEL IZQUIERDO: FORMULARIO                                      -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="relative flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:w-[520px] lg:px-16 bg-secondary overflow-hidden">

                <!-- Decoración sutil -->
                <div class="absolute inset-0 grid-pattern opacity-50 pointer-events-none"></div>
                <div class="absolute -top-40 -left-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl pointer-events-none blob"></div>
                <div class="absolute -bottom-40 -right-20 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl pointer-events-none blob" style="animation-delay: -7s;"></div>

                <div class="relative mx-auto w-full max-w-sm">

                    <div class="text-center lg:text-left mb-8">
                        <!-- Logo -->
                        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2.5 group mb-6" aria-label="<?= t('a11y.home') ?>">
                            <img src="public/img/logo.png" alt="" aria-hidden="true" class="h-11 w-11 object-contain transition-transform group-hover:rotate-6">
                            <span class="text-2xl font-extrabold tracking-tight text-white group-hover:text-primary transition-colors">Ride4Study</span>
                        </a>

                        <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-white"><?= t('login.hello') ?></h1>
                        <p class="mt-2.5 text-sm leading-6 text-gray-400">
                            <?= t('login.no_account') ?>
                            <a href="<?= url('/register') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('login.register_free') ?></a>
                        </p>
                    </div>

                    <main id="main-content" tabindex="-1">
                        <div class="relative bg-gradient-to-b from-surface to-surface/80 rounded-2xl shadow-2xl ring-1 ring-white/10 backdrop-blur-sm p-7 sm:p-8">

                            <?php if ($error): ?>
                                <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/30 p-3.5 flex items-start gap-3" role="alert" aria-live="assertive">
                                    <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center shrink-0">
                                        <i class="fas fa-exclamation text-red-400 text-xs" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-medium text-red-200 leading-snug"><?= htmlspecialchars($error) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="mb-5 rounded-xl bg-green-500/10 border border-green-500/30 p-3.5 flex items-start gap-3" role="status">
                                    <div class="w-7 h-7 rounded-lg bg-green-500/20 flex items-center justify-center shrink-0">
                                        <i class="fas fa-check text-green-400 text-xs" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-medium text-green-200 leading-snug"><?= htmlspecialchars($success) ?></p>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('/login') ?>" class="space-y-5" novalidate>
                                <div>
                                    <label for="correo" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('login.email') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                            <i class="fas fa-envelope text-gray-500 text-sm" aria-hidden="true"></i>
                                        </div>
                                        <input id="correo" name="correo" type="email" autocomplete="email" required inputmode="email"
                                            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                            <?= $error ? 'aria-invalid="true"' : '' ?>
                                            class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-3 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                            placeholder="tu@email.com">
                                    </div>
                                </div>

                                <div>
                                    <label for="contrasena" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('login.password') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                            <i class="fas fa-lock text-gray-500 text-sm" aria-hidden="true"></i>
                                        </div>
                                        <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required
                                            <?= $error ? 'aria-invalid="true"' : '' ?>
                                            class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-11 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                            placeholder="••••••••••••">
                                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-200 transition-colors" aria-label="<?= t('a11y.toggle_password') ?>" aria-pressed="false" id="toggle-password-btn">
                                            <i id="password-icon" class="fas fa-eye text-sm" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <a href="<?= url('/forgot-password') ?>" class="text-[12px] font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('login.forgot') ?></a>
                                    </div>
                                </div>

                                <button type="submit" name="login"
                                    class="group relative flex w-full justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-emerald-400 px-3 py-3 text-sm font-bold text-secondary shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all overflow-hidden">
                                    <span class="relative z-10 flex items-center gap-2">
                                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i> <?= t('login.submit') ?>
                                    </span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-primary opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                </button>
                            </form>

                            <div class="mt-6 pt-5 border-t border-white/5 text-center">
                                <a href="<?= url('/institution-login') ?>" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-blue-300 transition-colors">
                                    <i class="fas fa-university text-[11px]" aria-hidden="true"></i>
                                    <?= t('login.institution_access') ?>
                                </a>
                            </div>
                        </div>

                        <p class="lg:hidden mt-8 text-center text-[11px] text-gray-600">
                            &copy; <?= date('Y') ?> Ride4Study
                        </p>
                    </main>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- PANEL DERECHO: HERO VISUAL                                       -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="relative hidden w-0 flex-1 lg:block overflow-hidden" aria-hidden="true">
                <div class="absolute inset-0" style="background-image: url('public/img/imgLogin.jpg'); background-size: cover; background-position: center;"></div>
                <!-- Overlay oscuro neutro (sin tinte de color) -->
                <div class="absolute inset-0 bg-secondary/70"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-secondary/80 via-secondary/30 to-transparent"></div>

                <div class="relative z-10 flex flex-col justify-between h-full p-12 xl:p-16 text-white">

                    <div>
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white/90 text-xs font-bold uppercase tracking-wider shadow-lg">
                            <i class="fas fa-car-side" aria-hidden="true"></i> Ride4Study · Students
                        </div>
                    </div>

                    <div class="max-w-lg">
                        <h2 class="text-5xl xl:text-6xl font-extrabold leading-[1.05] tracking-tight drop-shadow-2xl">
                            <span class="text-white"><?= t('login.hero_1') ?></span><br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-emerald-300 to-teal-300"><?= t('login.hero_2') ?></span>
                        </h2>
                        <p class="mt-5 text-lg text-gray-200/90 leading-relaxed drop-shadow"><?= t('login.hero_desc') ?></p>

                        <div class="mt-10 space-y-3">
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/40 to-emerald-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-car text-primary text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('login.hero_feature_1') ?></p>
                            </div>
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400/40 to-blue-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-shield-halved text-blue-200 text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('login.hero_feature_2') ?></p>
                            </div>
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-400/40 to-emerald-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-leaf text-green-200 text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('login.hero_feature_3') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-400/80">
                        <p>&copy; <?= date('Y') ?> Ride4Study. <?= t('login.rights') ?></p>
                        <div class="flex items-center gap-2 bg-white/5 backdrop-blur-sm rounded-full px-3 py-1 border border-white/10">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            <span class="font-medium text-gray-200">Servicio operativo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function togglePasswordVisibility() {
                const input = document.getElementById('contrasena');
                const icon = document.getElementById('password-icon');
                const btn = document.getElementById('toggle-password-btn');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                    btn.setAttribute('aria-pressed', 'true');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                    btn.setAttribute('aria-pressed', 'false');
                }
            }
        </script>
    </body>
</html>
