<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$userId = $_SESSION['user_id'];

try {
    // Obtener datos del usuario
    $stmt = $pdo->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../index.php?error=usuario_no_existe');
        exit;
    }

    // Obtener los anuncios propios del usuario
    $stmtPropios = $pdo->prepare("
        SELECT a.*, lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino, 
               u.nombre AS nombreUsuario, u.correo AS correoUsuario
        FROM anuncios a
        JOIN localidades lo ON a.origen = lo.idLocalidad
        JOIN localidades ld ON a.destino = ld.idLocalidad
        JOIN usuarios u ON a.idUsuario = u.idUsuario
        WHERE a.idUsuario = ?
        ORDER BY a.fechaPublicacion DESC
    ");
    $stmtPropios->execute([$userId]);
    $anunciosPropios = $stmtPropios->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar los viajes: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mis Viajes - Ride4Study</title>
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
    <script src="../assets/js/anuncio-popup.js" defer></script>
</head>
<body class="min-h-screen bg-background">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <a href="dashboard.php" class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary text-white shadow-md">
                    <i class="fas fa-car-side text-xl" aria-hidden="true"></i>
                    <span class="sr-only">Logo Ride4Study</span>
                </div>
                <div class="flex flex-col leading-none">
                    <span class="text-lg font-bold tracking-tight text-text">RIDE4STUDY</span>
                    <span class="text-[10px] leading-none text-text/60">Viajes compartidos para estudiantes</span>
                </div>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="dashboard.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Dashboard</a>
                <a href="my-rides.php" class="px-3 py-2 text-sm font-medium text-text bg-primary/10 rounded-md">Mis Viajes</a>
                <a href="messages.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Mensajes</a>
                <a href="profile.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Perfil</a>
                <a href="../actions/logout_action.php" class="px-3 py-2 text-sm font-medium text-text hover:text-red-500 transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="mb-8 space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-text">Mis Viajes</h1>
            <p class="text-text/70">Gestiona tus anuncios y participaciones en viajes</p>
        </div>

        <!-- Mis Anuncios -->
        <div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-text">Mis Anuncios</h2>
                    <a href="post/create.php" class="px-4 py-2 bg-primary text-text text-sm font-medium rounded-lg hover:bg-hover transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nuevo anuncio
                    </a>
                </div>

                <?php if (!empty($anunciosPropios)): ?>
                    <div class="space-y-4">
                        <?php foreach ($anunciosPropios as $anuncio): ?>
                            <div class="p-4 bg-background rounded-xl hover:border-primary/20 border border-transparent transition-colors">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-3 py-1.5 text-xs font-medium rounded-full <?= $anuncio['tipo'] === 'ofrezco' ? 'bg-primary/10 text-text' : 'bg-secondary/10 text-text' ?>">
                                        <?= ucfirst($anuncio['tipo']) ?>
                                    </span>
                                    <div class="flex gap-2">
                                        <a href="post/edit.php?id=<?= $anuncio['idAnuncio'] ?>" class="text-text hover:text-hover">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="post/delete.php?id=<?= $anuncio['idAnuncio'] ?>" class="text-text hover:text-red-500" 
                                           onclick="return confirm('¿Estás seguro de que deseas eliminar este anuncio?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-map-marker-alt text-primary text-sm"></i>
                                            <span class="font-medium text-text">
                                                <?= htmlspecialchars($anuncio['origen']) ?>
                                                <span class="text-primary mx-2">→</span>
                                                <?= htmlspecialchars($anuncio['destino']) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm text-text/70">
                                            <div class="flex items-center gap-2">
                                                <i class="far fa-calendar text-primary/80"></i>
                                                <span><?= date('d/m/Y', strtotime($anuncio['fechaPublicacion'])) ?></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="far fa-clock text-primary/80"></i>
                                                <span><?= $anuncio['horaSalida'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                        <button onclick="showAnuncioPopup(<?= $anuncio['idAnuncio'] ?>)" 
                                                class="px-4 py-2 bg-primary text-text text-sm font-medium rounded-lg hover:bg-hover transition-colors">
                                            Ver detalles
                                        </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="mx-auto w-16 h-16 bg-background rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-car-side text-2xl text-primary"></i>
                        </div>
                        <p class="text-text/70">No has publicado ningún anuncio todavía.</p>
                        <a href="post/create.php" class="mt-2 inline-block text-primary hover:text-hover">Publicar mi primer anuncio</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
