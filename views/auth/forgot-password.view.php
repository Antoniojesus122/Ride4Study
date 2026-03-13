<!DOCTYPE html>
<html lang="<?= currentLang() ?>" class="h-full bg-secondary">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= t('forgot.title') ?></title>
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
        <h2 class="text-2xl font-semibold text-white"><?= t('forgot.heading') ?></h2>
        <p class="text-sm text-text-muted mt-2"><?= t('forgot.desc') ?></p>
      </div>

      <!-- Mensajes -->
      <?php if (!empty($error)): ?>
        <div class="p-3 bg-red-900/20 rounded border border-red-500/20 text-red-200 text-sm"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (!empty($_GET['sent'])): ?>
        <div class="p-3 bg-green-900/20 rounded border border-green-500/20 text-green-200 text-sm"><?= t('forgot.code_sent') ?></div>
      <?php endif; ?>

      <!-- Formulario -->
      <?php if (empty($success)): ?>
      <form method="POST" action="<?= url('/forgot-password') ?>" class="space-y-4">
        <div>
          <label for="correo" class="block text-sm font-medium text-gray-300"><?= t('forgot.email') ?></label>
          <input id="correo" name="correo" type="email" autocomplete="email" required
                 value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                 placeholder="<?= t('forgot.email_placeholder') ?>"
                 class="mt-1 block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white focus:ring-2 focus:ring-primary">
        </div>
        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold text-secondary hover:bg-primary/90 transition">
          <i class="fas fa-paper-plane"></i> <?= t('forgot.submit') ?>
        </button>
      </form>
      <?php endif; ?>

      <div class="text-center text-sm text-text-muted">
        <a href="<?= url('/login') ?>" class="underline"><?= t('forgot.back_login') ?></a> · <a href="<?= url('/register') ?>" class="underline"><?= t('forgot.create_account') ?></a>
      </div>
    </div>
  </div>
</body>
</html>
