<?php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  $isLoggedIn = isset($_SESSION['user_id']);
  $userName = $_SESSION['user_name'] ?? '';
  $userInitial = !empty($userName) ? strtoupper(substr($userName, 0, 1)) : 'U';
?>

<!DOCTYPE html>
  <html lang="es" class="h-full bg-gray-900">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ride4Study</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
<body class="h-full text-gray-100 flex flex-col pt-28 bg-cover bg-center">
    <div class="fixed inset-0 bg-gray-900/90 z-[-1]"></div>

<nav class="fixed top-6 inset-x-0 mx-auto z-50 w-full max-w-5xl px-4 sm:px-6">
  <div class="bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-full shadow-2xl px-6 py-3 mx-auto">
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <a href="index.php" class="flex-shrink-0 group">
          <div class="flex items-center gap-3">
            <img src="public/img/logoRide.svg" alt="Ride4Study" class="w-10 h-10 object-contain transition-transform group-hover:scale-110">
          </div>
        </a>
      </div>

      <div class="block">
        <div class="flex items-center gap-6">
          <?php if ($isLoggedIn): ?>
              <div class="hidden md:flex items-center gap-1 bg-white/5 rounded-full p-1 border border-white/5">
                  <a href="dashboard.php" class="px-4 py-1.5 rounded-full text-sm font-medium transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5'; ?>">
                      <i class="fas fa-search mr-1.5"></i> Buscar
                  </a>
                  <a href="my-rides.php" class="px-4 py-1.5 rounded-full text-sm font-medium transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'my-rides.php' ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5'; ?>">
                      <i class="fas fa-car mr-1.5"></i> Mis viajes
                  </a>
                  <a href="messages.php" class="px-4 py-1.5 rounded-full text-sm font-medium transition-all relative <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5'; ?>">
                      <i class="fas fa-comment-alt mr-1.5"></i> Mensajes
                      <span class="absolute top-1 right-2 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                      </span>
                  </a>
              </div>

              <div class="h-6 w-px bg-white/10 hidden md:block"></div>

              <div class="relative">
                <button type="button" onclick="document.getElementById('user-menu').classList.toggle('hidden')" class="flex items-center gap-2 rounded-full pr-1 pl-1 py-1 hover:bg-white/10 transition-all border border-transparent hover:border-white/10" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                    <div class="h-9 w-9 rounded-full overflow-hidden ring-2 ring-gray-900 bg-gray-800 flex items-center justify-center">
                      <?php if (!empty($_SESSION['user_photo']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo'])): 
                        $photoPath = 'public/uploads/profiles/' . $_SESSION['user_photo'];
                        $fsPath = __DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo'];
                        $ver = filemtime($fsPath);
                      ?>
                        <img src="<?= $photoPath ?>?v=<?= $ver ?>" alt="Avatar" class="w-full h-full object-cover">
                      <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-tr from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-sm">
                          <?= $userInitial ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <i class="fas fa-chevron-down text-gray-400 text-xs mr-2 transition-transform duration-200" style="transform: rotate(0deg);" id="menu-arrow"></i>
                </button>
                
                <div id="user-menu" class="hidden absolute right-0 top-full mt-3 w-60 origin-top-right rounded-2xl bg-[#1a1b26]/95 backdrop-blur-xl border border-white/10 py-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none transform transition-all z-50">
                    <div class="px-5 py-4 border-b border-white/5 bg-white/5 mx-2 rounded-xl mb-2">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Conectado como</p>
                        <p class="text-base font-bold text-white truncate mt-1"><?= htmlspecialchars($userName) ?></p>
                    </div>
                    
                    <div class="px-2 space-y-1">
                        <a href="profile.php" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                            <i class="fas fa-user-circle w-6 text-center text-gray-400 group-hover:text-secondary/70"></i> Mi Perfil
                        </a>
                        <a href="my-rides.php" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                            <i class="fas fa-car w-6 text-center text-gray-400 group-hover:text-secondary/70"></i> Mis Viajes
                        </a>
                        <a href="my-rides.php?tab=bookings" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                            <i class="fas fa-ticket-alt w-6 text-center text-gray-400 group-hover:text-secondary/70"></i> Mis Reservas
                        </a>
                    </div>
                    
                    <div class="mt-2 pt-2 border-t border-white/5 px-2">
                         <a href="logout.php" class="group flex items-center px-3 py-2.5 text-sm font-medium text-red-400 rounded-lg hover:bg-red-500/10 hover:text-red-300 transition-all">
                            <i class="fas fa-sign-out-alt w-6 text-center opacity-70"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
              </div>

              <script>
                // Efectos para el menú
                const btn = document.getElementById('user-menu-button');
                const menu = document.getElementById('user-menu');
                const arrow = document.getElementById('menu-arrow');

                btn.addEventListener('click', () => {
                   const isHidden = menu.classList.contains('hidden');
                   arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                });

                document.addEventListener('click', function(event) {
                    if (!btn.contains(event.target) && !menu.contains(event.target)) {
                        menu.classList.add('hidden');
                        arrow.style.transform = 'rotate(0deg)';
                    }
                });
              </script>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>

<main class="flex-grow flex flex-col h-full">
