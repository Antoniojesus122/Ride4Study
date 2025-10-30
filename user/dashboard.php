<?php
session_start();

// Solo verificar sesión, sin redirección automática aquí
if (!isset($_SESSION['user_id'])) {
    // Redirigir a index.php para que gestione la redirección
    header('Location: ../index.php');
    exit;
}

// Obtener datos del usuario
require_once __DIR__ . '/../includes/db.php';
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: ../index.php?error=usuario_no_existe');
        exit;
    }

    // Obtener todas las localidades para los filtros
    $stmt = $pdo->query("SELECT idLocalidad, nombreLocalidad FROM localidades ORDER BY nombreLocalidad");
    $localidades = $stmt->fetchAll();

  // Configurar los filtros
  $origen = $_GET['origen'] ?? '';
  $destino = $_GET['destino'] ?? '';
  $fecha = $_GET['fecha'] ?? '';
  $tipo = $_GET['tipo'] ?? '';

  // Paginación: anuncios por página
  $perPage = 5; // mostrar 5 anuncios por página
  $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;

    // Construir la consulta base
    $query = "
        SELECT a.*, lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino, 
               u.nombre AS nombreUsuario, u.correo AS correoUsuario
        FROM anuncios a
        JOIN localidades lo ON a.origen = lo.idLocalidad
        JOIN localidades ld ON a.destino = ld.idLocalidad
        JOIN usuarios u ON a.idUsuario = u.idUsuario
        WHERE 1=1
    ";
    $params = [];

  // Excluir los anuncios del propio usuario (mostrar solo anuncios de otros usuarios)
  $query .= " AND a.idUsuario != ?";
  $params[] = $userId;

    // Añadir filtros si están establecidos
    if (!empty($origen)) {
        $query .= " AND a.origen = ?";
        $params[] = $origen;
    }
    if (!empty($destino)) {
        $query .= " AND a.destino = ?";
        $params[] = $destino;
    }
    if (!empty($fecha)) {
        $query .= " AND DATE(a.fechaPublicacion) = ?";
        $params[] = $fecha;
    }
    if (!empty($tipo)) {
        $query .= " AND a.tipo = ?";
        $params[] = $tipo;
    }

  $query .= " ORDER BY a.fechaPublicacion DESC";

  // Primero contamos el total de anuncios que cumplen los filtros
  $countQuery = preg_replace('/SELECT\s+a\.\*/i', 'SELECT COUNT(*) AS total', $query);
  // quitar ORDER BY en countQuery si existe
  $countQuery = preg_replace('/ORDER BY[\s\S]*/i', '', $countQuery);

  $countStmt = $pdo->prepare($countQuery);
  $countStmt->execute($params);
  $totalAnuncios = (int) $countStmt->fetchColumn();

  $totalPages = (int) ceil($totalAnuncios / $perPage);
  if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
  }

  $offset = ($currentPage - 1) * $perPage;

  // Añadir LIMIT y OFFSET a la consulta original
  $query .= " LIMIT ? OFFSET ?";
  $paramsWithLimit = $params;
  $paramsWithLimit[] = $perPage;
  $paramsWithLimit[] = $offset;

  $stmt = $pdo->prepare($query);
  $stmt->execute($paramsWithLimit);
  $anuncios = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar el dashboard: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Ride4Study</title>
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
        <a href="dashboard.php" class="px-3 py-2 text-sm font-medium text-text bg-primary/10 rounded-md">Dashboard</a>
        <a href="my-rides.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Mis Viajes</a>
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
      <h1 class="text-3xl font-bold tracking-tight text-text">Panel de viajes</h1>
      <p class="text-text/70">Explora y administra tus desplazamientos</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label for="origen" class="block text-sm font-medium text-text/70 mb-2">Origen</label>
          <select name="origen" id="origen" class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
            <option value="">Todos los orígenes</option>
            <?php foreach ($localidades as $localidad): ?>
              <option value="<?= $localidad['idLocalidad'] ?>" <?= $origen == $localidad['idLocalidad'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($localidad['nombreLocalidad']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="destino" class="block text-sm font-medium text-text/70 mb-2">Destino</label>
          <select name="destino" id="destino" class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
            <option value="">Todos los destinos</option>
            <?php foreach ($localidades as $localidad): ?>
              <option value="<?= $localidad['idLocalidad'] ?>" <?= $destino == $localidad['idLocalidad'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($localidad['nombreLocalidad']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="fecha" class="block text-sm font-medium text-text/70 mb-2">Fecha</label>
          <input type="date" name="fecha" id="fecha" value="<?= $fecha ?>" class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
        </div>
        <div>
          <label for="tipo" class="block text-sm font-medium text-text/70 mb-2">Tipo de anuncio</label>
          <select name="tipo" id="tipo" class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
            <option value="">Todos los tipos</option>
            <option value="ofrezco" <?= $tipo === 'ofrezco' ? 'selected' : '' ?>>Ofrezco</option>
            <option value="busco" <?= $tipo === 'busco' ? 'selected' : '' ?>>Busco</option>
          </select>
        </div>
        <div class="md:col-span-4 flex justify-end gap-2">
          <a href="dashboard.php" class="px-4 py-2 bg-background text-text font-medium rounded-lg border border-gray-200 hover:border-primary/20 transition-colors">
            Limpiar filtros
          </a>
          <button type="submit" class="px-4 py-2 bg-primary text-text font-medium rounded-lg hover:bg-hover transition-colors">
            Buscar viajes
          </button>
        </div>
      </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Columna principal con todos los anuncios -->
      <div class="space-y-6 lg:col-span-2">

        <!-- Lista de todos los anuncios -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-semibold mb-4 text-text">Viajes disponibles</h2>
          <?php if (!empty($anuncios)): ?>
            <div class="space-y-4">
              <?php foreach ($anuncios as $anuncio): ?>
                <div class="p-4 bg-background rounded-xl hover:border-primary/20 border border-transparent transition-colors">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-text">
                          <?= strtoupper(substr($anuncio['nombreUsuario'], 0, 2)) ?>
                        </span>
                      </div>
                      <div>
                        <span class="font-medium text-text"><?= htmlspecialchars($anuncio['nombreUsuario']) ?></span>
                        <div class="flex items-center gap-1 text-text/60 text-sm">
                          <i class="fas fa-star text-primary text-xs"></i>
                          <span>4.8</span>
                        </div>
                      </div>
                    </div>
                    <span class="px-3 py-1.5 text-xs font-medium rounded-full <?= $anuncio['tipo'] === 'ofrezco' ? 'bg-primary/10 text-text' : 'bg-secondary/10 text-text' ?>">
                      <?= $anuncio['tipo'] === 'ofrezco' ? 'Ofrezco' : 'Busco' ?>
                    </span>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-primary text-sm"></i>
                        <span class="font-medium text-text"><?= htmlspecialchars($anuncio['origen']) ?> <span class="text-primary mx-2">→</span> <?= htmlspecialchars($anuncio['destino']) ?></span>
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
                    <div class="flex items-center gap-2">
                      <button onclick="showAnuncioPopup(<?= $anuncio['idAnuncio'] ?>)" class="px-4 py-2 bg-background text-text text-sm font-medium rounded-lg border border-gray-200 hover:border-primary/20 transition-colors">
                        Ver detalles
                      </button>
                      <a href="#" class="px-4 py-2 bg-primary text-text text-sm font-medium rounded-lg hover:bg-hover transition-colors">
                        Contactar
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <div class="mx-auto w-16 h-16 bg-background rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-car-side text-2xl text-primary"></i>
              </div>
              <p class="text-text/70">No hay viajes disponibles con los filtros seleccionados.</p>
              <a href="dashboard.php" class="mt-2 inline-block text-primary hover:text-hover">Limpiar filtros</a>
            </div>
          <?php endif; ?>

          <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <?php
              // Construir base URL manteniendo los filtros
              $baseParams = $_GET;
              unset($baseParams['page']);
              $qs = http_build_query($baseParams);
              $baseUrl = 'dashboard.php' . ($qs ? '?' . $qs . '&' : '?');

              $showFrom = $totalAnuncios > 0 ? ($offset + 1) : 0;
              $showTo = min($offset + $perPage, $totalAnuncios);
            ?>

            <div class="mt-6 flex items-center justify-between">
              <div class="text-sm text-text/70">Mostrando <?= $showFrom ?> - <?= $showTo ?> de <?= $totalAnuncios ?> anuncios</div>
              <nav class="flex items-center gap-2" aria-label="Paginación">
                <?php if ($currentPage > 1): ?>
                  <a href="<?= htmlspecialchars($baseUrl . 'page=' . ($currentPage - 1)) ?>" class="px-3 py-1 rounded-md bg-background border border-gray-200 hover:bg-primary/10">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <a href="<?= htmlspecialchars($baseUrl . 'page=' . $i) ?>" class="px-3 py-1 rounded-md <?= $i === $currentPage ? 'bg-primary text-white' : 'bg-background border border-gray-200 hover:bg-primary/10' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                  <a href="<?= htmlspecialchars($baseUrl . 'page=' . ($currentPage + 1)) ?>" class="px-3 py-1 rounded-md bg-background border border-gray-200 hover:bg-primary/10">Siguiente &raquo;</a>
                <?php endif; ?>
              </nav>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Columna lateral -->
      <div class="space-y-6">
        <!-- Acciones rápidas -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-semibold mb-4 text-text">Acciones rápidas</h2>
          <div class="space-y-3">
            <a href="post/create.php" class="block w-full text-center px-4 py-3 bg-primary text-text font-medium rounded-xl hover:bg-hover transition-colors">
              Publicar anuncio
            </a>
            <a href="profile.php" class="block w-full text-center px-4 py-3 bg-background text-text font-medium rounded-xl border border-transparent hover:border-primary/20 transition-colors">
              Editar perfil
            </a>
            <a href="my-rides.php" class="block w-full text-center px-4 py-3 bg-background text-text font-medium rounded-xl border border-transparent hover:border-primary/20 transition-colors">
              Gestionar mis viajes
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>