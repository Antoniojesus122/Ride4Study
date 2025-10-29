<?php
session_start();

// Si el usuario está logueado, redirigir a su dashboard
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/db.php';

    try {
        $stmt = $pdo->prepare("SELECT idRol FROM usuarios WHERE idUsuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            session_destroy();
            header('Location: ./public/login.php?error=usuario_no_existe');
            exit;
        }

        $idRol = (int)$user['idRol'];

        if ($idRol === 1 || $idRol === 3) {
            header('Location: ./admin/dashboard.php');
        } elseif ($idRol === 2 || $idRol === 4) {
            header('Location: ./user/dashboard.php');
        } else {
            session_destroy();
            header('Location: ./public/login.php?error=rol_invalido');
        }
        exit;

    } catch (PDOException $e) {
        die("Error al cargar el perfil: " . htmlspecialchars($e->getMessage()));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Ride4Study: Plataforma gratuita para que estudiantes compartan transporte entre localidades. Ahorra dinero, reduce emisiones y haz comunidad.">
  <title>Ride4Study - Viajes compartidos para estudiantes</title>
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
</head>
<body class="min-h-screen bg-background">

  <!-- Header -->
  <header class="bg-background shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <a href="index.php" class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white shadow-md">
          <i class="fas fa-car-side text-lg" aria-hidden="true"></i>
          <span class="sr-only">Logo Ride4Study</span>
        </div>
        <span class="text-xl font-bold text-gray-800">RIDE4STUDY</span>
      </a>

      <nav class="hidden md:flex items-center gap-6">
        <a href="#beneficios" class="text-gray-600 hover:text-primary font-medium">Beneficios</a>
        <a href="#como-funciona" class="text-gray-600 hover:text-primary font-medium">Cómo funciona</a>
        <a href="#testimonios" class="text-gray-600 hover:text-primary font-medium">Testimonios</a>
        <a href="#faq" class="text-gray-600 hover:text-primary font-medium">Preguntas</a>
      </nav>

      <div class="flex gap-3">
        <a href="./public/login.php" class="px-4 py-2 font-medium text-text hover:text-hover transition-colors">Iniciar Sesión</a>
        <a href="./public/register.php" class="px-4 py-2 bg-primary text-secondary rounded-lg font-medium hover:bg-hover transition">Registrarse</a>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="bg-secondary text-white py-20">
    <div class="container mx-auto px-4 text-center max-w-4xl">
      <h1 class="text-4xl md:text-5xl font-bold mb-6">Comparte tu viaje, no tu coche</h1>
      <p class="text-xl opacity-90 mb-10 max-w-2xl mx-auto">
        La plataforma gratuita para estudiantes que quieren ahorrar en transporte, reducir emisiones y crear una comunidad sostenible.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="./public/register.php" class="px-8 py-3 bg-primary text-secondary font-bold rounded-lg shadow-lg hover:bg-hover transition">
          Únete ahora (es gratis)
        </a>
        <a href="#como-funciona" class="px-8 py-3 bg-transparent border-2 border-primary text-white font-medium rounded-lg hover:bg-primary/10 transition">
          Cómo funciona
        </a>
      </div>
    </div>
  </section>

  <!-- Beneficios -->
  <section id="beneficios" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">¿Por qué elegir Ride4Study?</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">Diseñado específicamente para la comunidad estudiantil, con seguridad y sostenibilidad en mente.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border text-center hover:border-primary transition-colors">
          <div class="w-12 h-12 bg-primary/10 text-hover rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-euro-sign text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Ahorro económico</h3>
          <p class="text-gray-600">Comparte gastos de combustible y peajes. Viaja por una fracción del costo.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
          <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-leaf text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Sostenibilidad</h3>
          <p class="text-gray-600">Reduce tu huella de carbono. Cada viaje compartido es un coche menos en la carretera.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
          <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-user-graduate text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Comunidad estudiantil</h3>
          <p class="text-gray-600">Conecta con otros estudiantes de tu universidad o ciudad. Viaja en compañía.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
          <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-shield-alt text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Verificación universitaria</h3>
          <p class="text-gray-600">Todos los usuarios deben verificar su identidad estudiantil. Seguridad garantizada.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
          <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-star text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Sistema de valoraciones</h3>
          <p class="text-gray-600">Califica a tus compañeros de viaje. Fomentamos la confianza y la responsabilidad.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
          <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-mobile-alt text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Totalmente gratuito</h3>
          <p class="text-gray-600">Ninguna comisión, ni suscripciones. 100% gratuito para todos los estudiantes.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Cómo funciona -->
  <section id="como-funciona" class="py-20">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">¿Cómo funciona?</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">En solo 3 pasos estarás compartiendo viajes con otros estudiantes.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="w-16 h-16 bg-primary/10 text-hover rounded-full flex items-center justify-center mx-auto mb-6 text-xl font-bold">1</div>
          <h3 class="text-xl font-semibold mb-3">Regístrate</h3>
          <p class="text-gray-600">Crea tu perfil con tu correo universitario y verifica tu identidad estudiantil.</p>
        </div>

        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 text-primary rounded-full flex items-center justify-center mx-auto mb-6 text-xl font-bold">2</div>
          <h3 class="text-xl font-semibold mb-3">Publica o busca</h3>
          <p class="text-gray-600">Ofrece plaza en tu coche o busca alguien que vaya a tu destino.</p>
        </div>

        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 text-primary rounded-full flex items-center justify-center mx-auto mb-6 text-xl font-bold">3</div>
          <h3 class="text-xl font-semibold mb-3">Viaja y valora</h3>
          <p class="text-gray-600">Coordina el encuentro, comparte el viaje y valora la experiencia.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Últimos anuncios -->
  <section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Viajes disponibles ahora</h2>
        <p class="text-gray-600">Descubre los últimos viajes publicados por estudiantes como tú.</p>
      </div>

      <?php
      require_once __DIR__ . '/includes/db.php';
      try {
          $stmt = $pdo->query("
              SELECT a.idAnuncio, a.tipo, a.horaSalida, a.precio, 
                     lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino,
                     u.nombre AS nombreUsuario
              FROM anuncios a
              JOIN usuarios u ON a.idUsuario = u.idUsuario
              JOIN localidades lo ON a.origen = lo.idLocalidad
              JOIN localidades ld ON a.destino = ld.idLocalidad
              ORDER BY a.fechaPublicacion DESC
              LIMIT 6
          ");
          $rides = $stmt->fetchAll();
      } catch (PDOException $e) {
          $rides = [];
      }
      ?>

      <?php if (!empty($rides)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($rides as $ride): ?>
            <div class="bg-white rounded-xl p-5 shadow-sm border hover:shadow-md transition">
              <div class="flex justify-between items-start mb-3">
                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                  <?= $ride['tipo'] === 'ofrezco' ? 'bg-primary/20 text-hover' : 'bg-secondary/10 text-secondary' ?>">
                  <?= $ride['tipo'] === 'ofrezco' ? 'Ofrece plaza' : 'Busca plaza' ?>
                </span>
                <span class="text-sm text-gray-500"><?= htmlspecialchars($ride['nombreUsuario']) ?></span>
              </div>
              <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($ride['origen']) ?> → <?= htmlspecialchars($ride['destino']) ?></h3>
              <div class="flex justify-between text-sm text-gray-600 mb-3">
                <span><?= date('d/m/Y', strtotime($ride['fechaPublicacion'])) ?></span>
                <span><?= substr($ride['horaSalida'], 0, 5) ?></span>
              </div>
              <div class="flex justify-between items-center">
                <p class="font-bold">
                  <?php if ($ride['precio'] == 0): ?>
                    <span class="text-green-600">Gratis</span>
                  <?php else: ?>
                    <span class="text-blue-600"><?= number_format($ride['precio'], 2) ?> €</span>
                  <?php endif; ?>
                </p>
                <a href="./public/login.php" class="text-sm text-primary font-medium hover:underline">Contactar</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-500 py-8">Aún no hay viajes publicados. ¡Sé el primero en publicar uno!</p>
      <?php endif; ?>

      <div class="text-center mt-8">
        <a href="./public/login.php" class="inline-block px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition">
          Inicia sesión para publicar o contactar
        </a>
      </div>
    </div>
  </section>

  <!-- Testimonios -->
  <section id="testimonios" class="py-20">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Lo que dicen nuestros usuarios</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">Miles de estudiantes ya confían en Ride4Study para sus desplazamientos diarios.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
              <span class="font-bold text-gray-700">M</span>
            </div>
            <div>
              <h4 class="font-semibold">María G.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600 italic">"Gracias a Ride4Study he ahorrado más de 100€ este mes en transporte. Además, he conocido a compañeros de otras facultades."</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
              <span class="font-bold text-gray-700">C</span>
            </div>
            <div>
              <h4 class="font-semibold">Carlos R.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600 italic">"Como conductor, comparto los gastos y hago nuevos amigos. El sistema de valoraciones da mucha seguridad."</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
              <span class="font-bold text-gray-700">L</span>
            </div>
            <div>
              <h4 class="font-semibold">Laura M.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600 italic">"Vivo en un pueblo pequeño y me desplazo diariamente a la universidad. Esta plataforma ha cambiado mi rutina."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Preguntas frecuentes</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">Encuentra respuestas a las dudas más comunes.</p>
      </div>

      <div class="max-w-3xl mx-auto space-y-4">
        <div class="bg-white p-6 rounded-lg border">
          <h3 class="font-semibold text-lg mb-2">¿Es realmente gratis?</h3>
          <p class="text-gray-600">Sí, Ride4Study es 100% gratuito. No cobramos comisiones ni suscripciones. Solo se permite compartir gastos reales de transporte.</p>
        </div>

        <div class="bg-white p-6 rounded-lg border">
          <h3 class="font-semibold text-lg mb-2">¿Cómo se verifica la identidad estudiantil?</h3>
          <p class="text-gray-600">Durante el registro, solicitamos un correo institucional (.edu, .ac.uk, etc.) o documentación que acredite tu condición de estudiante.</p>
        </div>

        <div class="bg-white p-6 rounded-lg border">
          <h3 class="font-semibold text-lg mb-2">¿Qué pasa si no me presento al viaje?</h3>
          <p class="text-gray-600">Nuestro sistema de valoraciones y reportes ayuda a mantener la confianza en la comunidad. Los usuarios poco confiables pueden ser sancionados.</p>
        </div>

        <div class="bg-white p-6 rounded-lg border">
          <h3 class="font-semibold text-lg mb-2">¿Puedo publicar viajes sin coche?</h3>
          <p class="text-gray-600">¡Claro! Puedes buscar compañeros de viaje en transporte público, tren, o incluso en coche de otros conductores.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Final -->
  <section class="py-20 bg-secondary text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-6">¿Listo para empezar?</h2>
      <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">
        Únete a la comunidad de estudiantes que ya comparten viajes cada día.
      </p>
      <a href="./public/register.php" class="inline-block px-8 py-4 bg-primary text-secondary font-bold rounded-lg shadow-lg hover:bg-hover transition text-lg">
        Regístrate gratis ahora
      </a>
      <p class="mt-4 text-sm opacity-80">Sin tarjeta de crédito • Sin compromiso • 100% gratuito</p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center gap-2 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-2L17 9.5 13.5 11c-.8.3-1.5 1.1-1.5 2v3c0 .6.4 1 1 1h2"/>
                <path d="M11 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-2L9 9.5 5.5 11c-.8.3-1.5 1.1-1.5 2v3c0 .6.4 1 1 1h2"/>
              </svg>
            </div>
            <span class="text-xl font-bold">RIDE4STUDY</span>
          </div>
          <p class="text-gray-400">Viajes compartidos para estudiantes. Ahorra, reduce emisiones y haz comunidad.</p>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Empresa</h3>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white">Sobre nosotros</a></li>
            <li><a href="#" class="hover:text-white">Contacto</a></li>
            <li><a href="#" class="hover:text-white">Blog</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Legal</h3>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white">Términos de uso</a></li>
            <li><a href="#" class="hover:text-white">Política de privacidad</a></li>
            <li><a href="#" class="hover:text-white">Cookies</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Soporte</h3>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#" class="hover:text-white">Ayuda</a></li>
            <li><a href="#" class="hover:text-white">Preguntas frecuentes</a></li>
            <li><a href="./public/login.php" class="hover:text-white">Iniciar sesión</a></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
        <p>© 2025 Ride4Study. Proyecto académico de Antonio Jesús González Domingo (2º DAW).</p>
      </div>
    </div>
  </footer>

</body>
</html>