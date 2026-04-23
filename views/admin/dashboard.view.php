<?php $pageTitle = 'Dashboard'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    // Saludo segun hora
    $hour = (int)date('G');
    if ($hour < 6 || $hour >= 21) {
        $greeting = 'Buenas noches';
    } elseif ($hour < 14) {
        $greeting = 'Buenos días';
    } else {
        $greeting = 'Buenas tardes';
    }
    $adminName = $_SESSION['user_name'] ?? 'Admin';

    // Fecha formateada en español sin depender de strftime (deprecated en PHP 8.1+)
    $diasEs  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $mesesEs = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $fechaHoy = $diasEs[(int)date('w')] . ', ' . date('j') . ' de ' . $mesesEs[(int)date('n')] . ' de ' . date('Y');

    // Helper local para la badge de cada KPI
    $kpis = [
        [
            'url'   => url('/admin/users'),
            'label' => t('admin.total_users'),
            'value' => $stats['users'],
            'badge' => $stats['verified_users'] . ' ' . t('admin.verified'),
            'color' => 'emerald',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
        ],
        [
            'url'   => url('/admin/ads'),
            'label' => t('admin.total_ads'),
            'value' => $stats['ads'],
            'badge' => t('admin.active'),
            'color' => 'amber',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
        ],
        [
            'url'   => url('/admin/reports') . '?tab=usuario',
            'label' => t('admin.total_reports'),
            'value' => $stats['reports'],
            'badge' => ($stats['pending_reports'] ?? 0) > 0
                        ? $stats['pending_reports'] . ' ' . t('admin.pending')
                        : t('admin.no_pending'),
            'badge_alert' => ($stats['pending_reports'] ?? 0) > 0,
            'color' => 'red',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />',
        ],
        [
            'url'   => url('/admin/instituciones'),
            'label' => t('admin.institutions'),
            'value' => $stats['institutions'],
            'badge' => t('admin.registered'),
            'color' => 'purple',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />',
        ],
        [
            'url'   => url('/admin/premium'),
            'label' => 'Usuarios Premium',
            'value' => $stats['premium_active'],
            'badge' => ($stats['premium_expiring'] ?? 0) > 0
                        ? $stats['premium_expiring'] . ' expiran pronto'
                        : number_format($stats['revenue_month'], 2, ',', '.') . ' € mes',
            'badge_alert' => ($stats['premium_expiring'] ?? 0) > 0,
            'color' => 'yellow',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />',
        ],
        [
            'url'   => url('/admin/messages'),
            'label' => 'Mensajes instituciones',
            'value' => $stats['messages_unread'],
            'badge' => ($stats['messages_unread'] ?? 0) > 0 ? 'sin leer' : 'al día',
            'badge_alert' => ($stats['messages_unread'] ?? 0) > 0,
            'color' => 'blue',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
        ],
    ];

    // Helper para formatear una entrada del log 
    function dashFormatLog(string $accion, string $entidad): string {
        $combos = [
            'aprobar_verificacion'       => 'Verificación aprobada',
            'rechazar_verificacion'      => 'Verificación rechazada',
            'banear_usuario'             => 'Usuario suspendido',
            'desbanear_usuario'          => 'Usuario reactivado',
            'eliminar_usuario'           => 'Usuario eliminado',
            'cambiar_rol_usuario'        => 'Rol cambiado',
            'conceder_premium_usuario'   => 'Premium concedido',
            'revocar_premium_usuario'    => 'Premium revocado',
            'crear_institucion'          => 'Institución creada',
            'editar_institucion'         => 'Institución editada',
            'eliminar_institucion'       => 'Institución eliminada',
            'activar_institucion'        => 'Institución activada',
            'desactivar_institucion'     => 'Institución desactivada',
            'reset_password_institucion' => 'Contraseña regenerada',
            'eliminar_anuncio'           => 'Anuncio eliminado',
            'tomar_reporte'              => 'Reporte asignado',
            'liberar_reporte'            => 'Reporte liberado',
            'resolver_reporte'           => 'Reporte resuelto',
            'eliminar_reporte'           => 'Reporte descartado',
            'actualizar_configuracion'   => 'Configuración actualizada',
            'enviar_notificacion_masiva_notificacion' => 'Notificación masiva enviada',
            'enviar_mensaje_institucion' => 'Mensaje a institución',
        ];
        $key = $accion . '_' . $entidad;
        if (isset($combos[$key]))    return $combos[$key];
        if (isset($combos[$accion])) return $combos[$accion];
        return ucfirst(str_replace('_', ' ', $accion));
    }
?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <!-- Saludo -->
    <header class="mb-8 flex items-end justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white"><?= $greeting ?>, <?= htmlspecialchars(explode(' ', $adminName)[0]) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= $fechaHoy ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($stats['pending_reports'] > 0): ?>
                <a href="<?= url('/admin/reports') ?>?tab=usuario" class="px-3 py-1.5 text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/30 rounded-full hover:bg-red-500/20 transition">
                    <?= $stats['pending_reports'] ?> reportes pendientes
                </a>
            <?php endif; ?>
            <?php if ($stats['pending_verification'] > 0): ?>
                <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="px-3 py-1.5 text-xs font-semibold bg-yellow-500/10 text-yellow-400 border border-yellow-500/30 rounded-full hover:bg-yellow-500/20 transition">
                    <?= $stats['pending_verification'] ?> verificaciones
                </a>
            <?php endif; ?>
            <?php if ($stats['messages_unread'] > 0): ?>
                <a href="<?= url('/admin/messages') ?>" class="px-3 py-1.5 text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/30 rounded-full hover:bg-blue-500/20 transition">
                    <?= $stats['messages_unread'] ?> mensajes
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- KPIs principales -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-10">
        <?php foreach ($kpis as $k): ?>
            <a href="<?= $k['url'] ?>" class="bg-gray-800/50 border border-gray-700 rounded-xl p-5 hover:border-gray-600 transition group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-lg bg-<?= $k['color'] ?>-500/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-<?= $k['color'] ?>-400"><?= $k['icon'] ?></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white leading-none"><?= (int)$k['value'] ?></p>
                <p class="text-sm text-gray-300 mt-2 font-medium"><?= htmlspecialchars($k['label']) ?></p>
                <p class="text-xs mt-2 <?= !empty($k['badge_alert']) ? 'text-' . $k['color'] . '-400 font-semibold' : 'text-gray-500' ?>">
                    <?= htmlspecialchars($k['badge']) ?>
                </p>
            </a>
        <?php endforeach; ?>
    </section>

    <!-- Graficas de tendencias -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-base font-semibold text-white mb-4"><?= t('admin.registrations_month') ?></h3>
            <div style="position: relative; height: 240px;">
                <canvas id="chartRegistros"></canvas>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-base font-semibold text-white mb-4"><?= t('admin.ads_month') ?></h3>
            <div style="position: relative; height: 240px;">
                <canvas id="chartAnuncios"></canvas>
            </div>
        </div>
    </section>

    <!-- Estado reportes (donut) + Verificacion -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <!-- Donut reportes por estado -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-base font-semibold text-white mb-4">Estado de reportes</h3>
            <?php $totalRep = array_sum($reportsByState); ?>
            <?php if ($totalRep > 0): ?>
                <div style="position: relative; height: 220px;">
                    <canvas id="chartReportesEstado"></canvas>
                </div>
                <div class="mt-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Pendientes</span>
                        <span class="text-gray-200 font-semibold"><?= $reportsByState['pendiente'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> En revisión</span>
                        <span class="text-gray-200 font-semibold"><?= $reportsByState['en_revision'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Resueltos</span>
                        <span class="text-gray-200 font-semibold"><?= $reportsByState['resuelto'] ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span> Descartados</span>
                        <span class="text-gray-200 font-semibold"><?= $reportsByState['descartado'] ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-gray-500 text-sm">Sin reportes registrados</div>
            <?php endif; ?>
        </div>

        <!-- Verificacion (ocupa 2 columnas en lg) -->
        <div class="lg:col-span-2 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white"><?= t('admin.verification_status') ?></h3>
                <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="text-primary text-sm hover:underline"><?= t('admin.manage_verifications') ?> &rarr;</a>
            </div>
            <div class="space-y-5">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400"><?= t('admin.verified_users') ?></span>
                        <span class="text-base font-semibold text-green-400"><?= $stats['verified_users'] ?? 0 ?> / <?= $stats['users'] ?></span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full transition-all" style="width: <?= $stats['users'] > 0 ? min(100, round(($stats['verified_users'] / $stats['users']) * 100)) : 0 ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400"><?= t('admin.pending_users') ?></span>
                        <span class="text-base font-semibold text-yellow-400"><?= $stats['pending_verification'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-yellow-500 h-2.5 rounded-full transition-all" style="width: <?= $stats['users'] > 0 ? min(100, round(($stats['pending_verification'] / $stats['users']) * 100)) : 0 ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Sin verificar</span>
                        <?php $noVer = max(0, $stats['users'] - $stats['verified_users'] - $stats['pending_verification']); ?>
                        <span class="text-base font-semibold text-gray-400"><?= $noVer ?></span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-gray-500 h-2.5 rounded-full transition-all" style="width: <?= $stats['users'] > 0 ? min(100, round(($noVer / $stats['users']) * 100)) : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reportes pendientes + Actividad admin -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <!-- Reportes pendientes -->
        <div class="lg:col-span-2 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white"><?= t('admin.pending_reports') ?></h3>
                <a href="<?= url('/admin/reports') ?>?tab=usuario" class="text-primary text-sm hover:underline"><?= t('admin.view_all') ?> &rarr;</a>
            </div>
            <?php $pendingReports = is_array($pendingReports) ? $pendingReports : []; ?>
            <?php if (!empty($pendingReports)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px]">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="pb-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                                <th class="pb-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Tipo</th>
                                <th class="pb-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Reportado</th>
                                <th class="pb-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pendingReports, 0, 5) as $report): ?>
                                <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                                    <td class="py-3 text-sm text-gray-400">#<?= $report['idReporte'] ?></td>
                                    <td class="py-3">
                                        <span class="px-2.5 py-1 text-xs rounded-full font-medium
                                            <?= $report['tipo'] === 'usuario' ? 'bg-emerald-500/10 text-emerald-400' : ($report['tipo'] === 'anuncio' ? 'bg-amber-500/10 text-amber-400' : 'bg-purple-500/10 text-purple-400') ?>">
                                            <?= htmlspecialchars($report['tipo']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm text-gray-300 truncate max-w-[180px]"><?= htmlspecialchars($report['reportado_nombre'] ?? '—') ?></td>
                                    <td class="py-3 text-sm text-gray-500"><?= isset($report['creado_en']) ? date('d/m/Y', strtotime($report['creado_en'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-10 text-gray-500 text-sm"><?= t('admin.no_pending_reports') ?></div>
            <?php endif; ?>
        </div>

        <!-- Actividad admin reciente -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white">Actividad reciente</h3>
                <a href="<?= url('/admin/logs') ?>" class="text-primary text-sm hover:underline">Ver todo &rarr;</a>
            </div>
            <?php if (!empty($recentLogs)): ?>
                <ul class="space-y-4">
                    <?php foreach ($recentLogs as $log): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-xs font-bold">
                                <?= mb_strtoupper(mb_substr($log['admin_nombre'] ?? '?', 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 truncate"><?= htmlspecialchars(dashFormatLog($log['accion'], $log['entidad'] ?? '')) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?= htmlspecialchars($log['admin_nombre'] ?? 'Desconocido') ?>
                                    · <?= date('d/m H:i', strtotime($log['creado_en'])) ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="text-center py-10 text-gray-500 text-sm">Sin actividad todavía</div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ultimos anuncios + Ultimos usuarios -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ultimos anuncios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white"><?= t('admin.latest_ads') ?></h3>
                <a href="<?= url('/admin/ads') ?>" class="text-primary text-sm hover:underline"><?= t('admin.view_all') ?> &rarr;</a>
            </div>
            <?php if (!empty($recentAds)): ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($recentAds, 0, 5) as $ad): ?>
                        <div class="flex items-center justify-between bg-gray-800/60 rounded-lg px-4 py-3 border border-gray-700/30">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs px-2 py-1 rounded font-medium shrink-0 <?= $ad['tipo'] === 'ofrezco' ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400' ?>">
                                    <?= $ad['tipo'] === 'ofrezco' ? 'Ofr' : 'Bus' ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-300 truncate"><?= htmlspecialchars($ad['usuario_nombre'] ?? '—') ?></p>
                                    <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($ad['fechaPublicacion'])) ?></p>
                                </div>
                            </div>
                            <?php if ($ad['precio']): ?>
                                <span class="text-base font-semibold text-green-400 shrink-0"><?= $ad['precio'] ?>&euro;</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-10 text-gray-500 text-sm"><?= t('admin.no_recent_ads') ?></div>
            <?php endif; ?>
        </div>

        <!-- Ultimos usuarios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white"><?= t('admin.recent_users') ?></h3>
                <a href="<?= url('/admin/users') ?>" class="text-primary text-sm hover:underline"><?= t('admin.view_all') ?> &rarr;</a>
            </div>
            <?php if (!empty($recentUsers)): ?>
                <div class="space-y-3">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="flex items-center gap-3 bg-gray-800/60 rounded-lg px-4 py-3 border border-gray-700/30">
                            <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-gray-300"><?= mb_strtoupper(mb_substr($user['nombre'], 0, 1)) ?></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-200 font-medium truncate"><?= htmlspecialchars($user['nombre']) ?></p>
                                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($user['correo']) ?></p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full shrink-0
                                <?php if ($user['estado_verificacion'] == 2): ?>bg-green-500/10 text-green-400
                                <?php elseif ($user['estado_verificacion'] == 1): ?>bg-yellow-500/10 text-yellow-400
                                <?php else: ?>bg-gray-700 text-gray-400<?php endif; ?>">
                                <?= $user['estado_verificacion'] == 2 ? t('admin.verified_badge') : ($user['estado_verificacion'] == 1 ? t('admin.pending_badge') : t('admin.not_verified_badge')) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-10 text-gray-500 text-sm"><?= t('admin.no_registered_users') ?></div>
            <?php endif; ?>
        </div>
    </section>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthNames = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        function formatLabels(labels) {
            return labels.map(label => {
                const parts = label.split('-');
                if (parts.length === 2) {
                    const monthIdx = parseInt(parts[1], 10) - 1;
                    return monthNames[monthIdx] + ' ' + parts[0].slice(2);
                }
                return label;
            });
        }

        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 }, beginAtZero: true, precision: 0 } }
            }
        };

        <?php
            $regLabels = is_array($registrationsByMonth) ? array_column($registrationsByMonth, 'mes') : [];
            $regData   = is_array($registrationsByMonth) ? array_map('intval', array_column($registrationsByMonth, 'total')) : [];
            $rideLabels= is_array($ridesByMonth) ? array_column($ridesByMonth, 'mes') : [];
            $rideData  = is_array($ridesByMonth) ? array_map('intval', array_column($ridesByMonth, 'total')) : [];
        ?>

        const regLabels = formatLabels(<?= json_encode($regLabels) ?>);
        const regData   = <?= json_encode($regData) ?>;
        if (regLabels.length > 0) {
            new Chart(document.getElementById('chartRegistros'), {
                type: 'bar',
                data: { labels: regLabels, datasets: [{ data: regData,
                    backgroundColor: 'rgba(99, 102, 241, 0.5)', borderColor: 'rgb(99, 102, 241)', borderWidth: 1, borderRadius: 6 }] },
                options: chartDefaults
            });
        }

        const rideLabels = formatLabels(<?= json_encode($rideLabels) ?>);
        const rideData   = <?= json_encode($rideData) ?>;
        if (rideLabels.length > 0) {
            new Chart(document.getElementById('chartAnuncios'), {
                type: 'line',
                data: { labels: rideLabels, datasets: [{ data: rideData,
                    borderColor: 'rgb(52, 211, 153)', backgroundColor: 'rgba(52, 211, 153, 0.15)',
                    fill: true, tension: 0.4, pointRadius: 4,
                    pointBackgroundColor: 'rgb(52, 211, 153)', borderWidth: 2 }] },
                options: chartDefaults
            });
        }

        // Donut de reportes por estado
        const repEstadoEl = document.getElementById('chartReportesEstado');
        if (repEstadoEl) {
            new Chart(repEstadoEl, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes','En revisión','Resueltos','Descartados'],
                    datasets: [{
                        data: [
                            <?= (int)$reportsByState['pendiente'] ?>,
                            <?= (int)$reportsByState['en_revision'] ?>,
                            <?= (int)$reportsByState['resuelto'] ?>,
                            <?= (int)$reportsByState['descartado'] ?>
                        ],
                        backgroundColor: ['rgba(250,204,21,0.7)','rgba(96,165,250,0.7)','rgba(52,211,153,0.7)','rgba(107,114,128,0.6)'],
                        borderColor: ['rgb(250,204,21)','rgb(96,165,250)','rgb(52,211,153)','rgb(107,114,128)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
