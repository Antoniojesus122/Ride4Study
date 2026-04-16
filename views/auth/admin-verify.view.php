<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title><?= t('auth.2fa_title') ?> - Ride4Study</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <link rel="stylesheet" href="public/css/accessibility.css">
        <style>
            body { font-family: 'Inter', sans-serif; }
            .code-input {
                width: 44px;
                height: 52px;
                text-align: center;
                font-size: 1.25rem;
                font-weight: 700;
                caret-color: #34d399;
                transition: all 0.2s;
            }
            .code-input:focus {
                border-color: #34d399 !important;
                box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.2);
                transform: translateY(-1px);
            }
            .code-input.filled {
                border-color: #34d399 !important;
                background: rgba(52, 211, 153, 0.05) !important;
            }
            @media (min-width: 640px) {
                .code-input { width: 48px; height: 56px; font-size: 1.5rem; }
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 15px rgba(52, 211, 153, 0.1); }
                50% { box-shadow: 0 0 30px rgba(52, 211, 153, 0.2); }
            }
            .animate-fade-in { animation: fadeInUp 0.5s ease-out; }
            .animate-fade-in-delay { animation: fadeInUp 0.5s ease-out 0.15s both; }
            .icon-glow { animation: pulse-glow 3s ease-in-out infinite; }
        </style>
    </head>
    <body class="h-full text-text">
        <div class="flex min-h-full">
            <!-- Formulario -->
            <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-secondary">
                <div class="mx-auto w-full max-w-sm lg:w-96">

                    <!-- Logo -->
                    <div class="text-center lg:text-left animate-fade-in">
                        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                                R
                            </div>
                            <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
                        </a>
                        <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white"><?= t('auth.2fa_title') ?></h2>
                        <p class="mt-2 text-sm leading-6 text-text-muted"><?= t('auth.2fa_subtitle') ?></p>
                    </div>

                    <div class="mt-10 animate-fade-in-delay">
                        <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">

                            <!-- Icono de seguridad -->
                            <div class="text-center mb-8">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-primary/10 to-green-500/10 border border-primary/20 mb-4 icon-glow">
                                    <i class="fas fa-shield-alt text-primary text-3xl" aria-hidden="true"></i>
                                </div>
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                                    <i class="fas fa-envelope text-primary/60" aria-hidden="true"></i>
                                    <span><?= t('auth.2fa_check_email') ?></span>
                                </div>
                            </div>

                            <!-- Error -->
                            <?php if ($error): ?>
                            <div class="mb-6 rounded-xl bg-red-900/20 border border-red-500/20 p-4 animate-fade-in">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                                        <i class="fas fa-times text-red-400 text-sm" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-medium text-red-200"><?= htmlspecialchars($error) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('/admin-verify') ?>" id="verifyForm">
                                <!-- Inputs del codigo -->
                                <div class="flex justify-center gap-2.5 mb-8">
                                    <?php for ($i = 0; $i < 6; $i++): ?>
                                    <?php if ($i === 3): ?>
                                    <div class="flex items-center px-1">
                                        <div class="w-2 h-0.5 bg-gray-600 rounded-full"></div>
                                    </div>
                                    <?php endif; ?>
                                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                           class="code-input rounded-xl border-2 border-gray-700 bg-gray-800/50 text-white outline-none"
                                           data-index="<?= $i ?>" autocomplete="off">
                                    <?php endfor; ?>
                                </div>

                                <!-- Campo oculto -->
                                <input type="hidden" name="code" id="codeInput">

                                <!-- Intentos restantes -->
                                <div class="flex items-center justify-center gap-1.5 mb-6">
                                    <?php for ($j = 0; $j < 5; $j++): ?>
                                    <div class="w-2 h-2 rounded-full <?= $j < $attemptsLeft ? 'bg-primary' : 'bg-gray-700' ?> transition-colors"></div>
                                    <?php endfor; ?>
                                    <span class="text-xs text-gray-500 ml-2"><?= $attemptsLeft ?> <?= t('auth.2fa_attempts') ?></span>
                                </div>

                                <button type="submit" id="submitBtn" disabled
                                    class="flex w-full justify-center items-center gap-2 rounded-xl bg-primary/30 px-3 py-3 text-sm font-semibold leading-6 text-secondary/50 cursor-not-allowed transition-all">
                                    <i class="fas fa-lock text-xs" aria-hidden="true"></i>
                                    <span><?= t('auth.2fa_verify') ?></span>
                                </button>
                            </form>

                            <div class="mt-6 pt-6 border-t border-gray-800 text-center">
                                <a href="<?= url('/login') ?>" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-primary transition-colors">
                                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i> <?= t('auth.2fa_back') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagen lateral -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <div class="absolute inset-0 flex flex-col justify-between p-12 text-white" style="background-image: url('public/img/imgLogin.jpg'); background-size: cover; background-position: center;">
                    <div class="absolute inset-0 bg-gradient-to-r from-secondary/90 via-secondary/60 to-secondary/30"></div>
                    <div class="z-10 relative">
                        <h1 class="text-5xl font-bold leading-tight tracking-tight drop-shadow-lg">
                            <?= t('auth.2fa_hero_1') ?><br>
                            <span class="text-primary"><?= t('auth.2fa_hero_2') ?></span>
                        </h1>
                        <p class="mt-6 text-xl max-w-md text-gray-300 drop-shadow-md"><?= t('auth.2fa_hero_desc') ?></p>

                        <!-- Info cards -->
                        <div class="mt-10 space-y-3 max-w-sm">
                            <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-envelope text-primary text-sm" aria-hidden="true"></i>
                                </div>
                                <p class="text-sm text-gray-300"><?= t('auth.2fa_subtitle') ?></p>
                            </div>
                            <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-clock text-primary text-sm" aria-hidden="true"></i>
                                </div>
                                <p class="text-sm text-gray-300">10 min</p>
                            </div>
                        </div>
                    </div>
                    <div class="z-10 relative text-sm text-gray-500">
                        &copy; <?= date('Y') ?> Ride4Study. <?= t('login.rights') ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('verifyForm');
            const hiddenInput = document.getElementById('codeInput');
            const submitBtn = document.getElementById('submitBtn');

            // Actualizar estado del botón
            function updateButton() {
                let code = '';
                inputs.forEach(inp => code += inp.value);
                const complete = code.length === 6;
                submitBtn.disabled = !complete;
                if (complete) {
                    submitBtn.className = 'flex w-full justify-center items-center gap-2 rounded-xl bg-primary px-3 py-3 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5 cursor-pointer';
                } else {
                    submitBtn.className = 'flex w-full justify-center items-center gap-2 rounded-xl bg-primary/30 px-3 py-3 text-sm font-semibold leading-6 text-secondary/50 cursor-not-allowed transition-all';
                }
            }

            // Juntar dígitos al enviar
            form.addEventListener('submit', function() {
                let code = '';
                inputs.forEach(input => code += input.value);
                hiddenInput.value = code;
            });

            inputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val.charAt(0) || '';

                    // Toggle clase filled
                    this.classList.toggle('filled', !!this.value);

                    if (val && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }

                    updateButton();

                    // Auto-submit cuando se completan los 6
                    let code = '';
                    inputs.forEach(inp => code += inp.value);
                    if (code.length === 6) {
                        hiddenInput.value = code;
                        form.submit();
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        inputs[index - 1].classList.remove('filled');
                        updateButton();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                    if (paste.length >= 6) {
                        for (let i = 0; i < 6; i++) {
                            inputs[i].value = paste[i];
                            inputs[i].classList.add('filled');
                        }
                        updateButton();
                        hiddenInput.value = paste.substring(0, 6);
                        form.submit();
                    }
                });

                // Focus visual
                input.addEventListener('focus', function() {
                    this.select();
                });
            });

            inputs[0].focus();
        });
        </script>
    </body>
</html>
