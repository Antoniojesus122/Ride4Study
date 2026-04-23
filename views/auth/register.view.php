<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title><?= t('register.title') ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="icon" href="public/favicon.ico" sizes="any">
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
            <div class="relative flex flex-1 flex-col justify-center px-4 py-10 sm:px-6 lg:flex-none lg:w-[560px] lg:px-16 bg-secondary overflow-hidden">

                <!-- Decoración sutil -->
                <div class="absolute inset-0 grid-pattern opacity-50 pointer-events-none"></div>
                <div class="absolute -top-40 -left-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl pointer-events-none blob"></div>
                <div class="absolute -bottom-40 -right-20 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl pointer-events-none blob" style="animation-delay: -7s;"></div>

                <div class="relative mx-auto w-full max-w-md">

                    <div class="text-center lg:text-left mb-6">
                        <!-- Logo -->
                        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2.5 group mb-5">
                            <img src="public/img/logo.png" alt="" aria-hidden="true" class="h-11 w-11 object-contain transition-transform group-hover:rotate-6">
                            <span class="text-2xl font-extrabold tracking-tight text-white group-hover:text-primary transition-colors">Ride4Study</span>
                        </a>

                        <h1 class="text-2xl lg:text-3xl font-extrabold leading-tight tracking-tight text-white"><?= t('register.hero') ?></h1>
                        <p class="mt-2.5 text-sm leading-6 text-gray-400">
                            <?= t('register.has_account') ?>
                            <a href="<?= url('/login') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('register.login_here') ?></a>
                        </p>
                    </div>

                    <div class="relative bg-gradient-to-b from-surface to-surface/80 rounded-2xl shadow-2xl ring-1 ring-white/10 backdrop-blur-sm p-6 sm:p-8">

                        <?php if ($error): ?>
                            <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/30 p-3.5 flex items-start gap-3" role="alert">
                                <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-exclamation text-red-400 text-xs" aria-hidden="true"></i>
                                </div>
                                <p class="text-sm font-medium text-red-200 leading-snug"><?= htmlspecialchars($error) ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">

                            <!-- Sección: Tu cuenta -->
                            <fieldset class="space-y-4">
                                <legend class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-primary mb-3">
                                    <i class="fas fa-user-circle text-xs" aria-hidden="true"></i>
                                    <?= t('register.section_account') ?>
                                </legend>

                                <div>
                                    <label for="nombre" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('register.name') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                            <i class="fas fa-user text-gray-500 text-sm" aria-hidden="true"></i>
                                        </div>
                                        <input id="nombre" name="nombre" type="text" autocomplete="name" required
                                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                               class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-3 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                               placeholder="Nombre y apellidos">
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="correo" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('register.email') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fas fa-envelope text-gray-500 text-sm" aria-hidden="true"></i>
                                            </div>
                                            <input id="correo" name="correo" type="email" autocomplete="email" required
                                                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                                   class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-3 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                                   placeholder="tu@email.com">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="telefono" class="block text-[13px] font-semibold text-gray-300 mb-2">
                                            <?= t('register.phone') ?> <span class="text-gray-500 text-[10px] font-normal ml-1"><?= t('register.optional') ?></span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fas fa-phone text-gray-500 text-sm" aria-hidden="true"></i>
                                            </div>
                                            <input id="telefono" name="telefono" type="tel" autocomplete="tel"
                                                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                                                   class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-3 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                                   placeholder="600000000">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Separador -->
                            <div class="border-t border-white/5"></div>

                            <!-- Sección: Institución -->
                            <fieldset>
                                <legend class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-primary mb-3">
                                    <i class="fas fa-university text-xs" aria-hidden="true"></i>
                                    <?= t('register.section_institution') ?>
                                </legend>
                                <div class="relative">
                                    <label for="institucion" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('register.institution') ?></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                            <i class="fas fa-search text-gray-500 text-sm" aria-hidden="true"></i>
                                        </div>
                                        <input id="institucion" name="institucion" type="text" required autocomplete="off"
                                               value="<?= htmlspecialchars($_POST['institucion'] ?? '') ?>"
                                               placeholder="<?= t('register.institution_placeholder') ?>"
                                               class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-3 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all">
                                    </div>
                                    <ul id="inst-autocomplete-list" class="hidden absolute z-30 w-full bg-gray-800 border border-gray-700 rounded-xl mt-1 shadow-2xl max-h-48 overflow-y-auto"></ul>
                                </div>
                            </fieldset>

                            <!-- Separador -->
                            <div class="border-t border-white/5"></div>

                            <!-- Sección: Seguridad -->
                            <fieldset class="space-y-4">
                                <legend class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-primary mb-3">
                                    <i class="fas fa-lock text-xs" aria-hidden="true"></i>
                                    <?= t('register.section_security') ?>
                                </legend>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="contrasena" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('register.password') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fas fa-lock text-gray-500 text-sm" aria-hidden="true"></i>
                                            </div>
                                            <input id="contrasena" name="contrasena" type="password" required autocomplete="new-password"
                                                   class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-11 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                                   placeholder="••••••••">
                                            <button type="button" onclick="togglePasswordVisibility('contrasena', 'icon-1')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-200 transition-colors" aria-label="<?= t('a11y.toggle_password') ?? 'Toggle password' ?>">
                                                <i id="icon-1" class="fas fa-eye text-sm" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="confirmar_contrasena" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('register.confirm_password') ?></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fas fa-lock text-gray-500 text-sm" aria-hidden="true"></i>
                                            </div>
                                            <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required autocomplete="new-password"
                                                   class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-11 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                                   placeholder="••••••••">
                                            <button type="button" onclick="togglePasswordVisibility('confirmar_contrasena', 'icon-2')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-200 transition-colors" aria-label="<?= t('a11y.toggle_password') ?? 'Toggle password' ?>">
                                                <i id="icon-2" class="fas fa-eye text-sm" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fuerza de contraseña -->
                                <div id="pw-strength-wrap" class="hidden" aria-live="polite">
                                    <div class="flex gap-1.5" role="presentation">
                                        <div class="pw-bar h-1 flex-1 rounded-full bg-gray-700 transition-colors"></div>
                                        <div class="pw-bar h-1 flex-1 rounded-full bg-gray-700 transition-colors"></div>
                                        <div class="pw-bar h-1 flex-1 rounded-full bg-gray-700 transition-colors"></div>
                                        <div class="pw-bar h-1 flex-1 rounded-full bg-gray-700 transition-colors"></div>
                                    </div>
                                    <p id="pw-strength-label" class="mt-1.5 text-[11px] text-gray-400"></p>
                                </div>

                                <p id="pw-match-label" class="hidden text-[11px]" aria-live="polite"></p>
                            </fieldset>

                            <!-- Checkbox políticas -->
                            <div class="flex items-start gap-3 pt-1">
                                <div class="flex items-center h-5">
                                    <input id="acepta_politicas" name="acepta_politicas" type="checkbox" required
                                           class="h-4 w-4 rounded border-gray-600 bg-secondary/70 text-primary focus:ring-primary focus:ring-2">
                                </div>
                                <div class="text-[13px] leading-5">
                                    <label for="acepta_politicas" class="text-gray-300">
                                        <?= t('register.accept_policy') ?>
                                        <a href="<?= url('/privacy') ?>" target="_blank" class="text-primary hover:text-primary-dark font-semibold transition-colors">
                                            <?= t('register.privacy_policy') ?>
                                        </a>
                                        <?= t('register.and_the') ?>
                                        <a href="<?= url('/terms') ?>" target="_blank" class="text-primary hover:text-primary-dark font-semibold transition-colors">
                                            <?= t('register.terms') ?>
                                        </a>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" name="register"
                                    class="group relative flex w-full justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-emerald-400 px-3 py-3 text-sm font-bold text-secondary shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all overflow-hidden">
                                <span class="relative z-10 flex items-center gap-2">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> <?= t('register.submit') ?>
                                </span>
                                <span class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-primary opacity-0 group-hover:opacity-100 transition-opacity"></span>
                            </button>
                        </form>
                    </div>

                    <p class="lg:hidden mt-6 text-center text-[11px] text-gray-600">
                        &copy; <?= date('Y') ?> Ride4Study
                    </p>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- PANEL DERECHO: HERO VISUAL                                       -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="relative hidden w-0 flex-1 lg:block overflow-hidden" aria-hidden="true">
                <div class="absolute inset-0" style="background-image: url('public/img/imgRegister.jpg'); background-size: cover; background-position: center;"></div>
                <!-- Overlay oscuro neutro -->
                <div class="absolute inset-0 bg-secondary/70"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-secondary/80 via-secondary/30 to-transparent"></div>

                <div class="relative z-10 flex flex-col justify-between h-full p-12 xl:p-16 text-white">

                    <div>
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white/90 text-xs font-bold uppercase tracking-wider shadow-lg">
                            <i class="fas fa-star" aria-hidden="true"></i> Únete gratis
                        </div>
                    </div>

                    <div class="max-w-lg">
                        <h2 class="text-5xl xl:text-6xl font-extrabold leading-[1.05] tracking-tight drop-shadow-2xl text-white">
                            <?= t('register.hero_title') ?>
                        </h2>
                        <p class="mt-5 text-lg text-gray-200/90 leading-relaxed drop-shadow"><?= t('register.hero_desc') ?></p>

                        <div class="mt-10 space-y-3">
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/40 to-emerald-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-user-check text-primary text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('register.hero_feature_1') ?></p>
                            </div>
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400/40 to-blue-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-comments text-blue-200 text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('register.hero_feature_2') ?></p>
                            </div>
                            <div class="group flex items-center gap-4 bg-white/8 hover:bg-white/12 backdrop-blur-lg rounded-2xl px-5 py-3.5 border border-white/15 shadow-xl transition-all hover:translate-x-1">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-400/40 to-emerald-600/20 flex items-center justify-center shrink-0 shadow-inner ring-1 ring-white/10">
                                    <i class="fas fa-euro-sign text-green-200 text-base" aria-hidden="true"></i>
                                </div>
                                <p class="text-[15px] font-semibold text-white leading-tight"><?= t('register.hero_feature_3') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-400/80">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-primary/40 to-primary/20 flex items-center justify-center text-xs text-primary font-bold">A</div>
                                <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-blue-500/40 to-blue-500/20 flex items-center justify-center text-xs text-blue-300 font-bold">M</div>
                                <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-purple-500/40 to-purple-500/20 flex items-center justify-center text-xs text-purple-300 font-bold">L</div>
                            </div>
                            <span class="text-gray-300 font-medium"><?= t('register.students_count') ?></span>
                        </div>
                        <p>&copy; <?= date('Y') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function togglePasswordVisibility(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }

            const telefonoInput = document.getElementById('telefono');
            telefonoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 9);
            });

            // Indicador de fuerza de contraseña
            const pwInput = document.getElementById('contrasena');
            const pwConfirm = document.getElementById('confirmar_contrasena');
            const pwWrap = document.getElementById('pw-strength-wrap');
            const pwBars = pwWrap.querySelectorAll('.pw-bar');
            const pwLabel = document.getElementById('pw-strength-label');
            const matchLabel = document.getElementById('pw-match-label');

            const STRENGTH_LABELS = [
                '',
                <?= json_encode(t('register.password_strength_weak')) ?>,
                <?= json_encode(t('register.password_strength_fair')) ?>,
                <?= json_encode(t('register.password_strength_good')) ?>,
                <?= json_encode(t('register.password_strength_strong')) ?>,
            ];
            const STRENGTH_COLORS = ['', 'bg-red-500', 'bg-amber-500', 'bg-lime-500', 'bg-green-500'];
            const STRENGTH_TEXT = ['text-gray-400', 'text-red-400', 'text-amber-400', 'text-lime-400', 'text-green-400'];

            function calcStrength(pw) {
                let score = 0;
                if (pw.length >= 8) score++;
                if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
                if (/\d/.test(pw)) score++;
                if (/[^A-Za-z0-9]/.test(pw)) score++;
                return score;
            }

            function renderStrength() {
                const v = pwInput.value;
                if (!v) { pwWrap.classList.add('hidden'); return; }
                pwWrap.classList.remove('hidden');
                const s = calcStrength(v);
                pwBars.forEach((bar, i) => {
                    bar.className = 'pw-bar h-1 flex-1 rounded-full transition-colors ' + (i < s ? STRENGTH_COLORS[s] : 'bg-gray-700');
                });
                pwLabel.className = 'mt-1.5 text-[11px] ' + STRENGTH_TEXT[s];
                pwLabel.textContent = STRENGTH_LABELS[s] || '';
            }

            function renderMatch() {
                const a = pwInput.value, b = pwConfirm.value;
                if (!b) { matchLabel.classList.add('hidden'); return; }
                matchLabel.classList.remove('hidden');
                if (a === b) {
                    matchLabel.className = 'text-[11px] text-green-400 flex items-center gap-1';
                    matchLabel.innerHTML = '<i class="fas fa-check-circle" aria-hidden="true"></i> <?= htmlspecialchars(t('register.password_match'), ENT_QUOTES) ?>';
                } else {
                    matchLabel.className = 'text-[11px] text-red-400 flex items-center gap-1';
                    matchLabel.innerHTML = '<i class="fas fa-times-circle" aria-hidden="true"></i> <?= htmlspecialchars(t('register.password_nomatch'), ENT_QUOTES) ?>';
                }
            }

            pwInput.addEventListener('input', () => { renderStrength(); renderMatch(); });
            pwConfirm.addEventListener('input', renderMatch);

            const checkbox = document.getElementById('acepta_politicas');
            const submitBtn = document.querySelector('button[name="register"]');

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            // Autocompletado de instituciones
            const instInput = document.getElementById('institucion');
            const instList = document.getElementById('inst-autocomplete-list');
            let instDebounce;

            instInput.addEventListener('input', function() {
                clearTimeout(instDebounce);
                const q = this.value.trim();
                if (q.length < 2) { instList.classList.add('hidden'); instList.innerHTML = ''; return; }

                instDebounce = setTimeout(() => {
                    fetch('<?= url('/api/instituciones-search') ?>?q=' + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(data => {
                            instList.innerHTML = '';
                            if (!data.length) { instList.classList.add('hidden'); return; }
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.nombre;
                                li.className = 'px-4 py-2.5 text-sm text-gray-300 cursor-pointer border-b border-gray-700/50 last:border-0 hover:bg-primary/10 transition-colors';
                                li.addEventListener('click', () => {
                                    instInput.value = item.nombre;
                                    instList.classList.add('hidden');
                                });
                                instList.appendChild(li);
                            });
                            instList.classList.remove('hidden');
                        })
                        .catch(() => instList.classList.add('hidden'));
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!instInput.contains(e.target) && !instList.contains(e.target)) {
                    instList.classList.add('hidden');
                }
            });
        </script>
    </body>
</html>
