<?php
    // Recuento de reportes pendientes y verificaciones pendientes
    $pendingReports = 0;
    $pendingVerifications = 0;
    $recentNotifs = [];
    try {
        $dbTopbar = (new Database())->connect();

        $stmtR = $dbTopbar->query("SELECT COUNT(*) FROM reportes WHERE estado = 'pendiente'");
        $pendingReports = (int)$stmtR->fetchColumn();

        $stmtV = $dbTopbar->query("SELECT COUNT(*) FROM usuarios WHERE estado_verificacion = 1");
        $pendingVerifications = (int)$stmtV->fetchColumn();

        // Últimos reportes pendientes (max 5)
        $stmtRecent = $dbTopbar->query(
            "SELECT r.tipo, r.motivo, r.creado_en, u.nombre AS reporta_nombre
             FROM reportes r
             LEFT JOIN usuarios u ON r.idUsuarioQueReporta = u.idUsuario
             WHERE r.estado = 'pendiente'
             ORDER BY r.creado_en DESC LIMIT 5"
        );
        $recentNotifs = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

        // Últimas verificaciones pendientes (max 3)
        $stmtVerif = $dbTopbar->query(
            "SELECT nombre, correo, creado_en FROM usuarios WHERE estado_verificacion = 1 ORDER BY creado_en DESC LIMIT 3"
        );
        $recentVerifs = $stmtVerif->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $recentVerifs = [];
    }

    $totalPending = $pendingReports + $pendingVerifications;

    $motivoLabelsTopbar = [
        'spam' => 'Spam', 'ofensivo' => 'Ofensivo', 'suplantacion' => 'Suplantacion',
        'inapropiado' => 'Inapropiado', 'fraude' => 'Fraude', 'otro' => 'Otro',
    ];
    $tipoLabelsTopbar = ['usuario' => 'Usuario', 'anuncio' => 'Anuncio', 'chat' => 'Chat'];
?>

<div class="sticky top-0 z-20 bg-gray-900/80 backdrop-blur-md border-b border-gray-800 px-4 sm:px-6 lg:px-10 py-4 sm:py-5 lg:py-6 flex items-center justify-between gap-3">
    <!-- Izquierda: Hamburguesa móvil + Titulo de la página -->
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" onclick="toggleAdminSidebar()" class="md:hidden p-2 -ml-2 text-gray-300 hover:text-white rounded-lg hover:bg-gray-800 transition-colors shrink-0" aria-label="Abrir menú">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-white truncate"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>

    <!-- Derecha: Notificaciones + Usuario Admin -->
    <div class="flex items-center gap-2 sm:gap-3 lg:gap-5 shrink-0">

        <!-- Notificaciones admin -->
        <div class="relative">
            <button onclick="document.getElementById('admin-notif-dropdown').classList.toggle('hidden')" class="relative p-2.5 text-gray-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <?php if ($totalPending > 0): ?>
                <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center"><?= $totalPending > 99 ? '99+' : $totalPending ?></span>
                <?php endif; ?>
            </button>

            <!-- Dropdown de notificaciones -->
            <div id="admin-notif-dropdown" class="hidden fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-[4.5rem] sm:top-full sm:mt-2 sm:w-96 sm:max-w-none bg-gray-800 border border-gray-700 rounded-xl shadow-2xl z-[60] overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-white">Notificaciones</h3>
                    <?php if ($totalPending > 0): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 font-medium"><?= $totalPending ?> pendiente<?= $totalPending !== 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <?php if ($totalPending === 0): ?>
                    <div class="px-5 py-8 text-center">
                        <i class="fas fa-check-circle text-2xl text-green-400/50 mb-2" aria-hidden="true"></i>
                        <p class="text-sm text-gray-500">Todo al día, sin pendientes</p>
                    </div>
                    <?php else: ?>

                        <!-- Reportes pendientes -->
                        <?php if ($pendingReports > 0): ?>
                        <div class="px-5 py-2.5 bg-gray-900/50">
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Reportes pendientes (<?= $pendingReports ?>)</p>
                        </div>
                        <?php foreach ($recentNotifs as $n): ?>
                        <a href="<?= url('/admin/reports') ?>?tab=<?= htmlspecialchars($n['tipo']) ?>" class="flex items-start gap-3 px-5 py-3 hover:bg-gray-700/40 transition border-b border-gray-700/30">
                            <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-flag text-xs text-red-400" aria-hidden="true"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 leading-snug">
                                    <span class="font-medium"><?= htmlspecialchars($n['reporta_nombre'] ?? 'Usuario') ?></span>
                                    reporto un <span class="text-red-400 font-medium"><?= $tipoLabelsTopbar[$n['tipo']] ?? $n['tipo'] ?></span>
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] px-1.5 py-0.5 rounded bg-gray-700 text-gray-400"><?= $motivoLabelsTopbar[$n['motivo']] ?? $n['motivo'] ?></span>
                                    <span class="text-[11px] text-gray-600"><?= date('d/m H:i', strtotime($n['creado_en'])) ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php if ($pendingReports > 5): ?>
                        <a href="<?= url('/admin/reports') ?>" class="block px-5 py-2 text-xs text-primary hover:text-primary-dark text-center">Ver todos los reportes</a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <!-- Verificaciones pendientes -->
                        <?php if ($pendingVerifications > 0): ?>
                        <div class="px-5 py-2.5 bg-gray-900/50">
                            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Verificaciones pendientes (<?= $pendingVerifications ?>)</p>
                        </div>
                        <?php foreach ($recentVerifs as $v): ?>
                        <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="flex items-start gap-3 px-5 py-3 hover:bg-gray-700/40 transition border-b border-gray-700/30">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/10 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-id-card text-xs text-yellow-400" aria-hidden="true"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 leading-snug">
                                    <span class="font-medium"><?= htmlspecialchars($v['nombre']) ?></span>
                                    solicito verificacion
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-gray-500"><?= htmlspecialchars($v['correo']) ?></span>
                                    <span class="text-[11px] text-gray-600"><?= date('d/m H:i', strtotime($v['creado_en'])) ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php if ($pendingVerifications > 3): ?>
                        <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="block px-5 py-2 text-xs text-primary hover:text-primary-dark text-center">Ver todas las verificaciones</a>
                        <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Avatar de admin + desplegable -->
        <div class="relative sm:pl-4 sm:border-l sm:border-gray-700/50">
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

<!-- Cerrar desplegables si se hace click fuera + toggle sidebar móvil -->
<script>
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('admin-dropdown');
        if (dd && !dd.parentElement.contains(e.target)) {
            dd.classList.add('hidden');
        }
        const nd = document.getElementById('admin-notif-dropdown');
        if (nd && !nd.parentElement.contains(e.target)) {
            nd.classList.add('hidden');
        }
    });
    window.toggleAdminSidebar = function(force) {
        const sb = document.getElementById('admin-sidebar');
        const bd = document.getElementById('admin-backdrop');
        if (!sb) return;
        const willOpen = typeof force === 'boolean' ? force : !sb.classList.contains('is-open');
        sb.classList.toggle('is-open', willOpen);
        if (bd) bd.classList.toggle('is-open', willOpen);
        document.body.style.overflow = willOpen ? 'hidden' : '';
    };
    // Cerrar sidebar al navegar (links dentro) en móvil
    document.addEventListener('click', function(e) {
        if (window.innerWidth >= 768) return;
        const sb = document.getElementById('admin-sidebar');
        if (sb && sb.classList.contains('is-open') && e.target.closest('#admin-sidebar a')) {
            setTimeout(() => toggleAdminSidebar(false), 50);
        }
    });
    // Reset al cambiar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            const sb = document.getElementById('admin-sidebar');
            const bd = document.getElementById('admin-backdrop');
            if (sb) sb.classList.remove('is-open');
            if (bd) bd.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });
</script>
