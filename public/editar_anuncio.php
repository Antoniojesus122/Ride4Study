<?php
include '../includes/header.php';
require_once '../models/Anuncio.php';
require_once '../config/db.php';

$anuncioModel = new Anuncio($pdo);
$anuncio = $anuncioModel->obtenerPorId($_GET['id']);
?>
<div class="container mt-5">
  <h2 class="text-center">Editar Anuncio</h2>
  <form action="../controllers/anuncioController.php" method="POST" class="col-md-8 mx-auto">
    <input type="hidden" name="idAnuncio" value="<?= $anuncio['idAnuncio']; ?>">
    <div class="mb-3">
      <label>Tipo</label>
      <select name="tipo" class="form-control">
        <option value="ofrezco" <?= $anuncio['tipo']=='ofrezco'?'selected':''; ?>>Ofrezco transporte</option>
        <option value="busco" <?= $anuncio['tipo']=='busco'?'selected':''; ?>>Busco transporte</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Origen</label>
      <input type="text" name="origen" class="form-control" value="<?= $anuncio['origen']; ?>" required>
    </div>
    <div class="mb-3">
      <label>Destino</label>
      <input type="text" name="destino" class="form-control" value="<?= $anuncio['destino']; ?>" required>
    </div>
    <div class="mb-3">
      <label>Hora de Salida</label>
      <input type="time" name="horaSalida" class="form-control" value="<?= $anuncio['horaSalida']; ?>" required>
    </div>
    <div class="mb-3">
      <label>Hora de Regreso</label>
      <input type="time" name="horaRegreso" class="form-control" value="<?= $anuncio['horaRegreso']; ?>">
    </div>
    <div class="mb-3">
      <label>Precio (€)</label>
      <input type="number" name="precio" class="form-control" step="0.01" value="<?= $anuncio['precio']; ?>">
    </div>
    <button type="submit" name="editar_anuncio" class="btn btn-warning w-100">Guardar Cambios</button>
  </form>
</div>
<?php include '../includes/footer.php'; ?>