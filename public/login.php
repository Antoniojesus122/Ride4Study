<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h2 class="text-center">Login</h2>
  <form action="../controllers/usuarioController.php" method="POST" class="mt-3 col-md-6 mx-auto">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="correo" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="contrasena" class="form-control" required>
    </div>
    <button type="submit" name="login" class="btn btn-success w-100">Login</button>
  </form>
  <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
</div>
<?php include '../includes/footer.php'; ?>