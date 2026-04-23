<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= t('inst_auth.verify_title') ?? t('auth.2fa_title') ?> - Ride4Study</title>
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
        .code-input {
            width: 48px; height: 56px;
            text-align: center;
            font-size: 1.5rem; font-weight: 700;
            caret-color: #60a5fa;
            transition: all 0.2s;
        }
        .code-input:focus {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
            transform: translateY(-1px);
        }
        .code-input.filled {
            border-color: #60a5fa !important;
            background: rgba(96, 165, 250, 0.08) !important;
        }
        @media (min-width: 640px) { .code-input { width: 54px; height: 62px; font-size: 1.75rem; } }
    </style>
</head>
<body class="min-h-full text-text relative overflow-x-hidden">

    <div class="absolute inset-0 grid-pattern opacity-50 pointer-events-none"></div>
    <div class="absolute -top-40 -left-20 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-3xl pointer-events-none blob"></div>
    <div class="absolute -bottom-40 -right-20 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-3xl pointer-events-none blob" style="animation-delay: -7s;"></div>

    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 py-10">

        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2.5 mb-6 group">
            <img src="public/img/logo.png" alt="" aria-hidden="true" class="h-11 w-11 object-contain transition-transform group-hover:rotate-6">
            <span class="text-2xl font-extrabold tracking-tight text-white group-hover:text-primary transition-colors">Ride4Study</span>
        </a>

        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-gradient-to-r from-blue-500/15 to-indigo-500/10 border border-blue-400/30 rounded-full text-blue-300 text-[11px] font-bold uppercase tracking-widest mb-6 shadow-lg shadow-blue-500/10">
            <i class="fas fa-university text-[10px]" aria-hidden="true"></i> Panel institucional
        </div>

        <div class="w-full max-w-md">
            <div class="bg-gradient-to-b from-surface to-surface/80 rounded-2xl shadow-2xl ring-1 ring-white/10 backdrop-blur-sm p-7 sm:p-9">

                <div class="flex justify-center mb-5">
                    <div class="relative">
                        <div class="absolute inset-0 bg-blue-500/30 rounded-2xl blur-xl animate-pulse"></div>
                        <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-400/10 border border-blue-400/30 flex items-center justify-center shadow-lg">
                            <i class="fas fa-shield-halved text-blue-300 text-2xl" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-6">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight"><?= t('inst_auth.verify_title') ?? t('auth.2fa_title') ?></h1>
                    <p class="mt-3 text-base text-gray-400 leading-relaxed"><?= t('inst_auth.verify_subtitle') ?? t('auth.2fa_subtitle') ?></p>
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 rounded-full text-sm text-gray-300">
                        <i class="fas fa-envelope text-blue-400/70 text-[10px]" aria-hidden="true"></i>
                        <span><?= t('auth.2fa_check_email') ?></span>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/30 p-3.5 flex items-start gap-3" role="alert">
                        <div class="w-7 h-7 rounded-lg bg-red-500/20 flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation text-red-400 text-xs" aria-hidden="true"></i>
                        </div>
                        <p class="text-base font-medium text-red-200 leading-snug"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/institution-verify') ?>" id="verifyForm">
                    <div class="flex justify-center gap-2 sm:gap-2.5 mb-6">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <?php if ($i === 3): ?>
                                <div class="flex items-center px-1"><div class="w-2 h-0.5 bg-gray-600 rounded-full"></div></div>
                            <?php endif; ?>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   class="code-input rounded-xl border-2 border-gray-700 bg-secondary/70 text-white outline-none"
                                   data-index="<?= $i ?>" autocomplete="off">
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="code" id="codeInput">

                    <div class="flex items-center justify-center gap-1.5 mb-6">
                        <?php for ($j = 0; $j < 5; $j++): ?>
                            <div class="w-2 h-2 rounded-full <?= $j < $attemptsLeft ? 'bg-blue-400 shadow-sm shadow-blue-400/50' : 'bg-gray-700' ?> transition-colors"></div>
                        <?php endfor; ?>
                        <span class="text-xs text-gray-400 ml-2"><?= $attemptsLeft ?> <?= t('auth.2fa_attempts') ?></span>
                    </div>

                    <button type="submit" id="submitBtn" disabled
                            class="flex w-full justify-center items-center gap-2 rounded-xl bg-blue-500/30 px-3 py-3 text-base font-bold text-white/50 cursor-not-allowed transition-all">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        <span><?= t('auth.2fa_verify') ?></span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-white/5 text-center">
                    <a href="<?= url('/institution-login') ?>" class="inline-flex items-center gap-1.5 text-base text-gray-400 hover:text-blue-300 transition-colors">
                        <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i> <?= t('auth.2fa_back') ?>
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-[11px] text-gray-600">
                <i class="fas fa-shield-alt mr-1" aria-hidden="true"></i> Acceso protegido por verificación en 2 pasos
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('verifyForm');
            const hiddenInput = document.getElementById('codeInput');
            const submitBtn = document.getElementById('submitBtn');
            const enabledClasses = 'flex w-full justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 px-3 py-3 text-base font-bold text-white shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer';
            const disabledClasses = 'flex w-full justify-center items-center gap-2 rounded-xl bg-blue-500/30 px-3 py-3 text-base font-bold text-white/50 cursor-not-allowed transition-all';

            function updateButton() {
                let code = '';
                inputs.forEach(inp => code += inp.value);
                const complete = code.length === 6;
                submitBtn.disabled = !complete;
                submitBtn.className = complete ? enabledClasses : disabledClasses;
            }

            form.addEventListener('submit', function() {
                let code = '';
                inputs.forEach(input => code += input.value);
                hiddenInput.value = code;
            });

            inputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    const val = this.value.replace(/[^0-9]/g, '');
                    this.value = val.charAt(0) || '';
                    this.classList.toggle('filled', !!this.value);
                    if (val && index < inputs.length - 1) inputs[index + 1].focus();
                    updateButton();
                    let code = '';
                    inputs.forEach(inp => code += inp.value);
                    if (code.length === 6) { hiddenInput.value = code; form.submit(); }
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
                        for (let i = 0; i < 6; i++) { inputs[i].value = paste[i]; inputs[i].classList.add('filled'); }
                        updateButton();
                        hiddenInput.value = paste.substring(0, 6);
                        form.submit();
                    }
                });
                input.addEventListener('focus', function() { this.select(); });
            });

            inputs[0].focus();
        });
    </script>
</body>
</html>
