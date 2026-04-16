<!DOCTYPE html>
  <html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title><?= t('login.title') ?></title>
      <script src="https://cdn.tailwindcss.com"></script>
      <script src="public/js/tailwind-config.js"></script>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      
      <style>
          body { font-family: 'Inter', sans-serif; }
          .split-bg {
            background-image: linear-gradient(to top, rgba(17, 24, 39, 0.9), rgba(17, 24, 39, 0.7)), url('assets/img/img_login.jpg');
            background-size: cover;
            background-position: center;
          }
      </style>
    </head>
    <body class="h-full text-text">
      <div class="flex min-h-full">
        <!-- Sección del formulario -->
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-secondary">
          <div class="mx-auto w-full max-w-sm lg:w-96">
            <div class="text-center lg:text-left">
              <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 group" aria-label="<?= t('a11y.home') ?>">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20" aria-hidden="true">
                      R
                  </div>
                  <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
              </a>
              <h1 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white"><?= t('login.hello') ?></h1>
              <p class="mt-2 text-sm leading-6 text-text-muted">
                <?= t('login.no_account') ?>
                <a href="<?= url('/register') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('login.register_free') ?></a>
              </p>
            </div>

            <main id="main-content" tabindex="-1" class="mt-10">
              <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">
                <!-- Mensajes de error y éxito -->
                <?php if ($error): ?>
                  <div class="mb-6 rounded-lg bg-red-900/30 border border-red-500/30 p-4" role="alert" aria-live="assertive">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400" aria-hidden="true"></i>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-red-200"><?= htmlspecialchars($error) ?></p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($success): ?>
                  <div class="mb-6 rounded-lg bg-green-900/30 border border-green-500/30 p-4" role="status" aria-live="polite">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400" aria-hidden="true"></i>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-green-200"><?= htmlspecialchars($success) ?></p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/login') ?>" class="space-y-6" aria-labelledby="login-form-title" novalidate>
                  <h2 id="login-form-title" class="sr-only"><?= t('login.submit') ?></h2>
                  <div>
                    <label for="correo" class="block text-sm font-medium leading-6 text-gray-300"><?= t('login.email') ?> <span class="text-red-400" aria-hidden="true">*</span></label>
                    <div class="mt-2">
                      <input id="correo" name="correo" type="email" autocomplete="email" required aria-required="true" inputmode="email"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        <?= $error ? 'aria-invalid="true"' : '' ?>
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <div>
                    <label for="contrasena" class="block text-sm font-medium leading-6 text-gray-300"><?= t('login.password') ?> <span class="text-red-400" aria-hidden="true">*</span></label>
                    <div class="mt-2 relative">
                      <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required aria-required="true"
                        <?= $error ? 'aria-invalid="true"' : '' ?>
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 pr-10 transition-all">
                      <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors" aria-label="<?= t('a11y.toggle_password') ?>" aria-pressed="false" id="toggle-password-btn">
                        <i id="password-icon" class="fas fa-eye" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="text-sm leading-6">
                      <a href="<?= url('/forgot-password') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('login.forgot') ?></a>
                    </div>
                  </div>

                  <div>
                    <button type="submit" name="login"
                      class="flex w-full justify-center rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform hover:-translate-y-0.5">
                      <?= t('login.submit') ?>
                    </button>
                  </div>
                </form>

                <div class="mt-6 text-center">
                  <a href="<?= url('/institution-login') ?>" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                    <i class="fas fa-university" aria-hidden="true"></i>
                    <?= t('login.institution_access') ?>
                  </a>
                </div>
              </div>
            </main>
          </div>
        </div>

        <!-- Sección de la imagen -->
        <div class="relative hidden w-0 flex-1 lg:block" aria-hidden="true">
          <div class="absolute inset-0 flex flex-col justify-between p-12 text-white" style="background-image: url('public/img/imgLogin.jpg'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-gradient-to-r from-secondary/90 via-secondary/60 to-secondary/30"></div>
            <div class="z-10 relative">
              <p class="text-5xl font-bold leading-tight tracking-tight drop-shadow-lg">
                <?= t('login.hero_1') ?><br>
                <span class="text-primary"><?= t('login.hero_2') ?></span>
              </p>
              <p class="mt-6 text-xl max-w-md text-gray-300 drop-shadow-md"><?= t('login.hero_desc') ?></p>

              <div class="mt-10 space-y-3 max-w-sm">
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-car text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('login.hero_feature_1') ?></p>
                </div>
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-shield-alt text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('login.hero_feature_2') ?></p>
                </div>
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-leaf text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('login.hero_feature_3') ?></p>
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
        function togglePasswordVisibility() {
          const passwordInput = document.getElementById('contrasena');
          const passwordIcon = document.getElementById('password-icon');
          const toggleBtn = document.getElementById('toggle-password-btn');
          const isHidden = passwordInput.type === 'password';
          passwordInput.type = isHidden ? 'text' : 'password';
          passwordIcon.classList.toggle('fa-eye', !isHidden);
          passwordIcon.classList.toggle('fa-eye-slash', isHidden);
          if (toggleBtn) toggleBtn.setAttribute('aria-pressed', String(isHidden));
        }
      </script>
    </body>
  </html>