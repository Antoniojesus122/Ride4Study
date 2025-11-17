  <?php
  session_start();

  if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php');
      exit;
  }

  require_once __DIR__ . '/../includes/db.php';
  $userId = $_SESSION['user_id'];

  try {
      // Obtener datos del usuario para el saludo
      $stmtUser = $pdo->prepare("SELECT nombre, correo, foto_perfil FROM usuarios WHERE idUsuario = ?");
      $stmtUser->execute([$userId]);
      $user = $stmtUser->fetch();

      if (!$user) {
          session_destroy();
          header('Location: ../index.php?error=usuario_no_existe');
          exit;
      }

      // Obtener todas las localidades para los filtros
      $stmtLocalidades = $pdo->query("SELECT idLocalidad, nombreLocalidad FROM localidades ORDER BY nombreLocalidad");
      $localidades = $stmtLocalidades->fetchAll();

      // Configuración de filtros
      $origen = $_GET['origen'] ?? '';
      $destino = $_GET['destino'] ?? '';
      $fecha = $_GET['fecha'] ?? '';
      $tipo = $_GET['tipo'] ?? '';

      // 1. Definir la base de la consulta
      $queryBase = "
          FROM anuncios a
          JOIN localidades lo ON a.origen = lo.idLocalidad
          JOIN localidades ld ON a.destino = ld.idLocalidad
          JOIN usuarios u ON a.idUsuario = u.idUsuario
          WHERE a.idUsuario != ?
      ";
      $params = [$userId];

      // 2. Construir la parte de los filtros
      $queryFilters = "";
      if (!empty($origen)) { $queryFilters .= " AND a.origen = ?"; $params[] = $origen; }
      if (!empty($destino)) { $queryFilters .= " AND a.destino = ?"; $params[] = $destino; }
      if (!empty($fecha)) { $queryFilters .= " AND a.fechaSalida = ?"; $params[] = $fecha; }
      if (!empty($tipo)) { $queryFilters .= " AND a.tipo = ?"; $params[] = $tipo; }

      // 3. Crear y ejecutar la consulta de CONTEO TOTAL (sin LIMIT)
      $countQuery = "SELECT COUNT(a.idAnuncio) " . $queryBase . $queryFilters;
      $countStmt = $pdo->prepare($countQuery);
      $countStmt->execute($params);
      $totalAnuncios = (int) $countStmt->fetchColumn();

      // 4. Calcular la paginación con el total correcto
      $perPage = 5;
      $totalPages = (int) ceil($totalAnuncios / $perPage);
      $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
      if ($currentPage > $totalPages && $totalPages > 0) { 
          $currentPage = $totalPages; 
      }
      $offset = ($currentPage - 1) * $perPage;

      // Se crea y ejecuta la consulta para obtener los anuncios de la página
      $dataQuery = "SELECT a.*, lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino, u.nombre AS nombreUsuario, u.correo AS correoUsuario " 
                . $queryBase . $queryFilters 
                . " ORDER BY a.fechaSalida DESC, a.horaSalida ASC LIMIT ? OFFSET ?";
      
      $params[] = $perPage;
      $params[] = $offset;

      $stmtAnuncios = $pdo->prepare($dataQuery);
      $stmtAnuncios->execute($params);
      $anuncios = $stmtAnuncios->fetchAll();

  } catch (PDOException $e) {
      die("Error al cargar el dashboard: " . htmlspecialchars($e->getMessage()));
  }
  ?>

  <!DOCTYPE html>
  <html lang="es" class="h-full bg-background">
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
    <!-- Menú de navegación -->
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
                <a href="dashboard.php" class="bg-primary/10 text-primary-dark rounded-md px-3 py-2 text-sm font-semibold">Dashboard</a>
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
                      <img src="../assets/avatars/<?= htmlspecialchars($user['foto_perfil']) ?>" alt="Foto de perfil" class="rounded-full w-full h-full object-cover">
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
      <div class="container mx-auto px-4 lg:px-6">
        
        <div class="mb-8">
          <h1 class="text-4xl font-extrabold tracking-tight text-text">Viajes Disponibles</h1>
          <p class="mt-2 text-lg text-text-muted">Encuentra compañeros para tu próximo trayecto o publica tus asientos libres.</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white p-6 rounded-xl shadow-sm border mb-8">
          <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div class="lg:col-span-1">
              <label for="origen" class="block text-sm font-medium text-text-muted mb-1">Origen</label>
              <select name="origen" id="origen" class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm">
                <option value="">Cualquiera</option>
                <?php foreach ($localidades as $localidad): ?>
                  <option value="<?= $localidad['idLocalidad'] ?>" <?= $origen == $localidad['idLocalidad'] ? 'selected' : '' ?>><?= htmlspecialchars($localidad['nombreLocalidad']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="lg:col-span-1">
              <label for="destino" class="block text-sm font-medium text-text-muted mb-1">Destino</label>
              <select name="destino" id="destino" class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm">
                <option value="">Cualquiera</option>
                <?php foreach ($localidades as $localidad): ?>
                  <option value="<?= $localidad['idLocalidad'] ?>" <?= $destino == $localidad['idLocalidad'] ? 'selected' : '' ?>><?= htmlspecialchars($localidad['nombreLocalidad']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="lg:col-span-1">
              <label for="fecha" class="block text-sm font-medium text-text-muted mb-1">Fecha</label>
              <input type="date" name="fecha" id="fecha" value="<?= $fecha ?>" class="w-full rounded-md border-0 py-2 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm">
            </div>
            <div class="lg:col-span-1">
              <label for="tipo" class="block text-sm font-medium text-text-muted mb-1">Tipo</label>
              <select name="tipo" id="tipo" class="w-full rounded-md border-0 py-2.5 px-3 text-text shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-dark sm:text-sm">
                <option value="">Ambos</option>
                <option value="ofrezco" <?= $tipo === 'ofrezco' ? 'selected' : '' ?>>Ofrezco plaza</option>
                <option value="busco" <?= $tipo === 'busco' ? 'selected' : '' ?>>Busco plaza</option>
              </select>
            </div>
            <div class="lg:col-span-1 flex gap-2">
              <button type="submit" class="w-full justify-center rounded-md bg-secondary px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-light">Buscar</button>
              <a href="dashboard.php" class="rounded-md bg-gray-200 p-2.5 text-gray-600 hover:bg-gray-300" title="Limpiar filtros"><i class="fas fa-undo"></i></a>
            </div>
          </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Columna principal de anuncios -->
          <div class="lg:col-span-2 space-y-6">
            <?php if (!empty($anuncios)): ?>
              <?php foreach ($anuncios as $anuncio): ?>
                <div class="bg-white rounded-xl border shadow-sm hover:shadow-lg hover:border-primary transition-all group">
                  <div class="p-6">
                    <div class="flex justify-between items-start">
                      <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/20 rounded-full flex items-center justify-center font-bold text-lg text-primary-dark">
                            <?= strtoupper(substr($anuncio['nombreUsuario'], 0, 1)) ?>
                        </div>
                        <div>
                          <h3 class="font-bold text-lg text-text"><?= htmlspecialchars($anuncio['nombreUsuario']) ?></h3>
                          <div class="flex items-center gap-1 text-sm text-yellow-500"><i class="fas fa-star"></i> 4.8 <span class="text-text-muted">(12)</span></div>
                        </div>
                      </div>
                      <span class="px-3 py-1 text-xs font-bold rounded-full <?= $anuncio['tipo'] === 'ofrezco' ? 'bg-primary/20 text-primary-dark' : 'bg-secondary/10 text-secondary' ?>">
                          <?= $anuncio['tipo'] === 'ofrezco' ? 'OFERTA' : 'BÚSQUEDA' ?>
                      </span>
                    </div>
                    <div class="my-4 border-t border-b border-gray-100 py-4">
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
                    <div class="flex justify-between items-center">
                      <p class="text-2xl font-extrabold text-secondary"><?= ($anuncio['precio'] > 0) ? number_format($anuncio['precio'], 2) . ' €' : '<span class="text-primary-dark">Gratis</span>' ?></p>
                      <div class="flex items-center gap-2">
                        <button onclick="showAnuncioPopup(<?= htmlspecialchars(json_encode($anuncio), ENT_QUOTES, 'UTF-8') ?>)" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-text-muted hover:bg-gray-200">Detalles</button>
                        <a href="messages.php?to=<?= $anuncio['idUsuario'] ?>" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-secondary shadow-sm hover:bg-primary-dark">Contactar</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-16 bg-white rounded-lg border border-dashed">
                  <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                  <h3 class="text-xl font-semibold text-secondary">No se encontraron viajes</h3>
                  <p class="text-text-muted mt-2">Prueba a modificar los filtros o a <a href="dashboard.php" class="text-primary-dark font-semibold hover:underline">limpiarlos</a> para ver más resultados.</p>
              </div>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
              <?php
                $baseParams = $_GET; unset($baseParams['page']);
                $qs = http_build_query($baseParams);
                $baseUrl = 'dashboard.php' . ($qs ? '?' . $qs . '&' : '?');
              ?>
              <nav class="mt-8 flex items-center justify-between" aria-label="Pagination">
                  <div class="text-sm text-text-muted">Mostrando <span class="font-medium"><?= $offset + 1 ?></span> a <span class="font-medium"><?= min($offset + $perPage, $totalAnuncios) ?></span> de <span class="font-medium"><?= $totalAnuncios ?></span> resultados</div>
                  <div class="flex items-center gap-2">
                      <a href="<?= $currentPage > 1 ? htmlspecialchars($baseUrl . 'page=' . ($currentPage - 1)) : '#' ?>" class="<?= $currentPage <= 1 ? 'pointer-events-none opacity-50' : '' ?> px-3 py-1.5 text-sm font-medium rounded-md bg-white border text-text-muted hover:bg-gray-50">Anterior</a>
                      <a href="<?= $currentPage < $totalPages ? htmlspecialchars($baseUrl . 'page=' . ($currentPage + 1)) : '#' ?>" class="<?= $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '' ?> px-3 py-1.5 text-sm font-medium rounded-md bg-white border text-text-muted hover:bg-gray-50">Siguiente</a>
                  </div>
              </nav>
            <?php endif; ?>
          </div>

          <!-- Columna lateral -->
          <aside class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border">
              <h2 class="text-xl font-bold text-text mb-4">Acciones rápidas</h2>
              <div class="space-y-3">
                <a href="post/create.php" class="flex w-full items-center justify-center gap-2 rounded-md bg-primary px-3 py-3 text-sm font-semibold text-secondary shadow-sm hover:bg-primary-dark transition-all">
                  <i class="fas fa-plus-circle"></i> Publicar un viaje
                </a>
                <a href="profile.php" class="flex w-full items-center justify-center gap-2 rounded-md bg-gray-100 px-3 py-3 text-sm font-semibold text-text-muted hover:bg-gray-200 transition-all">
                  <i class="fas fa-user-edit"></i> Editar mi perfil
                </a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </main>
  </div>
  </body>
  </html>