<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h2 class="text-center">Create Account</h2>
  <form action="../controllers/usuarioController.php" method="POST" class="mt-3 col-md-6 mx-auto">
    <div class="mb-3">
      <label>Name</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="correo" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Phone</label>
      <input type="text" name="telefono" class="form-control">
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="contrasena" class="form-control" required>
    </div>
    <button type="submit" name="registro" class="btn btn-primary w-100">Register</button>
  </form>
</div>
<?php include '../includes/footer.php'; ?>