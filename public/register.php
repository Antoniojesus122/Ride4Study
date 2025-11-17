  <?php
  session_start();

  // Si ya está logueado, redirigir a index.php, que gestionará el rol
  if (isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
  }

  $error = '';

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
      require_once __DIR__ . '/../includes/db.php';

      $nombre = trim($_POST['nombre'] ?? '');
      $correo = trim($_POST['correo'] ?? '');
      $telefono = trim($_POST['telefono'] ?? '');
      $contrasena = $_POST['contrasena'] ?? '';
      $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

      if (empty($nombre) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
          $error = 'Por favor, completa todos los campos obligatorios.';
      } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
          $error = 'El correo electrónico no es válido.';
      } elseif ($contrasena !== $confirmar_contrasena) {
          $error = 'Las contraseñas no coinciden.';
      } elseif (strlen($contrasena) < 6) {
          $error = 'La contraseña debe tener al menos 6 caracteres.';
      } else {
          try {
              $stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE correo = ?");
              $stmt->execute([$correo]);
              if ($stmt->fetch()) {
                  $error = 'Este correo electrónico ya está registrado.';
              } else {
                  $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
                  
                  // Asignar el rol de usuario estándar (2)
                  $idRol = 2;
                  
                  $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, idRol) VALUES (?, ?, ?, ?, ?)");
                  $stmt->execute([$nombre, $correo, $telefono, $hashedPassword, $idRol]);

                  $_SESSION['flash_message'] = [
                      'text' => '¡Registro completado! Ya puedes iniciar sesión con tu nueva cuenta.',
                      'type' => 'success'
                  ];
                  header('Location: login.php');
                  exit;
              }
          } catch (PDOException $e) {
              $error = 'Error al registrar el usuario. Por favor, inténtalo de nuevo más tarde.';
          }
      }
  }
  ?>

  <!DOCTYPE html>
  <html lang="es" class="h-full bg-white">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Crear Cuenta - Ride4Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#6EE7B7',
              'primary-dark': '#10B981',
              secondary: '#374151',
              'secondary-light': '#4B5563',
              background: '#F9FAFB',
              text: '#1F2937',
              'text-muted': '#6B7280',
            }
          }
        }
      }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      .split-bg {
        background-image: linear-gradient(to top, rgba(55, 65, 81, 0.8), rgba(55, 65, 81, 0.6)), url('../assets/img/img_login.jpg');
        background-size: cover;
        background-position: center;
      }
    </style>
  </head>
  <body class="h-full">

  <div class="flex min-h-full">
    <!-- Sección del formulario -->
    <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <div>
          <a href="../index.php" class="flex items-center gap-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white">
                  <i class="fas fa-car-side text-lg"></i>
              </div>
              <span class="text-2xl font-bold text-secondary tracking-tighter">RIDE4STUDY</span>
          </a>
          <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-text">Crea tu cuenta gratis</h2>
          <p class="mt-2 text-sm leading-6 text-text-muted">
            ¿Ya eres miembro?
            <a href="login.php" class="font-semibold text-primary-dark hover:text-primary-dark/80">Inicia sesión aquí</a>
          </p>
        </div>

        <div class="mt-10">
          <div>
            <!-- Mensaje de error -->
            <?php if ($error): ?>
              <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-400"></i>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($error) ?></p>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
              <div>
                <label for="nombre" class="block text-sm font-medium leading-6 text-text">Nombre completo</label>
                <div class="mt-2">
                  <input id="nombre" name="nombre" type="text" autocomplete="name" required
                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6">
                </div>
              </div>

              <div>
                <label for="correo" class="block text-sm font-medium leading-6 text-text">Correo electrónico</label>
                <div class="mt-2">
                  <input id="correo" name="correo" type="email" autocomplete="email" required
                    value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6">
                </div>
              </div>
              
              <div>
                <label for="telefono" class="block text-sm font-medium leading-6 text-text">Teléfono <span class="text-text-muted">(Opcional)</span></label>
                <div class="mt-2">
                  <input id="telefono" name="telefono" type="tel" autocomplete="tel"
                    value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6">
                </div>
              </div>

              <div>
                <label for="contrasena" class="block text-sm font-medium leading-6 text-text">Contraseña</label>
                <div class="mt-2 relative">
                  <input id="contrasena" name="contrasena" type="password" required
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6 pr-10">
                    <button type="button" onclick="togglePasswordVisibility('contrasena', 'icon-1')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                      <i id="icon-1" class="fas fa-eye"></i>
                    </button>
                </div>
              </div>
              
              <div>
                <label for="confirmar_contrasena" class="block text-sm font-medium leading-6 text-text">Confirmar contraseña</label>
                <div class="mt-2 relative">
                  <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6 pr-10">
                    <button type="button" onclick="togglePasswordVisibility('confirmar_contrasena', 'icon-2')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                      <i id="icon-2" class="fas fa-eye"></i>
                    </button>
                </div>
              </div>

              <div>
                <button type="submit" name="register"
                  class="flex w-full justify-center rounded-md bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-sm hover:bg-primary-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-dark transition-all">
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
      <div class="absolute inset-0 flex flex-col justify-between p-12 text-white split-bg">
        <div class="z-10">
          <h1 class="text-4xl font-bold leading-tight tracking-tight">Únete a la comunidad de viajeros inteligentes.</h1>
          <p class="mt-4 text-lg max-w-md opacity-90">Regístrate en menos de un minuto y empieza a publicar y buscar viajes hoy mismo.</p>
        </div>
        <div class="z-10 text-sm opacity-80">
          &copy; <?= date('Y') ?> Ride4Study. Todos los derechos reservados.
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
  </script>

  </body>
  </html>