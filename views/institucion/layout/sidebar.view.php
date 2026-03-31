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

    $navItems = [
        ['route' => '/institution/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line'],
        ['route' => '/institution/students', 'label' => 'Estudiantes', 'icon' => 'fas fa-users'],
        ['route' => '/institution/messages', 'label' => 'Mensajes', 'icon' => 'fas fa-envelope'],
    ];
?>

<aside class="inst-sidebar bg-gray-950 border-r border-gray-800 flex flex-col fixed top-0 left-0 h-screen z-30 overflow-hidden">

    <!-- Logo -->
    <div class="flex items-center h-[72px] border-b border-gray-800 shrink-0">
        <div class="w-[72px] flex items-center justify-center shrink-0">
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i class="fas fa-university text-blue-400"></i>
            </div>
        </div>
        <span class="nav-label text-lg font-bold text-white whitespace-nowrap">Ride4Study</span>
    </div>

    <!-- Navegacion -->
    <nav class="flex-1 py-3 overflow-y-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
        <?php foreach ($navItems as $item):
            $active = instIsActive($item['route'], $currentPath, $basePath);
        ?>
        <a href="<?= url($item['route']) ?>"
           class="flex items-center h-12 relative transition-colors <?= $active ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/30' ?>">
            <?php if ($active): ?>
                <div class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-blue-400 rounded-r-full"></div>
            <?php endif; ?>
            <div class="w-[72px] flex items-center justify-center shrink-0">
                <i class="<?= $item['icon'] ?> text-lg"></i>
            </div>
            <span class="nav-label text-sm font-medium whitespace-nowrap"><?= $item['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Cerrar sesion -->
    <div class="border-t border-gray-800 shrink-0">
        <a href="<?= url('/institution-logout') ?>" class="flex items-center h-12 text-gray-500 hover:text-red-400 transition-colors" title="Cerrar sesion">
            <div class="w-[72px] flex items-center justify-center shrink-0">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </div>
            <span class="nav-label text-sm whitespace-nowrap">Cerrar sesion</span>
        </a>
    </div>
</aside>
