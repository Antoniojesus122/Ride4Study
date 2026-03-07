<!DOCTYPE html>
  <html lang="es" class="h-full bg-secondary">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title>Iniciar Sesión - Ride4Study</title>
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
              <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 group">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                      R
                  </div>
                  <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
              </a>
              <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white">¡Hola de nuevo!</h2>
              <p class="mt-2 text-sm leading-6 text-text-muted">
                ¿Aún no tienes cuenta?
                <a href="<?= url('/register') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors">Regístrate gratis</a>
              </p>
            </div>

            <div class="mt-10">
              <div class="bg-surface px-6 py-8 shadow-2xl ring-1 ring-white/5 sm:rounded-xl sm:px-10">
                <!-- Mensajes de error y éxito -->
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

                <?php if ($success): ?>
                  <div class="mb-6 rounded-lg bg-green-900/30 border border-green-500/30 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-green-200"><?= htmlspecialchars($success) ?></p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/login') ?>" class="space-y-6">
                  <div>
                    <label for="correo" class="block text-sm font-medium leading-6 text-gray-300">Correo Electrónico</label>
                    <div class="mt-2">
                      <input id="correo" name="correo" type="email" autocomplete="email" required
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all">
                    </div>
                  </div>

                  <div>
                    <label for="contrasena" class="block text-sm font-medium leading-6 text-gray-300">Contraseña</label>
                    <div class="mt-2 relative">
                      <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required
                        class="block w-full rounded-lg border-0 bg-secondary/50 py-2.5 px-3 text-white shadow-sm ring-1 ring-inset ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 pr-10 transition-all">
                      <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors">
                        <i id="password-icon" class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="text-sm leading-6">
                      <a href="<?= url('/forgot-password') ?>" class="font-semibold text-primary hover:text-primary-dark transition-colors">¿Olvidaste tu contraseña?</a>
                    </div>
                  </div>

                  <div>
                    <button type="submit" name="login"
                      class="flex w-full justify-center rounded-lg bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-primary/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform hover:-translate-y-0.5">
                      Iniciar Sesión
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección de la imagen -->
        <div class="relative hidden w-0 flex-1 lg:block">
          <div class="absolute inset-0 flex flex-col justify-between p-12 text-white split-bg" style="background-image: url('public/img/imgLogin.jpg');">
            <div class="absolute inset-0 bg-gradient-to-r from-secondary/80 to-transparent"></div>
            <div class="z-10 relative">
              <h1 class="text-5xl font-bold leading-tight tracking-tight drop-shadow-lg">Tu viaje,<br>tu comunidad.</h1>
              <p class="mt-6 text-xl max-w-md text-gray-200 drop-shadow-md">Únete a la plataforma de carpooling estudiantil más segura y eficiente.</p>
            </div>
            <div class="z-10 relative text-sm text-gray-400">
              &copy; <?= date('Y') ?> Ride4Study. Todos los derechos reservados.
            </div>
          </div>
        </div>
      </div>

      <script>
        function togglePasswordVisibility() {
          const passwordInput = document.getElementById('contrasena');
          const passwordIcon = document.getElementById('password-icon');
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
      </script>
    </body>
  </html>