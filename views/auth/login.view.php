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
              <a href="index.php" class="inline-flex items-center gap-2 group">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-secondary font-bold text-xl transition-transform group-hover:rotate-12 shadow-lg shadow-primary/20">
                      R
                  </div>
                  <span class="text-2xl font-bold tracking-tighter text-white group-hover:text-primary transition-colors">Ride4Study</span>
              </a>
              <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-white">¡Hola de nuevo!</h2>
              <p class="mt-2 text-sm leading-6 text-text-muted">
                ¿Aún no tienes cuenta?
                <a href="register.php" class="font-semibold text-primary hover:text-primary-dark transition-colors">Regístrate gratis</a>
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

                <form method="POST" action="login.php" class="space-y-6">
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
                      <a href="recuperar_contrasena.php" class="font-semibold text-primary hover:text-primary-dark transition-colors">¿Olvidaste tu contraseña?</a>
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
              
              <div class="mt-6">
                  <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                      <div class="w-full border-t border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                      <span class="bg-secondary px-2 text-gray-500">O continúa con</span>
                    </div>
                  </div>

                  <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="#" class="flex w-full items-center justify-center gap-3 rounded-lg bg-surface px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-gray-700 hover:bg-gray-700 focus-visible:ring-transparent transition-all">
                      <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M12.0003 20.45c-4.6667 0-8.45-3.7833-8.45-8.45 0-4.6667 3.7833-8.45 8.45-8.45 4.6667 0 8.45 3.7833 8.45 8.45 0 4.6667-3.7833 8.45-8.45 8.45Z" fill="#fff" fill-opacity="0" stroke="currentColor" stroke-width="1.5"></path>
                        <path d="M22.0003 12c-1.5714 0-2.8571-1.2857-4.2857-2.1429-1.4286-.8571-3.1429-.8571-4.2857 0-1.4286.8571-2.8571 2.1429-4.2857 2.1429C7.7146 12 5.5717 9.8571 5.5717 9.8571" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      <span class="text-xs sm:text-sm">Google</span>
                    </a>

                    <a href="#" class="flex w-full items-center justify-center gap-3 rounded-lg bg-surface px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-gray-700 hover:bg-gray-700 focus-visible:ring-transparent transition-all">
                      <svg class="h-5 w-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                      </svg>
                      <span class="text-xs sm:text-sm">Facebook</span>
                    </a>
                  </div>
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