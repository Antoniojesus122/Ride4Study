<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= t('reset.title') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="public/js/tailwind-config.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full text-text flex items-center justify-center">
  <div class="w-full max-w-md mx-4">
    <div class="bg-surface rounded-xl shadow-lg p-8 space-y-6">
      <div class="text-center">
        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 mb-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl">R</div>
          <span class="text-xl font-bold text-white">Ride4Study</span>
        </a>
        <h2 class="text-2xl font-semibold text-white"><?= t('reset.heading') ?></h2>
        <p class="text-sm text-text-muted mt-2"><?= t('reset.desc') ?></p>
      </div>

      <!-- Mensajes -->
      <?php if (!empty($error)): ?>
        <div class="p-3 bg-red-900/20 rounded border border-red-500/20 text-red-200 text-sm"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (!empty($success)): ?>
        <div class="p-3 bg-green-900/20 rounded border border-green-500/20 text-green-200 text-sm"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($_GET['sent'])): ?>
        <div class="p-3 bg-green-900/20 rounded border border-green-500/20 text-green-200 text-sm"><?= t('reset.code_sent') ?></div>
      <?php endif; ?>

      <!-- Formulario -->
      <?php if (empty($success)): ?>
      <form method="POST" action="<?= url('/reset-password') ?>" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2"><?= t('reset.code') ?></label>
            <div id="otp" class="flex justify-center gap-2">
                <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*"
                    class="w-12 h-12 text-center text-xl font-mono rounded-xl bg-secondary/50 border-2 border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary text-white placeholder:text-white/50 transition-all duration-200 otp-input">
                <?php endfor; ?>
            </div>
            <input type="hidden" name="code" id="code-hidden">
            <p class="text-xs text-text-muted mt-2 text-center"><?= t('reset.code_hint') ?></p>
        </div>

        <!-- Contraseña -->
        <div>
            <label for="contrasena" class="block text-sm font-medium text-gray-300 mt-4"><?= t('reset.new_password') ?></label>
            <div class="relative mt-1">
                <input id="contrasena" name="contrasena" type="password" required minlength="6"
                class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white pr-10">
                <button type="button" onclick="togglePasswordVisibility('contrasena','icon-pass')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                <i id="icon-pass" class="fas fa-eye"></i>
                </button>
            </div>
            <p id="passwordStrengthText" class="text-xs text-text-muted mt-2"><?= t('reset.strength') ?> —</p>
        </div>

        <!-- Confirmar contraseña -->
        <div>
            <label for="confirmar_contrasena" class="block text-sm font-medium text-gray-300 mt-4"><?= t('reset.confirm_password') ?></label>
            <div class="relative mt-1">
                <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required minlength="6"
                class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white pr-10">
                <button type="button" onclick="togglePasswordVisibility('confirmar_contrasena','icon-confirm')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                <i id="icon-confirm" class="fas fa-eye"></i>
                </button>
            </div>
            <p id="matchText" class="text-xs text-text-muted mt-2"></p>
        </div>

        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold text-secondary hover:bg-primary/90 transition">
                <i class="fas fa-check"></i> <?= t('reset.submit') ?>
                </button>
            </form>
            <?php endif; ?>

            <div class="text-center text-sm text-text-muted">
                <a href="<?= url('/forgot-password') ?>" class="underline"><?= t('reset.request_again') ?></a> · <a href="<?= url('/login') ?>" class="underline"><?= t('reset.login') ?></a>
            </div>
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
        });

        function updateHiddenInput() {
            hiddenInput.value = Array.from(otpInputs).map(i => i.value).join('');
        }

        // Visibilidad de la contraseña
        function togglePasswordVisibility(inputId, iconId) {
            const el = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!el) return;
            if (el.type === 'password') { el.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { el.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }

        // Fortaleza de la contraseña
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

        // Coincidencia de contraseñas
        document.getElementById('confirmar_contrasena')?.addEventListener('input', function(){
            const a = document.getElementById('contrasena')?.value || '';
            const b = this.value;
            const m = document.getElementById('matchText');
            if (b === '') { m.textContent=''; return; }
            if (a===b) { m.textContent='<?= t('reset.passwords_match') ?>'; m.className='text-xs text-green-500 mt-2'; }
            else { m.textContent='<?= t('reset.passwords_no_match') ?>'; m.className='text-xs text-red-500 mt-2'; }
        });
    </script>

</body>
</html>