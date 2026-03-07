<?php
    require_once __DIR__ . '/layout/header.view.php';
    require_once __DIR__ . '/layout/sidebar.view.php';
?>

<main class="flex-1 p-8">
    <!-- Header -->
    <header class="mb-10 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold">Dashboard</h1>
            <p class="text-gray-400 mt-2">Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?></p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-400">Hoy: <?= date('d/m/Y H:i') ?></p>
        </div>
    </header>

    <!-- Estadísticas Principales -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Usuarios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Total de Usuarios</p>
                    <h3 class="text-3xl font-bold text-white mt-2"><?= $stats['users'] ?></h3>
                    <p class="text-gray-400 text-xs mt-3">
                        ✓ <?= $stats['verified_users'] ?? 0 ?> verificados
                    </p>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <a href="<?= url('/admin/users') ?>" class="text-primary text-xs hover:text-primary/80 mt-4 inline-block">Ver todos →</a>
        </div>

        <!-- Anuncios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Anuncios Activos</p>
                    <h3 class="text-3xl font-bold text-white mt-2"><?= $stats['ads'] ?></h3>
                    <p class="text-gray-400 text-xs mt-3">
                        Sistema funcionando
                    </p>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            </div>
            <a href="<?= url('/admin/ads') ?>" class="text-primary text-xs hover:text-primary/80 mt-4 inline-block">Moderar →</a>
        </div>

        <!-- Reportes -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Total de Reportes</p>
                    <h3 class="text-3xl font-bold text-white mt-2"><?= $stats['reports'] ?></h3>
                    <p class="text-gray-400 text-xs mt-3">
                        <?= $stats['pending_reports'] ?? 0 ?> pendientes
                    </p>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                    </svg>
                </div>
            </div>
            <a href="<?= url('/admin/reports') ?>" class="text-primary text-xs hover:text-primary/80 mt-4 inline-block">Revisar →</a>
        </div>

        <!-- Instituciones -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Instituciones</p>
                    <h3 class="text-3xl font-bold text-white mt-2"><?= $stats['institutions'] ?></h3>
                    <p class="text-gray-400 text-xs mt-3">
                        Registradas
                    </p>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
            </div>
            <a href="<?= url('/admin/instituciones') ?>" class="text-primary text-xs hover:text-primary/80 mt-4 inline-block">Administrar →</a>
        </div>
    </section>

    <!-- Estadísticas secundarias -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <!-- Verificación de usuarios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Estado de Verificación</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Verificados</span>
                    <div class="flex items-center gap-3">
                        <div class="w-32 bg-gray-700 rounded-full h-2">
                             <!--  
                            <div class="bg-gray-400 h-2 rounded-full" style="width: <?= $stats['users'] > 0 ? (($stats['verified_users'] / $stats['users']) * 100) : 0 ?>%"></div>
                             -->
                        </div>
                        <span class="text-gray-300 font-semibold text-sm"><?= $stats['verified_users'] ?? 0 ?></span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Pendientes</span>
                    <div class="flex items-center gap-3">
                        <div class="w-32 bg-gray-700 rounded-full h-2">
                            <!--
                            <div class="bg-gray-500 h-2 rounded-full" style="width: <?= $stats['users'] > 0 ? (($stats['pending_verification'] / $stats['users']) * 100) : 0 ?>%"></div>
                            -->
                        </div>
                        <span class="text-gray-300 font-semibold text-sm"><?= $stats['pending_verification'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Acciones Rápidas</h3>
            <div class="space-y-2">
                <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition text-center">
                    Gestionar Usuarios
                </a>
                <a href="<?= url('/admin/reports') ?>" class="block px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-medium transition text-center">
                    Revisar Reportes
                </a>
                <a href="<?= url('/admin/ads') ?>" class="block px-4 py-2 bg-gray-700 hover:bg-gray-650 text-white rounded-lg font-medium transition text-center">
                    Moderar Anuncios
                </a>
                <a href="<?= url('/admin/instituciones') ?>" class="block px-4 py-2 bg-gray-700 hover:bg-gray-650 text-white rounded-lg font-medium transition text-center">
                    Instituciones
                </a>
            </div>
        </div>
    </section>

    <!-- Tablas de datos recientes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <!-- Reportes pendientes -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">Reportes Pendientes</h3>
                <a href="<?= url('/admin/reports') ?>?tab=usuario" class="text-primary text-sm hover:underline">Ver todos</a>
            </div>
            <?php if (!empty($pendingReports)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="px-3 py-2 text-left text-gray-400">ID</th>
                                <th class="px-3 py-2 text-left text-gray-400">Tipo</th>
                                <th class="px-3 py-2 text-left text-gray-400">Usuario</th>
                                <th class="px-3 py-2 text-left text-gray-400">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pendingReports, 0, 5) as $report): ?>
                            <tr class="border-b border-gray-700/50 hover:bg-gray-700/20">
                                <td class="px-3 py-2">#<?= $report['idReporte'] ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded capitalize">
                                        <?= htmlspecialchars($report['tipo']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-300">
                                    <?= htmlspecialchars($report['reportado_nombre'] ?? 'N/A') ?>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded">
                                        Pendiente
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-400">
                    <p>✓ No hay reportes pendientes</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Últimos anuncios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">Últimos Anuncios</h3>
                <a href="<?= url('/admin/ads') ?>" class="text-primary text-sm hover:underline">Ver todos</a>
            </div>
            <?php if (!empty($recentAds)): ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($recentAds, 0, 5) as $ad): ?>
                    <div class="bg-gray-700/50 rounded-lg p-3 border border-gray-600/50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-1 bg-gray-700 text-gray-300 rounded capitalize">
                                        <?= htmlspecialchars($ad['tipo']) ?>
                                    </span>
                                    <p class="text-gray-300 text-sm font-medium">
                                        por <?= htmlspecialchars($ad['usuario_nombre'] ?? 'N/A') ?>
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?= date('d/m/Y H:i', strtotime($ad['fechaPublicacion'])) ?>
                                </p>
                            </div>
                            <?php if ($ad['precio']): ?>
                            <span class="text-green-400 font-semibold">€<?= $ad['precio'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-400">
                    <p>No hay anuncios recientes</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Últimos usuarios -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 overflow-hidden">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-white">Últimos Usuarios Registrados</h3>
            <a href="<?= url('/admin/users') ?>" class="text-primary text-sm hover:underline">Ver todos</a>
        </div>
        <?php if (!empty($recentUsers)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-4 py-3 text-left text-gray-400">Nombre</th>
                            <th class="px-4 py-3 text-left text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-gray-400">Verificación</th>
                            <th class="px-4 py-3 text-left text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/20 transition">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium"><?= htmlspecialchars($user['nombre']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-sm"><?= htmlspecialchars($user['correo']) ?></td>
                            <td class="px-4 py-3">
                                <?php 
                                    $estado = $user['estado_verificacion'];
                                    if ($estado === 2):
                                        echo '<span class="px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded">Verificado</span>';
                                    elseif ($estado === 1):
                                        echo '<span class="px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded">Pendiente</span>';
                                    else:
                                        echo '<span class="px-2 py-1 bg-gray-700 text-gray-200 text-xs rounded">No verificado</span>';
                                    endif;
                                ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?= url('/admin/users') ?>?action=view&id=<?= $user['idUsuario'] ?>" class="text-primary hover:underline text-xs">
                                    Ver detalles
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <p>No hay usuarios registrados</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
