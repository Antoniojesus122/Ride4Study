<?php
session_start();

// Si ya está logueado, redirigir a index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
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
        $error = 'Por favor, completa todos los campos.';
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
                $error = 'Este correo ya está registrado.';
            } else {
                $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, idRol) VALUES (?, ?, ?, ?, 2)");
                $stmt->execute([$nombre, $correo, $telefono, $hashedPassword]);

                // ✅ Redirigir al login con mensaje de éxito
                $_SESSION['flash_message'] = [
                    'text' => '¡Registro exitoso! Ahora puedes iniciar sesión.',
                    'type' => 'success'
                ];
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar el usuario. Inténtalo más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro - Ride4Study</title>
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

  <!-- Sección izquierda - Branding -->
  <div class="hidden w-1/2 bg-secondary p-12 text-white lg:flex lg:flex-col lg:justify-between relative" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%);">
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
        Únete a la comunidad de estudiantes más grande
      </h1>
      <p class="text-pretty text-lg opacity-90">
        Crea tu cuenta en minutos y comienza a compartir viajes con otros estudiantes de tu universidad.
      </p>
    </div>

    <div class="space-y-4 text-sm opacity-80">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">1</span>
        </div>
        <span>Crea tu perfil de estudiante</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">2</span>
        </div>
        <span>Busca o publica viajes</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <span class="text-lg">3</span>
        </div>
        <span>Conecta y viaja seguro</span>
      </div>
    </div>
  </div>

  <!-- Sección derecha - Formulario de registro -->
  <div class="flex w-full items-center justify-center p-8 lg:w-1/2">
    <div class="w-full max-w-md space-y-8">
      <div class="space-y-2 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-text">Crea tu cuenta</h2>
        <p class="text-text/70">Completa el formulario para comenzar</p>
      </div>

      <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-6">
        <div>
          <label for="nombre" class="block text-sm font-medium text-text">Nombre completo</label>
          <input
            type="text"
            name="nombre"
            id="nombre"
            required
            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary bg-white"
          />
        </div>

        <div>
          <label for="correo" class="block text-sm font-medium text-text">Correo electrónico</label>
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
          <label for="telefono" class="block text-sm font-medium text-text">Teléfono (opcional)</label>
          <input
            type="tel"
            name="telefono"
            id="telefono"
            value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary bg-white"
          />
        </div>

        <div>
          <label for="contrasena" class="block text-sm font-medium text-text">Contraseña</label>
          <input
            type="password"
            name="contrasena"
            id="contrasena"
            required
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary bg-white"
          />
        </div>

        <div>
          <label for="confirmar_contrasena" class="block text-sm font-medium text-text">Confirmar contraseña</label>
          <input
            type="password"
            name="confirmar_contrasena"
            id="confirmar_contrasena"
            required
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary bg-white"
          />
        </div>

        <button
          type="submit"
          name="register"
          class="w-full bg-primary hover:bg-hover text-text font-medium py-3 px-4 rounded-lg transition"
        >
          Crear cuenta
        </button>
      </form>

      <div class="text-center text-sm text-text/70">
        ¿Ya tienes una cuenta? 
        <a href="login.php" class="font-medium text-primary hover:text-hover">Inicia sesión</a>
      </div>
    </div>
  </div>
</body>
</html>