    <?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit;
    }

    require_once __DIR__ . '/../includes/db.php';
    $userId = $_SESSION['user_id'];

    $successMessage = '';
    $errorMessage = '';

    // Manejar la actualización de los datos del perfil
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $newPhotoName = null;

        if (empty($nombre) || empty($correo)) {
            $errorMessage = 'El nombre y el correo electrónico son obligatorios.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'El formato del correo electrónico no es válido.';
        } else {
            try {
                // Obtener datos actuales para comparar y borrar foto antigua si es necesario (No funcional)
                $stmt = $pdo->prepare("SELECT correo, foto_perfil FROM usuarios WHERE idUsuario = ?");
                $stmt->execute([$userId]);
                $currentUserData = $stmt->fetch();

                // Verificar si el nuevo correo ya existe para otro usuario
                if ($correo !== $currentUserData['correo']) {
                    $stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE correo = ? AND idUsuario != ?");
                    $stmt->execute([$correo, $userId]);
                    if ($stmt->fetch()) {
                        $errorMessage = 'Ese correo electrónico ya está en uso por otra cuenta.';
                    }
                }

                // Procesar la subida de la foto de perfil si no hay errores previos
                if (empty($errorMessage) && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                    $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $maxSize = 2 * 1024 * 1024; // 2 MB

                    if (in_array($_FILES['foto_perfil']['type'], $allowedTypes) && $_FILES['foto_perfil']['size'] <= $maxSize) {
                        // Generar un nombre de archivo único
                        $newPhotoName = uniqid() . '-' . basename($_FILES['foto_perfil']['name']);
                        $uploadPath = $uploadDir . $newPhotoName;

                        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadPath)) {
                            // Borrar la foto antigua si existe
                            if (!empty($currentUserData['foto_perfil']) && file_exists($uploadDir . $currentUserData['foto_perfil'])) {
                                unlink($uploadDir . $currentUserData['foto_perfil']);
                            }
                        } else {
                            $errorMessage = "Hubo un error al subir la imagen.";
                            $newPhotoName = null; // Resetear si la subida falla
                        }
                    } else {
                        $errorMessage = "Archivo no válido. Solo se permiten imágenes (JPG, PNG, GIF) de hasta 2MB.";
                    }
                }

                // Actualizar la base de datos si no hay errores
                if (empty($errorMessage)) {
                    $sql = "UPDATE usuarios SET nombre = ?, correo = ?, telefono = ?, biografia = ?";
                    $params = [$nombre, $correo, $telefono, $biografia];
                    
                    if ($newPhotoName) {
                        $sql .= ", foto_perfil = ?";
                        $params[] = $newPhotoName;
                    }
                    
                    $sql .= " WHERE idUsuario = ?";
                    $params[] = $userId;

                    $updateStmt = $pdo->prepare($sql);
                    $updateStmt->execute($params);
                    
                    $successMessage = '¡Perfil actualizado correctamente!';
                }

            } catch (PDOException $e) {
                $errorMessage = 'Error al actualizar el perfil. Inténtalo de nuevo.';
            }
        }
    }

    // Manejar el cambio de contraseña
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'Por favor, completa todos los campos de contraseña.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'Las nuevas contraseñas no coinciden.';
        } elseif (strlen($newPassword) < 6) {
            $errorMessage = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } else {
            try {
                // Obtener la contraseña actual del usuario
                $stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE idUsuario = ?");
                $stmt->execute([$userId]);
                $userPass = $stmt->fetch();

                if ($userPass && password_verify($currentPassword, $userPass['contrasena'])) {
                    // La contraseña actual es correcta, proceder a actualizar
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE idUsuario = ?");
                    $updateStmt->execute([$hashedPassword, $userId]);
                    
                    $successMessage = '¡Contraseña cambiada con éxito!';
                } else {
                    $errorMessage = 'La contraseña actual es incorrecta.';
                }
            } catch (PDOException $e) {
                $errorMessage = 'Error al cambiar la contraseña. Inténtalo de nuevo.';
            }
        }
    }


    // Obtener los datos actuales del usuario para mostrarlos en el formulario
    try {
        $stmt = $pdo->prepare("SELECT nombre, correo, telefono, foto_perfil, biografia, estado_verificacion FROM usuarios WHERE idUsuario = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) {
            session_destroy();
            header('Location: ../index.php');
            exit;
        }
    } catch (PDOException $e) {
        die("Error al cargar los datos del perfil: " . htmlspecialchars($e->getMessage()));
    }
    ?>

    <!DOCTYPE html>
    <html lang="es" class="h-full bg-background">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Mi Perfil - Ride4Study</title>
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
        <!-- Menú de Navegación -->
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4 lg:px-6">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="dashboard.php" class="flex items-center gap-2 flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white">
                                <i class="fas fa-car-side text-lg"></i>
                            </div>
                            <span class="text-2xl font-bold text-secondary tracking-tighter">RIDE4STUDY</span>
                        </a>
                        <div class="hidden md:block">
                            <div class="flex items-baseline space-x-4">
                                <a href="dashboard.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
                                <a href="my-rides.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Mis Viajes</a>
                                <a href="messages.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Mensajes</a>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-text-muted">Hola, <span class="font-semibold text-text"><?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></span></span>
                            <a href="profile.php" class="relative">
                            <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center font-bold text-primary-dark ring-2 ring-primary-dark">
                                    <?php if (!empty($user['foto_perfil'])): ?>
                                        <img src="../assets/uploads/avatars/<?= htmlspecialchars($user['foto_perfil']) ?>" alt="Foto de perfil" class="rounded-full w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= strtoupper(substr($user['nombre'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <a href="../actions/logout_action.php" title="Cerrar sesión" class="text-text-muted hover:text-red-500">
                                <i class="fas fa-sign-out-alt fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Contenido -->
        <main class="py-10">
            <div class="container mx-auto px-4 lg:px-6 max-w-4xl">
                <div class="mb-8">
                    <h1 class="text-4xl font-extrabold tracking-tight text-text">Mi Perfil</h1>
                    <p class="mt-2 text-lg text-text-muted">Gestiona tu información y aumenta la confianza en la comunidad.</p>
                </div>

                <!-- Mensajes de Feedback -->
                <?php if ($successMessage): ?>
                    <div class="mb-6 rounded-md bg-green-50 p-4"><div class="flex"><div class="flex-shrink-0"><i class="fas fa-check-circle text-green-400"></i></div><div class="ml-3"><p class="text-sm font-medium text-green-800"><?= htmlspecialchars($successMessage) ?></p></div></div></div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div class="mb-6 rounded-md bg-red-50 p-4"><div class="flex"><div class="flex-shrink-0"><i class="fas fa-times-circle text-red-400"></i></div><div class="ml-3"><p class="text-sm font-medium text-red-800"><?= htmlspecialchars($errorMessage) ?></p></div></div></div>
                <?php endif; ?>

                <div class="space-y-8">
                    <!-- Card de Información Personal -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <h2 class="text-xl font-bold text-text mb-6 border-b pb-4">Información de la cuenta</h2>
                        <form method="POST" class="space-y-6" enctype="multipart/form-data">
                            <div class="flex items-center gap-6">
                                <div class="w-24 h-24 rounded-full bg-secondary/10 flex-shrink-0">
                                    <?php if (!empty($user['foto_perfil'])): ?>
                                        <img src="../assets/uploads/avatars/<?= htmlspecialchars($user['foto_perfil']) ?>" alt="Foto de perfil" class="rounded-full w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="w-full h-full flex items-center justify-center text-4xl font-bold text-secondary"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="foto_perfil" class="block text-sm font-medium text-text-muted mb-1">Foto de perfil</label>
                                    <input type="file" name="foto_perfil" id="foto_perfil" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/20 file:text-primary-dark hover:file:bg-primary/30"/>
                                    <p class="text-xs text-text-muted mt-1">PNG, JPG o GIF (Máx. 2MB).</p>
                                </div>
                            </div>

                            <div>
                                <label for="nombre" class="block text-sm font-medium text-text-muted mb-1">Nombre completo</label>
                                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="correo" class="block text-sm font-medium text-text-muted mb-1">Correo electrónico</label>
                                    <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($user['correo']) ?>" required class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                                </div>
                                <div>
                                    <label for="telefono" class="block text-sm font-medium text-text-muted mb-1">Teléfono <span class="text-xs">(Opcional)</span></label>
                                    <input type="tel" name="telefono" id="telefono" value="<?= htmlspecialchars($user['telefono']) ?>" class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                                </div>
                            </div>

                            <div>
                                <label for="biografia" class="block text-sm font-medium text-text-muted mb-1">Sobre mí</label>
                                <textarea name="biografia" id="biografia" rows="3" class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark" placeholder="Ej: Estudio 2º de DAW. Me gusta escuchar música pop en los viajes. ¡Soy bastante hablador!"><?= htmlspecialchars($user['biografia']) ?></textarea>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" name="update_profile" class="rounded-md bg-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-light">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>

                    <!-- Card de Verificación de Estudiante -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <h2 class="text-xl font-bold text-text mb-4">Verificación de Estudiante</h2>
                        <?php if ($user['estado_verificacion'] == 2): ?>
                            <div class="flex items-center gap-3 bg-green-50 p-4 rounded-md">
                                <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                <div>
                                    <h3 class="font-semibold text-green-800">¡Estudiante Verificado!</h3>
                                    <p class="text-sm text-green-700">Tu cuenta ha sido verificada. Ahora generas más confianza en la comunidad.</p>
                                </div>
                            </div>
                        <?php elseif ($user['estado_verificacion'] == 1): ?>
                            <div class="flex items-center gap-3 bg-yellow-50 p-4 rounded-md">
                                <i class="fas fa-hourglass-half text-2xl text-yellow-500"></i>
                                <div>
                                    <h3 class="font-semibold text-yellow-800">Verificación Pendiente</h3>
                                    <p class="text-sm text-yellow-700">Hemos recibido tus documentos. Nuestro equipo los revisará en las próximas 24-48 horas.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-3 bg-blue-50 p-4 rounded-md">
                                <i class="fas fa-shield-alt text-2xl text-blue-500"></i>
                                <div>
                                    <h3 class="font-semibold text-blue-800">Verifica tu cuenta para aumentar la confianza</h3>
                                    <p class="text-sm text-blue-700 mb-3">Los perfiles verificados tienen más posibilidades de encontrar compañeros de viaje.</p>
                                    <a href="verify-student.php" class="rounded-md bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-600">Verificar ahora</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card de Cambio de Contraseña -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <h2 class="text-xl font-bold text-text mb-6 border-b pb-4">Cambiar Contraseña</h2>
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-text-muted mb-1">Contraseña Actual</label>
                                <input type="password" name="current_password" id="current_password" required class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-text-muted mb-1">Nueva Contraseña</label>
                                    <input type="password" name="new_password" id="new_password" required class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-text-muted mb-1">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="confirm_password" id="confirm_password" required class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark">
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" name="change_password" class="rounded-md bg-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-light">Cambiar Contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    </body>
    </html>