<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

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
<html lang="es">
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
            secondary: '#374151',
            background: '#F9FAF5',
            hover: '#10B981',
            text: '#1F2937'
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="flex min-h-screen bg-background">

  <!-- Sección izquierda - Branding con borde curvo -->
  <div class="hidden w-1/2 bg-secondary p-12 text-white lg:flex lg:flex-col lg:justify-between relative" style="background-color: #374151; clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%);">
    <a href="../index.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary text-white shadow-md">
        <i class="fas fa-car-side text-xl" aria-hidden="true"></i>
        <span class="sr-only">Logo Ride4Study</span>
      </div>
      <div>
        <span class="text-2xl font-bold tracking-tight">RIDE4STUDY</span>
        <span class="block text-sm opacity-80">Viajes compartidos para estudiantes</span>
      </div>
    </a>

    <div class="space-y-6">
      <h1 class="text-balance text-4xl font-bold leading-tight">
        Conecta con otros estudiantes y comparte el camino
      </h1>
      <p class="text-pretty text-lg opacity-90">
        Ahorra dinero, reduce emisiones y haz nuevos amigos en cada viaje. La forma más inteligente de llegar a clase.
      </p>
    </div>

    <div class="space-y-4 text-sm opacity-80">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">✓</span>
        </div>
        <span>Más de 10,000 estudiantes conectados</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">✓</span>
        </div>
        <span>Verificación de identidad universitaria</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">✓</span>
        </div>
        <span>Sistema de valoraciones y reseñas</span>
      </div>
    </div>
  </div>

  <!-- Sección derecha - Formulario -->
  <div class="flex w-full items-center justify-center p-8 lg:w-1/2">
    <div class="w-full max-w-md space-y-8">
      <div class="space-y-2 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-text">Bienvenido de nuevo</h2>
        <p class="text-text/70">Ingresa tus credenciales para acceder a tu cuenta</p>
      </div>

      <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm text-center">
          <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
      <?php endif; ?>

      <form method="POST" class="space-y-6">
        <div>
          <label for="correo" class="block text-sm font-medium text-text">Correo Electrónico</label>
          <input
            type="email"
            name="correo"
            id="correo"
            required
            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary bg-white"
          />
        </div>

        <div>
          <label for="contrasena" class="block text-sm font-medium text-text">Contraseña</label>
          <div class="relative mt-1">
            <input
              type="password"
              name="contrasena"
              id="contrasena"
              required
              class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary pr-12 bg-white"
            />
            <button
              type="button"
              onclick="togglePassword()"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
            >
              <span id="toggleIcon">🙈</span>
            </button>
          </div>
        </div>

        <button
          type="submit"
          name="login"
          class="w-full bg-primary hover:bg-hover text-text font-medium py-3 px-4 rounded-lg transition"
        >
          Iniciar Sesión
        </button>
      </form>

      <div class="text-center text-sm text-text/70">
        ¿No tienes una cuenta? 
        <a href="register.php" class="font-medium text-primary hover:text-hover">Regístrate aquí</a>
      </div>
      <div class="text-center">
        <a href="recuperar_contrasena.php" class="text-sm text-text/70 hover:text-hover">¿Has olvidado tu contraseña?</a>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('contrasena');
      const icon = document.getElementById('toggleIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '🐵';
      } else {
        input.type = 'password';
        icon.textContent = '🙈';
      }
    }
  </script>
</body>
</html>