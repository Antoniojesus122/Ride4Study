<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$userId = $_SESSION['user_id'];

$errorMessage = '';
$successMessage = '';

// Manejar la subida del documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    if ($_FILES['documento']['error'] == 0) {
        $uploadDir = __DIR__ . '/../assets/uploads/verifications/';
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['documento']['type'], $allowedTypes) && $_FILES['documento']['size'] <= $maxSize) {
            $fileName = uniqid() . '-' . basename($_FILES['documento']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['documento']['tmp_name'], $uploadPath)) {
                try {
                    // Actualizar el estado del usuario a "Pendiente" y guardar el nombre del archivo
                    $stmt = $pdo->prepare("UPDATE usuarios SET documento_verificacion = ?, estado_verificacion = 1, nota_admin = NULL WHERE idUsuario = ?");
                    $stmt->execute([$fileName, $userId]);
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => '¡Documento subido! Lo revisaremos pronto.'];
                    header('Location: verify-student.php');
                    exit;
                } catch (PDOException $e) {
                    $errorMessage = "Error al guardar en la base de datos.";
                }
            } else {
                $errorMessage = "Hubo un error al mover el archivo.";
            }
        } else {
            $errorMessage = "Archivo no válido. Solo se permiten imágenes (JPG, PNG) o PDF de hasta 5MB.";
        }
    } else {
        $errorMessage = "Error al subir el archivo. Código: " . $_FILES['documento']['error'];
    }
}


// Obtener datos del usuario, incluyendo el estado de verificación
try {
    $stmt = $pdo->prepare("SELECT nombre, correo, foto_perfil, estado_verificacion, nota_admin FROM usuarios WHERE idUsuario = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) { session_destroy(); header('Location: ../index.php'); exit; }
} catch (PDOException $e) {
    die("Error al cargar los datos del perfil: " . htmlspecialchars($e->getMessage()));
}


if (isset($_SESSION['flash_message'])) {
    $successMessage = $_SESSION['flash_message']['text'];
    unset($_SESSION['flash_message']);
}

?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-background">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Verificar Identidad - Ride4Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6EE7B7', 'primary-dark': '#10B981', secondary: '#374151',
                        'secondary-light': '#4B5563', background: '#F9FAFB', text: '#1F2937',
                        'text-muted': '#6B7280',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full antialiased">

<div class="min-h-full">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="flex items-center gap-2 flex-shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white"><i class="fas fa-car-side text-lg"></i></div>
                        <span class="text-2xl font-bold text-secondary tracking-tighter">RIDE4STUDY</span>
                    </a>
                </div>
                <a href="profile.php" class="text-sm font-semibold text-text-muted hover:text-text">&larr; Volver a Mi Perfil</a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="py-10">
        <div class="container mx-auto px-4 lg:px-6 max-w-2xl">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-text">Verificación de Estudiante</h1>
                <p class="mt-2 text-lg text-text-muted">Aumenta la confianza en la comunidad verificando tu identidad.</p>
            </div>

             <?php if ($successMessage): ?>
                <div class="mb-6 rounded-md bg-green-50 p-4"><div class="flex"><div class="flex-shrink-0"><i class="fas fa-check-circle text-green-400"></i></div><div class="ml-3"><p class="text-sm font-medium text-green-800"><?= htmlspecialchars($successMessage) ?></p></div></div></div>
            <?php endif; ?>
             <?php if ($errorMessage): ?>
                <div class="mb-6 rounded-md bg-red-50 p-4"><div class="flex"><div class="flex-shrink-0"><i class="fas fa-times-circle text-red-400"></i></div><div class="ml-3"><p class="text-sm font-medium text-red-800"><?= htmlspecialchars($errorMessage) ?></p></div></div></div>
            <?php endif; ?>


            <div class="bg-white p-8 rounded-xl shadow-sm border">
                <!-- Estado Verificado -->
                <?php if ($user['estado_verificacion'] == 2): ?>
                    <div class="text-center">
                        <i class="fas fa-user-check text-6xl text-green-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-text">¡Tu cuenta está verificada!</h2>
                        <p class="text-text-muted mt-2">Gracias por ayudar a construir una comunidad más segura. Ya no necesitas hacer nada más.</p>
                    </div>
                <!-- Estado Pendiente -->
                <?php elseif ($user['estado_verificacion'] == 1): ?>
                    <div class="text-center">
                        <i class="fas fa-hourglass-half text-6xl text-yellow-500 mb-4"></i>
                        <h2 class="text-2xl font-bold text-text">Revisión en Proceso</h2>
                        <p class="text-text-muted mt-2">Hemos recibido tu documento y lo revisaremos en las próximas 24-48 horas. Te notificaremos cuando el proceso haya terminado.</p>
                    </div>
                <!-- No Verificado o Rechazado -->
                <?php else: ?>
                    <?php if (!empty($user['nota_admin'])): ?>
                    <div class="mb-6 rounded-md bg-yellow-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-info-circle text-yellow-400"></i></div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-yellow-800">Nota del administrador:</h3>
                                <p class="text-sm text-yellow-700 mt-1"><?= htmlspecialchars($user['nota_admin']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                
                    <h2 class="text-xl font-bold text-text">Sube tu documento</h2>
                    <p class="text-text-muted mt-2 mb-6">Sube una imagen o PDF de tu carnet de estudiante, matrícula o cualquier documento que muestre tu **nombre completo, la institución y el año académico actual**.</p>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label for="documento" class="block text-sm font-medium text-text-muted mb-1">Documento de verificación</label>
                            <input type="file" name="documento" id="documento" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/20 file:text-primary-dark hover:file:bg-primary/30"/>
                            <p class="text-xs text-text-muted mt-1">Archivos permitidos: JPG, PNG, PDF. Tamaño máximo: 5MB.</p>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="w-full justify-center rounded-md bg-secondary px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-secondary-light">Enviar para Revisión</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>