<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h2 class="text-center">Nuevo Anuncio</h2>
  <form action="../controllers/anuncioController.php" method="POST" class="col-md-8 mx-auto">
    <div class="mb-3">
      <label>Tipo</label>
      <select name="tipo" class="form-control" required>
        <option value="ofrezco">Ofrezco transporte</option>
        <option value="busco">Busco transporte</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Origen</label>
      <input type="text" name="origen" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Destino</label>
      <input type="text" name="destino" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Hora de Salida</label>
      <input type="time" name="horaSalida" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Hora de Regreso</label>
      <input type="time" name="horaRegreso" class="form-control">
    </div>
    <div class="mb-3">
      <label>Precio (€)</label>
      <input type="number" name="precio" class="form-control" step="0.01">
    </div>
    <button type="submit" name="crear_anuncio" class="btn btn-primary w-100">Publicar</button>
  </form>
</div>
<?php include '../includes/footer.php'; ?>