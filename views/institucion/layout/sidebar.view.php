<?php
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = rtrim(BASE_PATH, '/');

    function instIsActive(string $route, string $currentPath, string $basePath): bool {
        $full = rtrim($basePath . $route, '/');
        $current = rtrim($currentPath, '/');
        if ($route === '/institution/dashboard') {
            return $current === $basePath . '/institution' || $current === $basePath . '/institution/dashboard';
        }
        return str_starts_with($current, $full);
    }

    // Contador de mensajes sin leer
    $instNoLeidos = 0;
    if (isset($_SESSION['institution_id'])) {
        try {
            require_once __DIR__ . '/../../../config/database.php';
            require_once __DIR__ . '/../../../app/models/MensajeInstitucion.php';
            $database = new Database();
            $pdoSidebar = $database->connect();
            $mensajesSidebar = new MensajeInstitucion($pdoSidebar);
            $instNoLeidos = $mensajesSidebar->totalNoLeidosInstitucion((int)$_SESSION['institution_id']);
        } catch (\Exception $e) {
            $instNoLeidos = 0;
        }
    }

    $navItems = [
        ['route' => '/institution/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line'],
        ['route' => '/institution/students',  'label' => 'Estudiantes', 'icon' => 'fas fa-users'],
        ['route' => '/institution/messages',  'label' => 'Mensajes',  'icon' => 'fas fa-envelope', 'badge' => $instNoLeidos],
        ['route' => '/institution/profile',   'label' => 'Mi perfil', 'icon' => 'fas fa-user-gear'],
    ];
?>

<div id="inst-backdrop" class="inst-backdrop" onclick="toggleInstSidebar(false)"></div>
<aside id="inst-sidebar" class="inst-sidebar bg-gray-950 border-r border-gray-800 flex flex-col fixed top-0 left-0 h-screen z-30 overflow-hidden">

    <!-- Logo -->
    <div class="flex items-center h-[72px] border-b border-gray-800 shrink-0">
        <div class="w-[72px] flex items-center justify-center shrink-0">
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i class="fas fa-university text-blue-400" aria-hidden="true"></i>
            </div>
        </div>
        <span class="nav-label text-lg font-bold text-white whitespace-nowrap">Ride4Study</span>
    </div>

    <!-- Navegacion -->
    <nav class="flex-1 py-3 overflow-y-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
        <?php foreach ($navItems as $item):
            $active = instIsActive($item['route'], $currentPath, $basePath);
            $badge  = (int)($item['badge'] ?? 0);
        ?>
        <a href="<?= url($item['route']) ?>"
           class="flex items-center h-12 relative transition-colors <?= $active ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/30' ?>">
            <?php if ($active): ?>
                <div class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-blue-400 rounded-r-full"></div>
            <?php endif; ?>
            <div class="w-[72px] flex items-center justify-center shrink-0 relative">
                <i class="<?= $item['icon'] ?> text-lg"></i>
                <?php if ($badge > 0): ?>
                    <span class="absolute top-1 right-4 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                        <?= $badge > 99 ? '99+' : $badge ?>
                    </span>
                <?php endif; ?>
            </div>
            <span class="nav-label text-sm font-medium whitespace-nowrap flex items-center gap-2">
                <?= $item['label'] ?>
                <?php if ($badge > 0): ?>
                    <span class="px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 text-[10px] font-semibold border border-red-500/30">
                        <?= $badge > 99 ? '99+' : $badge ?>
                    </span>
                <?php endif; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Cerrar sesion -->
    <div class="border-t border-gray-800 shrink-0">
        <form method="POST" action="<?= url('/institution-logout') ?>" class="block">
            <?= csrfField() ?>
            <button type="submit" class="w-full flex items-center h-12 text-gray-500 hover:text-red-400 transition-colors text-left" title="Cerrar sesion">
                <div class="w-[72px] flex items-center justify-center shrink-0">
                    <i class="fas fa-sign-out-alt text-lg" aria-hidden="true"></i>
                </div>
                <span class="nav-label text-sm whitespace-nowrap">Cerrar sesion</span>
            </button>
        </form>
    </div>
</aside>
