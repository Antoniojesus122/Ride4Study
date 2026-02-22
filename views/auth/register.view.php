<!DOCTYPE html>
  <html lang="es" class="h-full bg-secondary">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title>Crear Cuenta - Ride4Study</title>
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
              <a href="index.php" class="inline-flex items-center gap-2 group">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                      R
                  </div>
                  <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
              </a>
              <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white">Comienza tu viaje</h2>
              <p class="mt-2 text-sm leading-6 text-text-muted">
                ¿Ya eres miembro?
                <a href="login.php" class="font-semibold text-primary hover:text-primary-dark transition-colors">Inicia sesión aquí</a>
              </p>
            </div>

            <div class="mt-10">
              <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">
                <!-- Mensaje de error -->
                <?php if ($error): ?>
                  <div class="mb-6 rounded-lg bg-red-900/30 border border-red-500/30 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-red-200"><?= htmlspecialchars($error) ?></p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                  <div>
                    <label for="nombre" class="block text-sm font-medium leading-6 text-gray-300">Nombre completo</label>
                    <div class="mt-2">
                      <input id="nombre" name="nombre" type="text" autocomplete="name" required
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <div>
                    <label for="correo" class="block text-sm font-medium leading-6 text-gray-300">Correo electrónico</label>
                    <div class="mt-2">
                      <input id="correo" name="correo" type="email" autocomplete="email" required
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>
                  
                  <div>
                    <label for="telefono" class="block text-sm font-medium leading-6 text-gray-300">Teléfono <span class="text-gray-500 text-xs ml-1">(Opcional)</span></label>
                    <div class="mt-2">
                      <input id="telefono" name="telefono" type="tel" autocomplete="tel"
                        value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <div>
                    <label for="contrasena" class="block text-sm font-medium leading-6 text-gray-300">Contraseña</label>
                    <div class="mt-2 relative">
                      <input id="contrasena" name="contrasena" type="password" required
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 pr-10 transition-all">
                        <button type="button" onclick="togglePasswordVisibility('contrasena', 'icon-1')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors">
                          <i id="icon-1" class="fas fa-eye"></i>
                        </button>
                    </div>
                  </div>
                  
                  <div>
                    <label for="confirmar_contrasena" class="block text-sm font-medium leading-6 text-gray-300">Confirmar contraseña</label>
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
                        Acepto la 
                        <a href="privacy.php" target="_blank"
                          class="text-primary hover:text-primary-dark font-medium transition-colors">
                          Política de Privacidad
                        </a>
                        y las 
                        <a href="terms.php" target="_blank"
                          class="text-primary hover:text-primary-dark font-medium transition-colors">
                          Condiciones de Uso
                        </a>
                      </label>
                    </div>
                  </div>

                  <div class="pt-2">
                    <button type="submit" name="register"
                      class="flex w-full justify-center rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform hover:-translate-y-0.5">
                      Crear mi cuenta
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección de la imagen -->
        <div class="relative hidden w-0 flex-1 lg:block">
          <div class="absolute inset-0 flex flex-col justify-between p-12 text-white split-bg" style="background-image: url('public/img/imgRegister.jpg');">
            <div class="absolute inset-0 bg-gradient-to-r from-secondary/80 to-transparent"></div>
            <div class="z-10 relative">
              <h1 class="text-5xl font-bold leading-tight tracking-tight drop-shadow-lg">Explora nuevos<br>horizontes.</h1>
              <p class="mt-6 text-xl max-w-md text-gray-200 drop-shadow-md">Registro en segundos. Sin comisiones ocultas. Solo comunidad.</p>
            </div>
            <div class="z-10 relative text-sm text-gray-400">
              <div class="flex items-center gap-4">
                  <div class="flex -space-x-2">
                    <!-- Simulación de avatars -->
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gray-500"></div>
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gray-600"></div>
                    <div class="h-8 w-8 rounded-full ring-2 ring-secondary bg-gray-700"></div>
                  </div>
                  <span>+100 estudiantes registrados</span>
              </div>
              <div class="mt-4">
                &copy; <?= date('Y') ?> Ride4Study. Todos los derechos reservados.
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
      </script>
    </body>
  </html>