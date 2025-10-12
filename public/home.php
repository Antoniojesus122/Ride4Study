<?php
include '../includes/header.php';
require_once '../models/Anuncio.php';
require_once '../config/db.php';

$anuncioModel = new Anuncio($pdo);
$anuncios = $anuncioModel->obtenerTodos();
?>
<div class="container mt-4">
  <h2 class="text-center">Anuncios Disponibles</h2>
  <a href="nuevo_anuncio.php" class="btn btn-success mb-3">+ Nuevo Anuncio</a>

  <div class="row">
    <?php foreach ($anuncios as $a): ?>
      <div class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><?= ucfirst($a['tipo']); ?> transporte</h5>
            <p><strong>Desde:</strong> <?= $a['origenNombre']; ?> → <strong>Hasta:</strong> <?= $a['destinoNombre']; ?></p>
            <p><strong>Salida:</strong> <?= $a['horaSalida']; ?></p>
            <p><strong>Precio:</strong> <?= $a['precio'] ? $a['precio'] . " €" : "Free"; ?></p>
            <p><strong>Publicado por:</strong> <?= $a['autor']; ?></p>

            <a href="editar_anuncio.php?id=<?= $a['idAnuncio']; ?>" class="btn btn-warning btn-sm">Editar</a>
            <a href="../controllers/anuncioController.php?eliminar=<?= $a['idAnuncio']; ?>" 
               class="btn btn-danger btn-sm" onclick="return confirm('Eliminar este anuncio?')">Eliminar</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>