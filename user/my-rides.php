<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$userId = $_SESSION['user_id'];

try {
    // Obtener datos del usuario para el saludo en la cabecera
    $stmtUser = $pdo->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../index.php?error=usuario_no_existe');
        exit;
    }

    // Obtener los anuncios propios del usuario
    $stmtPropios = $pdo->prepare("
        SELECT a.*, lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino
        FROM anuncios a
        JOIN localidades lo ON a.origen = lo.idLocalidad
        JOIN localidades ld ON a.destino = ld.idLocalidad
        WHERE a.idUsuario = ?
        ORDER BY a.fechaSalida DESC, a.horaSalida ASC
    ");
    $stmtPropios->execute([$userId]);
    $anunciosPropios = $stmtPropios->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar los viajes: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-background">
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
    <script src="../assets/js/anuncio-popup.js" defer></script>
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
                            <a href="my-rides.php" class="bg-primary/10 text-primary-dark rounded-md px-3 py-2 text-sm font-semibold">Mis Viajes</a>
                            <a href="messages.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Mensajes</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-text-muted">Hola, <span class="font-semibold text-text"><?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></span></span>
                        <a href="profile.php" class="relative">
                            <div class="w-10 h-10 bg-secondary/20 rounded-full flex items-center justify-center font-bold text-secondary">
                                <?= strtoupper(substr($user['nombre'], 0, 1)) ?>
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
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-text">Mis Viajes</h1>
                    <p class="mt-2 text-lg text-text-muted">Aquí puedes ver, editar o eliminar los anuncios que has publicado.</p>
                </div>
                <a href="post/create.php" class="flex items-center justify-center gap-2 rounded-md bg-primary px-5 py-3 text-sm font-semibold text-secondary shadow-sm hover:bg-primary-dark transition-all">
                    <i class="fas fa-plus-circle"></i> Publicar Nuevo Viaje
                </a>
            </div>

            <!-- Lista de Mis Anuncios -->
            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <?php if (!empty($anunciosPropios)): ?>
                    <div class="space-y-6">
                        <?php foreach ($anunciosPropios as $anuncio): ?>
                            <div class="bg-white rounded-xl border hover:border-primary-dark/20 transition-all group p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2 text-xl font-semibold text-secondary">
                                            <span><?= htmlspecialchars($anuncio['origen']) ?></span>
                                            <i class="fas fa-long-arrow-alt-right text-primary mx-2"></i>
                                            <span><?= htmlspecialchars($anuncio['destino']) ?></span>
                                        </div>
                                        <div class="flex items-center gap-6 mt-3 text-sm text-text-muted">
                                            <span class="flex items-center gap-2"><i class="far fa-calendar-alt text-primary/80"></i> <?= date('d M Y', strtotime($anuncio['fechaSalida'])) ?></span>
                                            <span class="flex items-center gap-2"><i class="far fa-clock text-primary/80"></i> <?= substr($anuncio['horaSalida'], 0, 5) ?>h</span>
                                            <span class="flex items-center gap-2"><i class="fas fa-user-friends text-primary/80"></i> <?= htmlspecialchars($anuncio['plazasDisponibles']) ?> plazas</span>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full <?= $anuncio['tipo'] === 'ofrezco' ? 'bg-primary/20 text-primary-dark' : 'bg-secondary/10 text-secondary' ?>">
                                        <?= $anuncio['tipo'] === 'ofrezco' ? 'OFERTA' : 'BÚSQUEDA' ?>
                                    </span>
                                </div>
                                <div class="mt-4 border-t border-gray-100 pt-4 flex justify-between items-center">
                                    <p class="text-lg font-bold text-secondary">Publicado el <?= date('d/m/Y', strtotime($anuncio['fechaPublicacion'])) ?></p>
                                    <div class="flex items-center gap-2">
                                        <a href="post/edit.php?id=<?= $anuncio['idAnuncio'] ?>" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-text-muted hover:bg-gray-200">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        <a href="../actions/delete_ride_action.php?id=<?= $anuncio['idAnuncio'] ?>" 
                                           onclick="return confirm('¿Estás seguro de que quieres eliminar este viaje? Esta acción no se puede deshacer.');" 
                                           class="rounded-md bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16 bg-white rounded-lg border border-dashed">
                        <i class="fas fa-car-side text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-secondary">Aún no has publicado ningún viaje</h3>
                        <p class="text-text-muted mt-2">Cuando publiques un viaje, aparecerá aquí para que puedas gestionarlo.</p>
                        <a href="post/create.php" class="mt-6 inline-block rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-secondary shadow-sm hover:bg-primary-dark transition-all">
                            Publicar mi primer viaje
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>