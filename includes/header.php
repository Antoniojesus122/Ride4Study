<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ride4Study</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/ride4study/public/home.php">Ride4Study</a>

    <ul class="navbar-nav ms-auto">
      <?php if (isset($_SESSION['usuario'])): ?>
        <li class="nav-item"><a href="/ride4study/public/nuevo_anuncio.php" class="nav-link">Nuevo Anuncio</a></li>
        <li class="nav-item"><a href="/ride4study/controllers/usuarioController.php?logout=true" class="nav-link">Cerrar Sesión</a></li>
      <?php else: ?>
        <li class="nav-item"><a href="/ride4study/public/login.php" class="nav-link">Iniciar Sesión</a></li>
        <li class="nav-item"><a href="/ride4study/public/register.php" class="nav-link">Registrar</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>