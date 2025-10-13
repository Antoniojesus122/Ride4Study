<!-- CSS para el login -->
<link rel="stylesheet" href="../assets/css/login.css">

<div class="login-container">
  <!-- Sección izquierda  -->
  <div class="login-left-section">
  </div>
  
  <!-- Sección derecha -->
  <div class="login-right-section">
    <div class="login-form-container">
      <!-- Logo y header -->
      <div class="login-header">
        <img src="../assets/img/logo.png" alt="Logo" class="logo">
      </div>
      
      <!-- Título principal -->
      <h1 class="login-title">Accede a tu cuenta:</h1>
      
      <!-- Formulario -->
      <form action="../controllers/usuarioController.php" method="POST" class="login-form">
        <div class="form-group">
          <label for="correo" class="form-label">Correo Electrónico</label>
          <input type="email" name="correo" id="correo" class="form-input" required>
        </div>
        
        <div class="form-group">
          <label for="contrasena" class="form-label">Contraseña</label>
          <div class="password-container">
            <input type="password" name="contrasena" id="contrasena" class="form-input password-input" required>
            <span class="password-toggle" onclick="togglePassword()">🙈</span>
          </div>
        </div>
        
        <button type="submit" name="login" class="login-button">Iniciar Sesión</button>
      </form>
      
      <!-- Enlaces -->
      <div class="login-links">
        <a href="register.php" class="register-link">¿No tienes cuenta? Regístrate aquí</a>
        <a href="recuperar_contrasena.php" class="forgot-link">¿Has olvidado tu contraseña?</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePassword() {
  const passwordInput = document.getElementById('contrasena');
  const toggleIcon = document.querySelector('.password-toggle');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.textContent = '🐵';
  } else {
    passwordInput.type = 'password';
    toggleIcon.textContent = '🙈';
  }
}
</script>
