<?php
    // Recuento de reportes pendientes
    $pendingBadge = 0;
    try {
        $dbTopbar = (new Database())->connect();
        $stmtPending = $dbTopbar->query("SELECT COUNT(*) FROM reportes WHERE estado = 'pendiente'");
        $pendingBadge = (int)$stmtPending->fetchColumn();
    } catch (Exception $e) {}
?>

<div class="sticky top-0 z-20 bg-gray-900/80 backdrop-blur-md border-b border-gray-800 px-10 py-6 flex items-center justify-between">
    <!-- Izquierda: Titulo de la pagina -->
    <div>
        <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>

    <!-- Derecha: Busqueda + Notificaciones + Usuario Admin -->
    <div class="flex items-center gap-5">
        <!-- Busqueda (por ahora no funcional) -->
        <div class="hidden md:flex items-center bg-gray-800/60 border border-gray-700/50 rounded-lg px-4 py-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" placeholder="Buscar..." class="bg-transparent border-none outline-none text-base text-gray-300 placeholder-gray-500 ml-3 w-52" readonly>
        </div>

        <!-- Notificaciones de reportes pendientes -->
        <?php if ($pendingBadge > 0): ?>
        <a href="<?= url('/admin/reports') ?>?tab=usuario" class="relative p-2.5 text-gray-400 hover:text-white transition-colors" title="Reportes pendientes">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center"><?= $pendingBadge > 99 ? '99+' : $pendingBadge ?></span>
        </a>
        <?php endif; ?>

        <!-- Avatar de admin + desplegable -->
        <div class="relative pl-4 border-l border-gray-700/50">
            <button onclick="document.getElementById('admin-dropdown').classList.toggle('hidden')" class="flex items-center gap-3 hover:opacity-80 transition cursor-pointer">
                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="text-primary text-base font-bold"><?= mb_strtoupper(mb_substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?></span>
                </div>
                <span class="text-base text-gray-300 font-medium hidden sm:inline"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-500 hidden sm:block">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div id="admin-dropdown" class="hidden absolute right-0 top-full mt-2 w-52 bg-gray-800 border border-gray-700 rounded-xl shadow-xl z-30 py-1.5 overflow-hidden">
                <a href="<?= url('/admin/profile') ?>" class="flex items-center gap-3 px-5 py-3 text-base text-gray-300 hover:bg-gray-700/50 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Mi perfil
                </a>
                <div class="border-t border-gray-700 my-1"></div>
                <a href="<?= url('/logout') ?>" class="flex items-center gap-3 px-5 py-3 text-base text-red-400 hover:bg-red-500/10 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Cerrar sesion
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Cerrar desplegable si se hace click fuera de este -->
<script>
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('admin-dropdown');
        if (dd && !dd.parentElement.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });
</script>
