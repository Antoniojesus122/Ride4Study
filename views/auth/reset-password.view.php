<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= t('reset.title') ?></title>
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
<body class="min-h-full text-text relative overflow-x-hidden">

    <div class="absolute inset-0 grid-pattern opacity-50 pointer-events-none"></div>
    <div class="absolute -top-40 -left-20 w-[500px] h-[500px] bg-primary/10 rounded-full blur-3xl pointer-events-none blob"></div>
    <div class="absolute -bottom-40 -right-20 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-3xl pointer-events-none blob" style="animation-delay: -7s;"></div>

    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 py-10">

        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2.5 mb-8 group">
            <img src="public/img/logo.png" alt="" aria-hidden="true" class="h-11 w-11 object-contain transition-transform group-hover:rotate-6">
            <span class="text-2xl font-extrabold tracking-tight text-white group-hover:text-primary transition-colors">Ride4Study</span>
        </a>

        <div class="w-full max-w-md">
            <div class="bg-gradient-to-b from-surface to-surface/80 rounded-2xl shadow-2xl ring-1 ring-white/10 backdrop-blur-sm p-7 sm:p-9">

                <div class="flex justify-center mb-5">
                    <div class="relative">
                        <div class="absolute inset-0 bg-blue-500/30 rounded-2xl blur-xl"></div>
                        <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-400/10 border border-blue-400/30 flex items-center justify-center shadow-lg">
                            <i class="fas fa-shield-halved text-blue-300 text-2xl" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-6">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight"><?= t('reset.heading') ?></h1>
                    <p class="mt-3 text-base text-gray-400 leading-relaxed"><?= t('reset.desc') ?></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/30 p-3.5 flex items-start gap-3" role="alert">
                        <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation text-red-400 text-xs" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm font-medium text-red-200 leading-snug"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="mb-5 rounded-xl bg-green-500/10 border border-green-500/30 p-3.5 flex items-start gap-3" role="status">
                        <div class="w-7 h-7 rounded-lg bg-green-500/20 flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-green-400 text-xs" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm font-medium text-green-200 leading-snug"><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_GET['sent'])): ?>
                    <div class="mb-5 rounded-xl bg-blue-500/10 border border-blue-500/30 p-3.5 flex items-start gap-3" role="status">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                            <i class="fas fa-envelope text-blue-400 text-xs" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm font-medium text-blue-200 leading-snug"><?= t('reset.code_sent') ?></p>
                    </div>
                <?php endif; ?>

                <?php if (empty($success)): ?>
                    <form method="POST" action="<?= url('/reset-password') ?>" class="space-y-5">
                        <!-- Código OTP -->
                        <div>
                            <label class="block text-[13px] font-semibold text-gray-300 mb-3 text-center"><?= t('reset.code') ?></label>
                            <div id="otp" class="flex justify-center gap-2">
                                <?php for ($i = 0; $i < 6; $i++): ?>
                                    <input type="text" maxlength="1" inputmode="numeric" pattern="\d*"
                                           class="w-11 h-12 sm:w-12 sm:h-14 text-center text-xl font-bold font-mono rounded-xl bg-secondary/70 border-2 border-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/50 text-white transition-all otp-input">
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="code" id="code-hidden">
                            <p class="text-[11px] text-gray-500 mt-2.5 text-center"><?= t('reset.code_hint') ?></p>
                        </div>

                        <!-- Nueva contraseña -->
                        <div>
                            <label for="contrasena" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('reset.new_password') ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <i class="fas fa-lock text-gray-500 text-sm" aria-hidden="true"></i>
                                </div>
                                <input id="contrasena" name="contrasena" type="password" required minlength="6"
                                       class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-11 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                       placeholder="••••••••">
                                <button type="button" onclick="togglePasswordVisibility('contrasena','icon-pass')" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-200 transition-colors" aria-label="Mostrar/ocultar">
                                    <i id="icon-pass" class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                            <p id="passwordStrengthText" class="text-[11px] text-gray-500 mt-2"><?= t('reset.strength') ?> —</p>
                        </div>

                        <!-- Confirmar contraseña -->
                        <div>
                            <label for="confirmar_contrasena" class="block text-[13px] font-semibold text-gray-300 mb-2"><?= t('reset.confirm_password') ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <i class="fas fa-lock text-gray-500 text-sm" aria-hidden="true"></i>
                                </div>
                                <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required minlength="6"
                                       class="block w-full rounded-xl border-0 bg-secondary/70 py-2.5 pl-10 pr-11 text-white shadow-inner ring-1 ring-inset ring-gray-700/80 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm transition-all"
                                       placeholder="••••••••">
                                <button type="button" onclick="togglePasswordVisibility('confirmar_contrasena','icon-confirm')" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-200 transition-colors" aria-label="Mostrar/ocultar">
                                    <i id="icon-confirm" class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                            <p id="matchText" class="text-[11px] text-gray-500 mt-2"></p>
                        </div>

                        <button type="submit" class="group relative flex w-full justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-emerald-400 px-3 py-3 text-sm font-bold text-secondary shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 transition-all overflow-hidden">
                            <span class="relative z-10 flex items-center gap-2">
                                <i class="fas fa-check" aria-hidden="true"></i> <?= t('reset.submit') ?>
                            </span>
                            <span class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-primary opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="mt-6 pt-5 border-t border-white/5 flex items-center justify-center gap-4 text-sm">
                    <a href="<?= url('/forgot-password') ?>" class="text-gray-400 hover:text-primary transition-colors"><?= t('reset.request_again') ?></a>
                    <span class="text-gray-700">·</span>
                    <a href="<?= url('/login') ?>" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-primary transition-colors">
                        <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i> <?= t('reset.login') ?>
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-[11px] text-gray-600">
                &copy; <?= date('Y') ?> Ride4Study
            </p>
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll(".otp-input");
        const hiddenInput = document.getElementById("code-hidden");

        otpInputs.forEach((input, index) => {
            input.addEventListener("input", () => {
                input.value = input.value.replace(/\D/, "");
                if (input.value.length && index < otpInputs.length - 1) otpInputs[index + 1].focus();
                updateHiddenInput();
            });
            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && input.value === "" && index > 0) otpInputs[index - 1].focus();
            });
            input.addEventListener("paste", (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach((char, i) => { if (otpInputs[i]) otpInputs[i].value = char; });
                if (pasted.length) otpInputs[Math.min(pasted.length - 1, otpInputs.length - 1)].focus();
                updateHiddenInput();
            });
        });
        function updateHiddenInput() { hiddenInput.value = Array.from(otpInputs).map(i => i.value).join(''); }

        function togglePasswordVisibility(inputId, iconId) {
            const el = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!el) return;
            if (el.type === 'password') { el.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { el.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }

        document.getElementById('contrasena')?.addEventListener('input', function(){
            const v = this.value;
            let score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const texts = ['<?= t('reset.str_very_weak') ?>','<?= t('reset.str_weak') ?>','<?= t('reset.str_fair') ?>','<?= t('reset.str_good') ?>','<?= t('reset.str_excellent') ?>','<?= t('reset.str_very_strong') ?>'];
            document.getElementById('passwordStrengthText').textContent = '<?= t('reset.strength') ?> ' + texts[score];
        });

        document.getElementById('confirmar_contrasena')?.addEventListener('input', function(){
            const a = document.getElementById('contrasena')?.value || '';
            const b = this.value;
            const m = document.getElementById('matchText');
            if (b === '') { m.textContent=''; return; }
            if (a===b) { m.textContent='<?= t('reset.passwords_match') ?>'; m.className='text-[11px] text-green-400 mt-2'; }
            else { m.textContent='<?= t('reset.passwords_no_match') ?>'; m.className='text-[11px] text-red-400 mt-2'; }
        });
    </script>
</body>
</html>
