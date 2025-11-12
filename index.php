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
<html lang="es" class="scroll-smooth">
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
            primary: '#6EE7B7', // Un verde menta brillante
            'primary-dark': '#10B981', // Un verde más oscuro para hover
            secondary: '#374151', // Gris oscuro casi negro
            'secondary-light': '#4B5563', // Gris un poco más claro
            background: '#F9FAFB', // Un gris muy claro para el fondo
            text: '#1F2937', // Color de texto principal
            'text-muted': '#6B7280', // Color para texto secundario o párrafos
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .hero-bg {
      background-image: linear-gradient(to right, rgba(55, 65, 81, 0.9), rgba(55, 65, 81, 0.7)), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=2070&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
    }
  </style>
</head>
<body class="bg-white text-text antialiased">

  <!-- Header -->
  <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 lg:px-6">
      <div class="flex justify-between items-center py-4">
        <a href="index.php" class="flex items-center gap-2">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white">
            <i class="fas fa-car-side text-lg" aria-hidden="true"></i>
          </div>
          <span class="text-2xl font-bold text-secondary tracking-tighter">RIDE4STUDY</span>
        </a>

        <nav class="hidden md:flex items-center gap-8">
          <a href="#beneficios" class="text-text-muted hover:text-primary-dark font-medium transition-colors">Beneficios</a>
          <a href="#como-funciona" class="text-text-muted hover:text-primary-dark font-medium transition-colors">Cómo funciona</a>
          <a href="#testimonios" class="text-text-muted hover:text-primary-dark font-medium transition-colors">Testimonios</a>
          <a href="#faq" class="text-text-muted hover:text-primary-dark font-medium transition-colors">Preguntas</a>
        </nav>

        <div class="flex items-center gap-2">
          <a href="./public/login.php" class="px-4 py-2 font-semibold text-secondary hover:text-primary-dark transition-colors">Iniciar Sesión</a>
          <a href="./public/register.php" class="px-5 py-2.5 bg-primary text-secondary rounded-full font-bold hover:bg-primary-dark transition-all shadow-sm">Registrarse</a>
        </div>
      </div>
    </div>
  </header>

  <main>
    <!-- Hero Section -->
    <section class="hero-bg text-white">
      <div class="container mx-auto px-4 lg:px-6 py-24 md:py-32 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6 tracking-tighter">
          Viajes compartidos para estudiantes. <br class="hidden md:block" />
          <span class="text-primary">Ahorra, conecta y sé sostenible.</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-200 mb-10 max-w-3xl mx-auto">
          La plataforma gratuita donde los estudiantes comparten coche para ir a clase o volver a casa. Reduce gastos, emisiones y haz nuevos amigos.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="./public/register.php" class="px-8 py-4 bg-primary text-secondary font-bold rounded-full shadow-lg hover:bg-primary-dark transform hover:scale-105 transition-all text-lg">
            Crear cuenta gratis
          </a>
          <a href="#como-funciona" class="px-8 py-4 bg-transparent border-2 border-primary text-white font-medium rounded-full hover:bg-primary hover:text-secondary transition-all text-lg">
            Descubre cómo funciona
          </a>
        </div>
      </div>
    </section>

    <!-- Beneficios -->
    <section id="beneficios" class="py-20 md:py-28 bg-background">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4 tracking-tight">Diseñado para la vida estudiantil</h2>
          <p class="text-text-muted text-lg max-w-2xl mx-auto">Todo lo que necesitas para viajar de forma segura, económica y en buena compañía.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50 text-center transition-transform transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-primary/10 text-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-wallet text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Ahorro Inteligente</h3>
            <p class="text-text-muted">Comparte los gastos de gasolina y peajes. Tu cartera te lo agradecerá cada fin de mes.</p>
          </div>

          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50 text-center transition-transform transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-primary/10 text-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-users text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Comunidad Exclusiva</h3>
            <p class="text-text-muted">Conecta con estudiantes de tu campus. Todos los perfiles son verificados para tu tranquilidad.</p>
          </div>

          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50 text-center transition-transform transform hover:-translate-y-2">
            <div class="w-16 h-16 bg-primary/10 text-primary-dark rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-leaf text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-3 text-secondary">Planeta Feliz</h3>
            <p class="text-text-muted">Menos coches en la carretera significa menos emisiones. Un pequeño gesto con un gran impacto.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Cómo funciona -->
    <section id="como-funciona" class="py-20 md:py-28">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4 tracking-tight">Empieza en 3 simples pasos</h2>
          <p class="text-text-muted text-lg max-w-2xl mx-auto">Publicar un viaje u ocupar un asiento nunca fue tan fácil.</p>
        </div>

        <div class="relative">
          <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 -translate-y-1/2"></div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            <div class="text-center">
              <div class="relative w-20 h-20 bg-primary/10 text-primary-dark rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold border-4 border-white shadow-md">1</div>
              <h3 class="text-xl font-bold mb-3 text-secondary">Regístrate y Verifica</h3>
              <p class="text-text-muted">Crea tu perfil en segundos y verifícalo con tu correo de estudiante.</p>
            </div>

            <div class="text-center">
              <div class="relative w-20 h-20 bg-primary/10 text-primary-dark rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold border-4 border-white shadow-md">2</div>
              <h3 class="text-xl font-bold mb-3 text-secondary">Publica o Busca</h3>
              <p class="text-text-muted">Ofrece las plazas libres de tu coche o busca un viaje a tu destino.</p>
            </div>

            <div class="text-center">
              <div class="relative w-20 h-20 bg-primary/10 text-primary-dark rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold border-4 border-white shadow-md">3</div>
              <h3 class="text-xl font-bold mb-3 text-secondary">Viaja y Valora</h3>
              <p class="text-text-muted">Coordina con tu compañero, disfrutad del viaje y dejad una valoración.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Últimos anuncios -->
    <section class="py-20 md:py-28 bg-background">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4 tracking-tight">Viajes recientes</h2>
          <p class="text-text-muted text-lg max-w-2xl mx-auto">Estos son los últimos trayectos publicados por la comunidad.</p>
        </div>

        <?php
        require_once __DIR__ . '/includes/db.php';
        try {
            $stmt = $pdo->query("
                SELECT a.idAnuncio, a.tipo, a.horaSalida, a.fechaSalida, a.precio, 
                       lo.nombreLocalidad AS origen, ld.nombreLocalidad AS destino,
                       u.nombre AS nombreUsuario
                FROM anuncios a
                JOIN usuarios u ON a.idUsuario = u.idUsuario
                JOIN localidades lo ON a.origen = lo.idLocalidad
                JOIN localidades ld ON a.destino = ld.idLocalidad
                ORDER BY a.fechaSalida DESC, a.horaSalida ASC
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
              <div class="bg-white rounded-xl p-6 border border-gray-200/80 shadow-sm hover:shadow-xl hover:border-primary transition-all group">
                <div class="flex justify-between items-center mb-4">
                  <span class="px-3 py-1 text-xs font-bold rounded-full 
                    <?= $ride['tipo'] === 'ofrezco' ? 'bg-primary/20 text-primary-dark' : 'bg-secondary/10 text-secondary' ?>">
                    <?= $ride['tipo'] === 'ofrezco' ? 'OFERTA' : 'BÚSQUEDA' ?>
                  </span>
                  <span class="text-sm text-text-muted flex items-center gap-2"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($ride['nombreUsuario']) ?></span>
                </div>
                <h3 class="font-bold text-xl text-secondary mb-3"><?= htmlspecialchars($ride['origen']) ?> <i class="fas fa-long-arrow-alt-right text-primary mx-2"></i> <?= htmlspecialchars($ride['destino']) ?></h3>
                <div class="flex justify-between text-sm text-text-muted border-t border-b border-gray-100 py-3 my-3">
                  <span class="flex items-center gap-2"><i class="far fa-calendar-alt text-primary/80"></i><?= date('d M Y', strtotime($ride['fechaSalida'])) ?></span>
                  <span class="flex items-center gap-2"><i class="far fa-clock text-primary/80"></i><?= substr($ride['horaSalida'], 0, 5) ?>h</span>
                </div>
                <div class="flex justify-between items-center">
                  <p class="font-extrabold text-xl">
                    <?php if (!$ride['precio'] || $ride['precio'] == 0): ?>
                      <span class="text-primary-dark">Gratis</span>
                    <?php else: ?>
                      <span class="text-secondary"><?= number_format($ride['precio'], 2) ?> €</span>
                    <?php endif; ?>
                  </p>
                  <a href="./public/login.php" class="text-sm text-primary-dark font-bold hover:underline">Ver detalles</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-12 bg-white rounded-lg border border-dashed">
              <i class="fas fa-car-side text-4xl text-gray-300 mb-4"></i>
              <h3 class="text-xl font-semibold text-secondary">Aún no hay viajes publicados</h3>
              <p class="text-text-muted mt-2">¡Anímate y sé el primero en compartir tu trayecto!</p>
          </div>
        <?php endif; ?>

        <div class="text-center mt-12">
          <a href="./public/login.php" class="inline-block px-8 py-3 bg-secondary text-white font-bold rounded-full hover:bg-secondary-light transition-colors shadow-md">
            Ver todos los viajes
          </a>
        </div>
      </div>
    </section>

    <!-- Testimonios -->
    <section id="testimonios" class="py-20 md:py-28">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4 tracking-tight">Lo que dice nuestra comunidad</h2>
          <p class="text-text-muted text-lg max-w-2xl mx-auto">Experiencias reales de estudiantes que ya usan Ride4Study.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50">
            <p class="text-text-muted italic mb-6">"Gracias a Ride4Study he ahorrado más de 100€ este mes en transporte. Además, he conocido a compañeros de otras facultades. ¡Totalmente recomendado!"</p>
            <div class="flex items-center">
              <div class="w-12 h-12 bg-primary/20 rounded-full flex items-center justify-center mr-4">
                <span class="font-bold text-lg text-primary-dark">M</span>
              </div>
              <div>
                <h4 class="font-bold text-secondary">María G.</h4>
                <div class="flex text-yellow-400 text-sm">
                  <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50">
            <p class="text-text-muted italic mb-6">"Como conductor, compartir los gastos del viaje es una ayuda increíble. El sistema de valoraciones te da mucha seguridad a la hora de elegir compañeros."</p>
            <div class="flex items-center">
              <div class="w-12 h-12 bg-primary/20 rounded-full flex items-center justify-center mr-4">
                <span class="font-bold text-lg text-primary-dark">C</span>
              </div>
              <div>
                <h4 class="font-bold text-secondary">Carlos R.</h4>
                <div class="flex text-yellow-400 text-sm">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200/50">
            <p class="text-text-muted italic mb-6">"Vivo en un pueblo pequeño y me desplazo diariamente a la universidad. Esta plataforma ha cambiado mi rutina, ahora viajo acompañada y es mucho más ameno."</p>
            <div class="flex items-center">
              <div class="w-12 h-12 bg-primary/20 rounded-full flex items-center justify-center mr-4">
                <span class="font-bold text-lg text-primary-dark">L</span>
              </div>
              <div>
                <h4 class="font-bold text-secondary">Laura M.</h4>
                <div class="flex text-yellow-400 text-sm">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20 md:py-28 bg-background">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4 tracking-tight">Preguntas Frecuentes</h2>
          <p class="text-text-muted text-lg max-w-2xl mx-auto">Resolvemos tus dudas más comunes para que empieces con total confianza.</p>
        </div>

        <div class="max-w-3xl mx-auto space-y-4">
          <div class="bg-white p-6 rounded-lg border border-gray-200/80">
            <h3 class="font-semibold text-lg text-secondary mb-2">¿Es realmente gratis?</h3>
            <p class="text-text-muted">Sí, Ride4Study es 100% gratuito. No cobramos comisiones ni suscripciones. La plataforma se centra en conectar estudiantes para que compartan los gastos reales del viaje.</p>
          </div>

          <div class="bg-white p-6 rounded-lg border border-gray-200/80">
            <h3 class="font-semibold text-lg text-secondary mb-2">¿Cómo se verifica mi identidad de estudiante?</h3>
            <p class="text-text-muted">Para garantizar un entorno seguro, solicitamos la verificación a través de un correo electrónico institucional (por ejemplo, @universidad.edu) durante el proceso de registro.</p>
          </div>

          <div class="bg-white p-6 rounded-lg border border-gray-200/80">
            <h3 class="font-semibold text-lg text-secondary mb-2">¿Qué ocurre si un usuario no se presenta al viaje?</h3>
            <p class="text-text-muted">La comunidad se basa en la confianza. Nuestro sistema de valoraciones permite calificar a tus compañeros. Los usuarios con bajas calificaciones o reportes recurrentes pueden ser sancionados para mantener la fiabilidad de la plataforma.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Final -->
    <section class="bg-secondary">
        <div class="container mx-auto px-4 lg:px-6 py-20 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 tracking-tight">¿Listo para tu próximo viaje?</h2>
            <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                Únete a miles de estudiantes que ya están ahorrando y conectando. El registro es rápido, fácil y gratuito.
            </p>
            <a href="./public/register.php" class="inline-block px-8 py-4 bg-primary text-secondary font-bold rounded-full shadow-lg hover:bg-primary-dark transform hover:scale-105 transition-all text-lg">
                Empieza a compartir viaje ahora
            </a>
            <p class="mt-4 text-sm text-gray-400">Sin compromisos • 100% para estudiantes</p>
        </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400">
    <div class="container mx-auto px-4 lg:px-6 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-8">
        <div>
          <div class="flex items-center gap-2 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white">
                <i class="fas fa-car-side text-lg" aria-hidden="true"></i>
            </div>
            <span class="text-xl font-bold text-white tracking-tighter">RIDE4STUDY</span>
          </div>
          <p class="text-sm">La plataforma de carpooling creada por y para estudiantes. Ahorra, reduce tu huella de carbono y haz nuevos amigos.</p>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-white mb-4">Navegación</h3>
          <ul class="space-y-3">
            <li><a href="#beneficios" class="hover:text-primary transition-colors">Beneficios</a></li>
            <li><a href="#como-funciona" class="hover:text-primary transition-colors">Cómo funciona</a></li>
            <li><a href="#testimonios" class="hover:text-primary transition-colors">Testimonios</a></li>
            <li><a href="#faq" class="hover:text-primary transition-colors">Preguntas Frecuentes</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-white mb-4">Legal</h3>
          <ul class="space-y-3">
            <li><a href="#" class="hover:text-primary transition-colors">Términos de uso</a></li>
            <li><a href="#" class="hover:text-primary transition-colors">Política de privacidad</a></li>
            <li><a href="#" class="hover:text-primary transition-colors">Política de cookies</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-white mb-4">Contacto</h3>
          <ul class="space-y-3">
            <li><a href="#" class="hover:text-primary transition-colors">Centro de Ayuda</a></li>
            <li><a href="#" class="hover:text-primary transition-colors">info@ride4study.com</a></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 mt-8 text-center text-sm">
        <p>© 2025 Ride4Study. Un proyecto académico de Antonio Jesús González Domingo (2º DAW).</p>
      </div>
    </div>
  </footer>

</body>
</html>