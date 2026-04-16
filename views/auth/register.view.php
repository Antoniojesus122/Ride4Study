<!DOCTYPE html>
  <html lang="<?= currentLang() ?>" class="h-full bg-secondary">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title><?= t('register.title') ?></title>
      <script src="https://cdn.tailwindcss.com"></script>
      <script src="public/js/tailwind-config.js"></script>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <link rel="stylesheet" href="public/css/accessibility.css">

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
              <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 group">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                      R
                  </div>
                  <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
              </a>
              <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white"><?= t('register.hero') ?></h2>
              <p class="mt-2 text-sm leading-6 text-text-muted">
                <?= t('register.has_account') ?>
                <a href="<?= url('/login') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors"><?= t('register.login_here') ?></a>
              </p>
            </div>

            <div class="mt-10">
              <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">
                <!-- Mensaje de error -->
                <?php if ($error): ?>
                  <div class="mb-6 rounded-lg bg-red-900/30 border border-red-500/30 p-4">
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

                <form method="POST" class="space-y-5">
                  <div>
                    <label for="nombre" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.name') ?></label>
                    <div class="mt-2">
                      <input id="nombre" name="nombre" type="text" autocomplete="name" required
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <div>
                    <label for="correo" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.email') ?></label>
                    <div class="mt-2">
                      <input id="correo" name="correo" type="email" autocomplete="email" required
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>
                  
                  <div>
                    <label for="telefono" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.phone') ?> <span class="text-gray-500 text-xs ml-1"><?= t('register.optional') ?></span></label>
                    <div class="mt-2">
                      <input id="telefono" name="telefono" type="tel" autocomplete="tel"
                        value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <!-- Institución (obligatorio, con autocompletado) -->
                  <div class="relative">
                    <label for="institucion" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.institution') ?></label>
                    <div class="mt-2">
                      <input id="institucion" name="institucion" type="text" required autocomplete="off"
                        value="<?= htmlspecialchars($_POST['institucion'] ?? '') ?>"
                        placeholder="<?= t('register.institution_placeholder') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                    <ul id="inst-autocomplete-list" class="hidden absolute z-30 w-full bg-gray-800 border border-gray-600 rounded-lg mt-1 shadow-xl max-h-48 overflow-y-auto"></ul>
                  </div>

                  <div>
                    <label for="contrasena" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.password') ?></label>
                    <div class="mt-2 relative">
                      <input id="contrasena" name="contrasena" type="password" required
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 pr-10 transition-all">
                        <button type="button" onclick="togglePasswordVisibility('contrasena', 'icon-1')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors">
                          <i id="icon-1" class="fas fa-eye"></i>
                        </button>
                    </div>
                  </div>
                  
                  <div>
                    <label for="confirmar_contrasena" class="block text-sm font-medium leading-6 text-gray-300"><?= t('register.confirm_password') ?></label>
                    <div class="mt-2 relative">
                      <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 pr-10 transition-all">
                        <button type="button" onclick="togglePasswordVisibility('confirmar_contrasena', 'icon-2')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors">
                          <i id="icon-2" class="fas fa-eye"></i>
                        </button>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="flex items-center h-5">
                      <input id="acepta_politicas" name="acepta_politicas" type="checkbox" required class="h-4 w-4 rounded border-gray-600 bg-secondary/50 text-primary focus:ring-primary focus:ring-2">
                    </div>

                    <div class="text-sm leading-6">
                      <label for="acepta_politicas" class="text-gray-300">
                        <?= t('register.accept_policy') ?>
                        <a href="<?= url('/privacy') ?>" target="_blank"
                          class="text-primary hover:text-primary-dark font-medium transition-colors">
                          <?= t('register.privacy_policy') ?>
                        </a>
                        <?= t('register.and_the') ?>
                        <a href="<?= url('/terms') ?>" target="_blank"
                          class="text-primary hover:text-primary-dark font-medium transition-colors">
                          <?= t('register.terms') ?>
                        </a>
                      </label>
                    </div>
                  </div>

                  <div class="pt-2">
                    <button type="submit" name="register"
                      class="flex w-full justify-center rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform hover:-translate-y-0.5">
                      <?= t('register.submit') ?>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección de la imagen -->
        <div class="relative hidden w-0 flex-1 lg:block">
          <div class="absolute inset-0 flex flex-col justify-between p-12 text-white" style="background-image: url('public/img/imgRegister.jpg'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-gradient-to-r from-secondary/90 via-secondary/60 to-secondary/30"></div>
            <div class="z-10 relative">
              <h1 class="text-5xl font-bold leading-tight tracking-tight drop-shadow-lg">
                <?= t('register.hero_title') ?>
              </h1>
              <p class="mt-6 text-xl max-w-md text-gray-300 drop-shadow-md"><?= t('register.hero_desc') ?></p>

              <div class="mt-10 space-y-3 max-w-sm">
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-user-check text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('register.hero_feature_1') ?></p>
                </div>
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-comments text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('register.hero_feature_2') ?></p>
                </div>
                <div class="flex items-center gap-3 bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                  <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-euro-sign text-primary text-sm" aria-hidden="true"></i>
                  </div>
                  <p class="text-sm text-gray-300"><?= t('register.hero_feature_3') ?></p>
                </div>
              </div>
            </div>
            <div class="z-10 relative text-sm text-gray-500">
              <div class="flex items-center gap-4">
                  <div class="flex -space-x-2">
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-primary/40 to-primary/20 flex items-center justify-center text-xs text-primary font-bold">A</div>
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-blue-500/40 to-blue-500/20 flex items-center justify-center text-xs text-blue-300 font-bold">M</div>
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gradient-to-br from-purple-500/40 to-purple-500/20 flex items-center justify-center text-xs text-purple-300 font-bold">L</div>
                  </div>
                  <span class="text-gray-400"><?= t('register.students_count') ?></span>
              </div>
              <div class="mt-4">
                &copy; <?= date('Y') ?> Ride4Study. <?= t('register.rights') ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
        function togglePasswordVisibility(inputId, iconId) {
          const passwordInput = document.getElementById(inputId);
          const passwordIcon = document.getElementById(iconId);
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
          } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
          }
        }

        const telefonoInput = document.getElementById('telefono');
          telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 9);
          });

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