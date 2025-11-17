  <?php
  session_start();

  if (isset($_SESSION['user_id'])) {
      // Redirige al dashboard correspondiente en lugar del index
      require_once __DIR__ . '/../includes/db.php';
      try {
          $stmt = $pdo->prepare("SELECT idRol FROM usuarios WHERE idUsuario = ?");
          $stmt->execute([$_SESSION['user_id']]);
          $user = $stmt->fetch();
          if ($user) {
              $idRol = (int)$user['idRol'];
              if ($idRol === 1 || $idRol === 3) {
                  header('Location: ../admin/dashboard.php');
              } elseif ($idRol === 2 || $idRol === 4) {
                  header('Location: ../user/dashboard.php');
              } else {
                  header('Location: ../index.php');
              }
              exit;
          }
      } catch (PDOException $e) {
      }
  }


  $error = '';
  $success = '';

  if (isset($_SESSION['flash_message'])) {
      $success = $_SESSION['flash_message']['text'];
      unset($_SESSION['flash_message']);
  }


  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
      require_once __DIR__ . '/../includes/db.php';

      $correo = trim($_POST['correo'] ?? '');
      $contrasena = $_POST['contrasena'] ?? '';

      if (empty($correo) || empty($contrasena)) {
          $error = 'Por favor, completa todos los campos.';
      } else {
          try {
              $stmt = $pdo->prepare("SELECT idUsuario, correo, contrasena, idRol FROM usuarios WHERE correo = ?");
              $stmt->execute([$correo]);
              $usuario = $stmt->fetch();

              if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                  $_SESSION['user_id'] = $usuario['idUsuario'];
                  header('Location: ../index.php');
                  exit;
              } else {
                  $error = 'Correo o contraseña incorrectos.';
              }
          } catch (PDOException $e) {
              $error = 'Error al conectar con la base de datos.';
          }
      }
  }
  ?>

  <!DOCTYPE html>
  <html lang="es" class="h-full bg-white">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar Sesión - Ride4Study</title>
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
          <h2 class="mt-8 text-3xl font-bold leading-9 tracking-tight text-text">Bienvenido de nuevo</h2>
          <p class="mt-2 text-sm leading-6 text-text-muted">
            ¿Aún no tienes cuenta?
            <a href="register.php" class="font-semibold text-primary-dark hover:text-primary-dark/80">Regístrate gratis</a>
          </p>
        </div>

        <div class="mt-10">
          <div>
            <!-- Mensajes de error y éxito -->
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

            <?php if ($success): ?>
              <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($success) ?></p>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
              <div>
                <label for="correo" class="block text-sm font-medium leading-6 text-text">Correo Electrónico</label>
                <div class="mt-2">
                  <input id="correo" name="correo" type="email" autocomplete="email" required
                    value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6">
                </div>
              </div>

              <div>
                <label for="contrasena" class="block text-sm font-medium leading-6 text-text">Contraseña</label>
                <div class="mt-2 relative">
                  <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required
                    class="block w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm sm:leading-6 pr-10">
                  <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <i id="password-icon" class="fas fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div class="text-sm leading-6">
                  <a href="recuperar_contrasena.php" class="font-semibold text-primary-dark hover:text-primary-dark/80">¿Olvidaste tu contraseña?</a>
                </div>
              </div>

              <div>
                <button type="submit" name="login"
                  class="flex w-full justify-center rounded-md bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-secondary shadow-sm hover:bg-primary-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-dark transition-all">
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
      <div class="absolute inset-0 flex flex-col justify-between p-12 text-white split-bg">
        <div class="z-10">
          <h1 class="text-4xl font-bold leading-tight tracking-tight">Conecta, comparte y ahorra en cada viaje.</h1>
          <p class="mt-4 text-lg max-w-md opacity-90">Tu comunidad de estudiantes te espera para hacer el camino a clase más fácil, económico y divertido.</p>
        </div>
        <div class="z-10 text-sm opacity-80">
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