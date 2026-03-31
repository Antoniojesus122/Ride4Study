<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title><?= t('inst_auth.login_title') ?> - Ride4Study</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="h-full text-text">
        <div class="flex min-h-full">
            <!-- Formulario -->
            <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-secondary">
                <div class="mx-auto w-full max-w-sm lg:w-96">

                    <!-- Logo -->
                    <div class="text-center lg:text-left">
                        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                                R
                            </div>
                            <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
                        </a>
                        <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-xs font-semibold">
                            <i class="fas fa-university"></i> <?= t('inst_auth.badge') ?>
                        </div>
                        <h2 class="mt-4 text-3xl font-bold leading-9 tracking-tight text-white"><?= t('inst_auth.login_title') ?></h2>
                        <p class="mt-2 text-sm leading-6 text-text-muted"><?= t('inst_auth.login_subtitle') ?></p>
                    </div>

                    <div class="mt-10">
                        <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">

                            <?php if ($error): ?>
                                <div class="mb-6 rounded-xl bg-red-900/20 border border-red-500/20 p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                                            <i class="fas fa-times text-red-400 text-sm"></i>
                                        </div>
                                        <p class="text-sm font-medium text-red-200"><?= htmlspecialchars($error) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('/institution-login') ?>" class="space-y-6">
                                <div>
                                    <label for="correo" class="block text-sm font-medium leading-6 text-gray-300"><?= t('inst_auth.email') ?></label>
                                    <div class="mt-2">
                                        <input id="correo" name="correo" type="email" autocomplete="email" required
                                            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                            class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                            placeholder="correo@institucion.es">
                                    </div>
                                </div>

                                <div>
                                    <label for="contrasena" class="block text-sm font-medium leading-6 text-gray-300"><?= t('inst_auth.password') ?></label>
                                    <div class="mt-2 relative">
                                        <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required
                                            class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 pr-10 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                            placeholder="••••••••••••">
                                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-300">
                                            <i class="fas fa-eye text-sm" id="toggle-icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="flex w-full justify-center items-center gap-2 rounded-xl bg-primary px-3 py-3 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                                    <i class="fas fa-sign-in-alt"></i> <?= t('inst_auth.login_btn') ?>
                                </button>
                            </form>

                            <div class="mt-6 pt-6 border-t border-gray-800">
                                <a href="<?= url('/login') ?>" class="flex items-center justify-center gap-2 text-sm text-gray-400 hover:text-primary transition-colors">
                                    <i class="fas fa-arrow-left text-xs"></i> <?= t('inst_auth.back_login') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <div class="absolute inset-0 flex flex-col justify-between p-12 text-white bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900">
                    <div>
                        <h1 class="text-5xl font-bold leading-tight tracking-tight">
                            <?= t('inst_auth.hero_1') ?><br>
                            <span class="text-primary"><?= t('inst_auth.hero_2') ?></span>
                        </h1>
                        <p class="mt-6 text-xl max-w-md text-gray-300"><?= t('inst_auth.hero_desc') ?></p>

                        <div class="mt-10 space-y-3 max-w-sm">
                            <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-chart-line text-blue-400 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-300"><?= t('inst_auth.feature_1') ?></p>
                            </div>
                            <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-users text-primary text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-300"><?= t('inst_auth.feature_2') ?></p>
                            </div>
                            <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-shield-alt text-purple-400 text-sm"></i>
                                </div>
                                <p class="text-sm text-gray-300"><?= t('inst_auth.feature_3') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        &copy; <?= date('Y') ?> Ride4Study. <?= t('login.rights') ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function togglePassword() {
                const input = document.getElementById('contrasena');
                const icon = document.getElementById('toggle-icon');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }
        </script>
    </body>
</html>
