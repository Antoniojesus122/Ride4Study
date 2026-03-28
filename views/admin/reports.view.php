<?php $pageTitle = 'Reportes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
$motivoLabels = [
    'spam' => 'Spam', 'ofensivo' => 'Contenido ofensivo', 'suplantacion' => 'Suplantacion',
    'inapropiado' => 'Comportamiento inapropiado', 'fraude' => 'Fraude', 'otro' => 'Otro',
];
$prioridadConfig = [
    'urgente' => ['color' => 'red',    'icon' => 'fas fa-fire',           'label' => 'Urgente'],
    'alta'    => ['color' => 'orange', 'icon' => 'fas fa-arrow-up',       'label' => 'Alta'],
    'media'   => ['color' => 'yellow', 'icon' => 'fas fa-minus',          'label' => 'Media'],
    'baja'    => ['color' => 'blue',   'icon' => 'fas fa-arrow-down',     'label' => 'Baja'],
];
$estadoFilter = $_GET['estado'] ?? '';
$adminId = $_SESSION['user_id'] ?? 0;
?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <!-- Mensajes -->
    <?php if ($successMsg): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            echo match($successMsg) {
                'resolved' => 'Reporte resuelto correctamente',
                'deleted' => 'Reporte eliminado',
                'assigned' => 'Reporte asignado - ahora esta en revision',
                'released' => 'Reporte liberado',
                default => $successMsg,
            };
            ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">Error: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Pestanas de tipo -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex space-x-1 bg-gray-800/50 rounded-lg p-1">
            <?php foreach (['usuario' => 'Usuarios', 'anuncio' => 'Anuncios', 'chat' => 'Chats', 'stats' => 'Estadisticas'] as $key => $label): ?>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $key ?>" class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1.5
                <?= $tab === $key ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                <?php if ($key === 'stats'): ?><i class="fas fa-chart-bar text-xs"></i><?php endif; ?>
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Filtro de estado -->
        <?php if ($tab !== 'stats'): ?>
        <div class="flex space-x-1 bg-gray-800/50 rounded-lg p-1 ml-auto">
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= !$estadoFilter ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">Todos</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=pendiente" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= $estadoFilter === 'pendiente' ? 'bg-yellow-500/20 text-yellow-400' : 'text-gray-400 hover:text-gray-200' ?>">Pendientes</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=en_revision" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= $estadoFilter === 'en_revision' ? 'bg-blue-500/20 text-blue-400' : 'text-gray-400 hover:text-gray-200' ?>">En revision</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=resuelto" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= $estadoFilter === 'resuelto' ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:text-gray-200' ?>">Resueltos</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Contenido del tab activo -->
    <div>
        <?php if ($tab === 'stats'): ?>
        <!-- Tab Estadisticas -->
        <div>
            <!-- Cards resumen -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                    <p class="text-3xl font-bold text-white"><?= $stats['total'] ?? 0 ?></p>
                    <p class="text-xs text-gray-400 mt-1">Total reportes</p>
                </div>
                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-5">
                    <p class="text-3xl font-bold text-yellow-400"><?= $stats['pendientes'] ?? 0 ?></p>
                    <p class="text-xs text-gray-400 mt-1">Pendientes</p>
                </div>
                <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-5">
                    <p class="text-3xl font-bold text-blue-400"><?= $stats['en_revision'] ?? 0 ?></p>
                    <p class="text-xs text-gray-400 mt-1">En revision</p>
                </div>
                <div class="bg-green-500/5 border border-green-500/20 rounded-xl p-5">
                    <p class="text-3xl font-bold text-green-400"><?= $stats['resueltos'] ?? 0 ?></p>
                    <p class="text-xs text-gray-400 mt-1">Resueltos</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Tiempo medio -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                    <h4 class="text-base font-semibold text-white mb-3 flex items-center gap-2"><i class="fas fa-clock text-purple-400 text-xs"></i> Tiempo medio de resolucion</h4>
                    <p class="text-3xl font-bold text-purple-400"><?= $stats['tiempo_medio_horas'] ?? 0 ?> <span class="text-sm font-normal text-gray-400">horas</span></p>
                </div>

                <!-- Por prioridad -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                    <h4 class="text-base font-semibold text-white mb-3 flex items-center gap-2"><i class="fas fa-layer-group text-orange-400 text-xs"></i> Pendientes por prioridad</h4>
                    <div class="space-y-2">
                        <?php foreach (['urgente', 'alta', 'media', 'baja'] as $p):
                            $count = $stats['por_prioridad'][$p] ?? 0;
                            $cfg = $prioridadConfig[$p];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-<?= $cfg['color'] ?>-400 flex items-center gap-1.5"><i class="<?= $cfg['icon'] ?> text-xs"></i> <?= $cfg['label'] ?></span>
                            <span class="text-sm font-bold text-gray-200"><?= $count ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Por motivo -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                    <h4 class="text-base font-semibold text-white mb-3 flex items-center gap-2"><i class="fas fa-tags text-cyan-400 text-xs"></i> Motivos mas frecuentes</h4>
                    <div class="space-y-2">
                        <?php
                        $totalMotivos = array_sum(array_column($stats['por_motivo'] ?? [], 'total'));
                        foreach (($stats['por_motivo'] ?? []) as $m):
                            $pct = $totalMotivos > 0 ? round(($m['total'] / $totalMotivos) * 100) : 0;
                        ?>
                        <div>
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-xs text-gray-300"><?= htmlspecialchars($motivoLabels[$m['motivo']] ?? $m['motivo']) ?></span>
                                <span class="text-xs text-gray-500"><?= $m['total'] ?> (<?= $pct ?>%)</span>
                            </div>
                            <div class="h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-500/60 rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Usuarios mas reportados -->
            <?php if (!empty($stats['usuarios_mas_reportados'])): ?>
            <div class="mt-6 bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                <h4 class="text-base font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-user-shield text-red-400 text-xs"></i> Usuarios mas reportados</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-gray-700">
                            <th class="pb-2 text-left">Usuario</th>
                            <th class="pb-2 text-center">Total reportes</th>
                            <th class="pb-2 text-center">Pendientes</th>
                            <th class="pb-2 text-right">Acciones</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($stats['usuarios_mas_reportados'] as $u): ?>
                        <tr class="border-b border-gray-700/30">
                            <td class="py-2.5 text-gray-200 font-medium"><?= htmlspecialchars($u['nombre'] ?? 'N/A') ?></td>
                            <td class="py-2.5 text-center text-gray-300"><?= $u['total_reportes'] ?></td>
                            <td class="py-2.5 text-center">
                                <?php if ($u['pendientes'] > 0): ?>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-500/10 text-yellow-400"><?= $u['pendientes'] ?></span>
                                <?php else: ?>
                                <span class="text-gray-500">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2.5 text-right">
                                <a href="<?= url('/profile/' . $u['idUsuarioReportado']) ?>" target="_blank" class="text-xs text-primary hover:underline">Ver perfil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Reportes por semana -->
            <?php if (!empty($stats['por_semana'])): ?>
            <div class="mt-6 bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                <h4 class="text-base font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-chart-line text-green-400 text-xs"></i> Reportes por semana (ultimas 8 semanas)</h4>
                <div class="flex items-end gap-2 h-32">
                    <?php
                    $maxWeek = max(array_column($stats['por_semana'], 'total'));
                    foreach ($stats['por_semana'] as $w):
                        $height = $maxWeek > 0 ? round(($w['total'] / $maxWeek) * 100) : 0;
                    ?>
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs text-gray-400"><?= $w['total'] ?></span>
                        <div class="w-full bg-primary/30 rounded-t-md transition-all" style="height: <?= max($height, 4) ?>%"></div>
                        <span class="text-xs text-gray-500"><?= date('d/m', strtotime($w['fecha_inicio'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- Tab de reportes activo -->
        <?php
        $label = match($tab) { 'anuncio' => 'Anuncios', 'chat' => 'Chats', default => 'Usuarios' };
        $reportesFiltrados = $reportes;
        ?>
        <div>

            <?php if (empty($reportesFiltrados)): ?>
                <div class="text-center py-16 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 opacity-40">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="text-sm">No hay reportes de <?= strtolower($label) ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($reportesFiltrados as $r):
                        $pCfg = $prioridadConfig[$r['prioridad'] ?? 'media'] ?? $prioridadConfig['media'];
                    ?>
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden <?= ($r['prioridad'] ?? '') === 'urgente' ? 'border-l-2 border-l-red-500' : '' ?>">
                        <!-- Fila principal -->
                        <div class="flex flex-col lg:flex-row lg:items-center gap-3 px-5 py-4 cursor-pointer hover:bg-gray-800/70 transition" onclick="toggleDetail(<?= $r['idReporte'] ?>)">
                            <!-- ID + Estado + Prioridad -->
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-gray-500">#<?= $r['idReporte'] ?></span>

                                <!-- Prioridad -->
                                <span class="px-1.5 py-0.5 text-xs rounded-full bg-<?= $pCfg['color'] ?>-500/10 text-<?= $pCfg['color'] ?>-400 font-medium flex items-center gap-1" title="Prioridad: <?= $pCfg['label'] ?>">
                                    <i class="<?= $pCfg['icon'] ?> text-xs"></i> <?= $pCfg['label'] ?>
                                </span>

                                <!-- Estado -->
                                <?php if ($r['estado'] === 'pendiente'): ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Pendiente</span>
                                <?php elseif ($r['estado'] === 'en_revision'): ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-500/10 text-blue-400 font-medium flex items-center gap-1">
                                        <i class="fas fa-eye text-xs"></i> En revision<?= !empty($r['admin_nombre']) ? ' (' . htmlspecialchars($r['admin_nombre']) . ')' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-500/10 text-green-400 font-medium">Resuelto</span>
                                <?php endif; ?>

                                <!-- Evidencia indicator -->
                                <?php if (!empty($r['evidencia_img'])): ?>
                                <span class="text-xs text-gray-500" title="Tiene evidencia adjunta"><i class="fas fa-image"></i></span>
                                <?php endif; ?>
                            </div>

                            <!-- Motivo -->
                            <?php if (!empty($r['motivo'])): ?>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-red-500/10 text-red-400 font-medium shrink-0">
                                <?= htmlspecialchars($motivoLabels[$r['motivo']] ?? $r['motivo']) ?>
                            </span>
                            <?php endif; ?>

                            <!-- Usuario reportado -->
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-gray-300">
                                    <?= htmlspecialchars($r['reportado_nombre'] ?? 'N/A') ?>
                                    <?php if ($r['tipo'] === 'anuncio' && !empty($r['anuncio_origen'])): ?>
                                        <span class="text-gray-500 text-xs ml-1">(<?= htmlspecialchars($r['anuncio_origen']) ?> &rarr; <?= htmlspecialchars($r['anuncio_destino'] ?? '') ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- Usuario que reporta -->
                            <span class="text-xs text-gray-500 shrink-0">por <?= htmlspecialchars($r['reporta_nombre'] ?? 'N/A') ?></span>

                            <!-- Fecha -->
                            <span class="text-xs text-gray-500 shrink-0"><?= date('d/m/Y H:i', strtotime($r['creado_en'])) ?></span>

                            <!-- Flechita -->
                            <svg id="chevron-<?= $r['idReporte'] ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-500 shrink-0 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>

                        <!-- Detalle expandible -->
                        <div id="detail-<?= $r['idReporte'] ?>" class="hidden border-t border-gray-700/50 px-5 py-4 bg-gray-900/30">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Info reportado con link directo -->
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reportado</p>
                                    <p class="text-sm text-gray-200 font-medium">
                                        <?= htmlspecialchars($r['reportado_nombre'] ?? 'N/A') ?>
                                        <?php if (!empty($r['idUsuarioReportado'])): ?>
                                        <a href="<?= url('/profile/' . $r['idUsuarioReportado']) ?>" target="_blank" class="ml-1.5 text-primary hover:underline text-xs"><i class="fas fa-external-link-alt text-xs"></i> Ver perfil</a>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($r['reportado_correo'])): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($r['reportado_correo']) ?></p>
                                    <?php endif; ?>

                                    <!-- Link directo al contenido reportado -->
                                    <?php if ($r['tipo'] === 'anuncio' && !empty($r['idAnuncio'])): ?>
                                    <a href="<?= url('/ride/' . $r['idAnuncio']) ?>" target="_blank" class="inline-flex items-center gap-1 mt-1 text-xs text-blue-400 hover:underline">
                                        <i class="fas fa-car text-xs"></i> Ver anuncio #<?= $r['idAnuncio'] ?>
                                    </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Info reportante -->
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reportado por</p>
                                    <p class="text-sm text-gray-200 font-medium">
                                        <?= htmlspecialchars($r['reporta_nombre'] ?? 'N/A') ?>
                                        <?php if (!empty($r['idUsuarioQueReporta'])): ?>
                                        <a href="<?= url('/profile/' . $r['idUsuarioQueReporta']) ?>" target="_blank" class="ml-1.5 text-primary hover:underline text-xs"><i class="fas fa-external-link-alt text-xs"></i> Ver perfil</a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Mensaje completo -->
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Mensaje</p>
                                <p class="text-sm text-gray-300 bg-gray-800/60 rounded-lg px-3 py-2 border border-gray-700/30"><?= nl2br(htmlspecialchars($r['mensaje'])) ?></p>
                            </div>

                            <!-- Evidencia adjunta -->
                            <?php if (!empty($r['evidencia_img'])): ?>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Evidencia adjunta</p>
                                <a href="<?= url('/public/uploads/reports/' . $r['evidencia_img']) ?>" target="_blank" class="inline-block">
                                    <img src="<?= url('/public/uploads/reports/' . $r['evidencia_img']) ?>" alt="Evidencia" class="max-w-sm max-h-48 rounded-lg border border-gray-700 hover:border-primary transition cursor-pointer">
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- Boton historial del usuario reportado -->
                            <?php if (!empty($r['idUsuarioReportado'])): ?>
                            <div class="mb-4">
                                <button onclick="loadHistory(<?= $r['idUsuarioReportado'] ?>, <?= $r['idReporte'] ?>)" class="text-xs text-purple-400 hover:text-purple-300 flex items-center gap-1.5 transition">
                                    <i class="fas fa-history text-xs"></i> Ver historial de sanciones del usuario
                                </button>
                                <div id="history-<?= $r['idReporte'] ?>" class="hidden mt-3"></div>
                            </div>
                            <?php endif; ?>

                            <!-- Nota admin y accion tomada (si resuelto) -->
                            <?php if ($r['estado'] === 'resuelto'): ?>
                            <div class="mb-4 p-3 bg-green-500/5 border border-green-500/20 rounded-lg">
                                <p class="text-xs text-green-400 uppercase tracking-wider mb-1 font-medium">Resuelto<?= !empty($r['resuelto_en']) ? ' el ' . date('d/m/Y H:i', strtotime($r['resuelto_en'])) : '' ?></p>
                                <?php if (!empty($r['accion_tomada'])): ?>
                                <p class="text-xs text-gray-300 mb-1">
                                    Accion: <span class="font-medium text-gray-200"><?= match($r['accion_tomada']) {
                                        'advertir' => 'Advertencia enviada',
                                        'eliminar_contenido' => 'Contenido eliminado',
                                        'suspender' => 'Usuario suspendido',
                                        'banear' => 'Usuario baneado',
                                        default => 'Solo resuelto',
                                    } ?></span>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($r['nota_admin'])): ?>
                                <p class="text-sm text-gray-400 italic"><?= nl2br(htmlspecialchars($r['nota_admin'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Acciones -->
                            <?php if ($r['estado'] === 'pendiente'): ?>
                            <div class="border-t border-gray-700/30 pt-4">
                                <!-- Boton "Tomar reporte" -->
                                <form method="POST" action="<?= url('/admin/reports') ?>" class="mb-3">
                                    <input type="hidden" name="action" value="take">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">
                                    <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                    <button type="submit" class="px-4 py-2 text-xs font-medium bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition border border-blue-500/20 flex items-center gap-1.5">
                                        <i class="fas fa-hand-paper text-xs"></i> Tomar reporte (asignarme)
                                    </button>
                                </form>
                            </div>

                            <?php elseif ($r['estado'] === 'en_revision'): ?>
                            <div class="border-t border-gray-700/30 pt-4">
                                <!-- Form resolver -->
                                <form method="POST" action="<?= url('/admin/reports') ?>" class="space-y-3">
                                    <input type="hidden" name="action" value="resolve">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">
                                    <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">

                                    <!-- Accion a tomar -->
                                    <div>
                                        <span class="text-xs text-gray-400 block mb-2">Accion a tomar:</span>
                                        <div class="flex flex-wrap gap-2">
                                            <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-emerald-500/50 has-[:checked]:bg-emerald-500/5">
                                                <input type="radio" name="accion" value="resolver" checked class="accent-emerald-500"> Solo resolver
                                            </label>
                                            <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-yellow-500/50 has-[:checked]:bg-yellow-500/5">
                                                <input type="radio" name="accion" value="advertir" class="accent-yellow-500"> Advertir
                                            </label>
                                            <?php if ($r['tipo'] === 'anuncio' && !empty($r['idAnuncio'])): ?>
                                            <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-orange-500/50 has-[:checked]:bg-orange-500/5">
                                                <input type="radio" name="accion" value="eliminar_contenido" class="accent-orange-500"> Eliminar contenido
                                            </label>
                                            <?php endif; ?>
                                            <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5" onclick="toggleSuspension(<?= $r['idReporte'] ?>, true)">
                                                <input type="radio" name="accion" value="suspender" class="accent-red-500"> Suspender temporal
                                            </label>
                                            <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5">
                                                <input type="radio" name="accion" value="banear" class="accent-red-500"> Ban permanente
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Dias de suspension (solo visible si se selecciona suspender) -->
                                    <div id="suspension-days-<?= $r['idReporte'] ?>" class="hidden">
                                        <label class="text-xs text-gray-400 block mb-1">Dias de suspension:</label>
                                        <div class="flex items-center gap-2">
                                            <?php foreach ([3, 7, 15, 30] as $d): ?>
                                            <label class="flex items-center gap-1 text-xs text-gray-300 cursor-pointer px-2.5 py-1 bg-gray-800 rounded-md border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5">
                                                <input type="radio" name="dias_suspension" value="<?= $d ?>" <?= $d === 7 ? 'checked' : '' ?> class="accent-red-500"> <?= $d ?>d
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Nota admin -->
                                    <input type="text" name="nota_admin" placeholder="Nota para el reportante (opcional)"
                                           class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">

                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="px-4 py-2 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
                                            Resolver reporte
                                        </button>
                                </form>
                                        <!-- Liberar reporte -->
                                        <form method="POST" action="<?= url('/admin/reports') ?>" class="inline">
                                            <input type="hidden" name="action" value="release">
                                            <input type="hidden" name="tab" value="<?= $tab ?>">
                                            <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                            <button type="submit" class="px-4 py-2 text-xs font-medium bg-gray-700/50 text-gray-400 rounded-lg hover:bg-gray-700 transition border border-gray-600">
                                                Liberar
                                            </button>
                                        </form>
                                        <!-- Descartar reporte -->
                                        <form method="POST" action="<?= url('/admin/reports') ?>" class="inline" onsubmit="return confirm('Eliminar este reporte? Esta accion no se puede deshacer.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="<?= $tab ?>">
                                            <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                            <button type="submit" class="px-4 py-2 text-xs font-medium bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition border border-red-500/20">
                                                Descartar
                                            </button>
                                        </form>
                                    </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
</main>

<!-- Modal de historial -->
<div id="historyModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" onclick="if(event.target===this)closeHistoryModal()">
    <div class="bg-[#1a1b26] border border-gray-700 rounded-2xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-4 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2"><i class="fas fa-history text-purple-400"></i> Historial de sanciones</h3>
            <button onclick="closeHistoryModal()" class="text-gray-500 hover:text-gray-300"><i class="fas fa-times"></i></button>
        </div>
        <div id="historyModalContent" class="p-4 overflow-y-auto flex-1">
            <div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    // Toggle detalle expandible
    function toggleDetail(id) {
        const detail = document.getElementById('detail-' + id);
        const chevron = document.getElementById('chevron-' + id);
        detail.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    // Toggle dias de suspension
    function toggleSuspension(reportId, show) {
        document.getElementById('suspension-days-' + reportId)?.classList.toggle('hidden', !show);
    }

    // Escuchar cambios en radios de accion para mostrar/ocultar dias suspension
    document.querySelectorAll('input[name="accion"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const form = this.closest('form');
            const reportId = form.querySelector('input[name="idReporte"]').value;
            const suspDiv = document.getElementById('suspension-days-' + reportId);
            if (suspDiv) suspDiv.classList.toggle('hidden', this.value !== 'suspender');
        });
    });

    // Cargar historial de sanciones de un usuario via AJAX
    function loadHistory(userId, reportId) {
        const modal = document.getElementById('historyModal');
        const content = document.getElementById('historyModalContent');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        content.innerHTML = '<div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

        fetch(`<?= url('/admin/reports') ?>?ajax=history&userId=${userId}`)
        .then(r => r.json())
        .then(data => {
            let html = '';

            // Resumen de sanciones
            const s = data.sanciones;
            const totalSanciones = (s.advertir || 0) + (s.eliminar_contenido || 0) + (s.suspender || 0) + (s.banear || 0);

            html += `<div class="grid grid-cols-2 gap-2 mb-4">
                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-lg p-2.5 text-center">
                    <p class="text-lg font-bold text-yellow-400">${s.advertir || 0}</p>
                    <p class="text-xs text-gray-400">Advertencias</p>
                </div>
                <div class="bg-orange-500/5 border border-orange-500/20 rounded-lg p-2.5 text-center">
                    <p class="text-lg font-bold text-orange-400">${s.eliminar_contenido || 0}</p>
                    <p class="text-xs text-gray-400">Contenido eliminado</p>
                </div>
                <div class="bg-red-500/5 border border-red-500/20 rounded-lg p-2.5 text-center">
                    <p class="text-lg font-bold text-red-400">${s.suspender || 0}</p>
                    <p class="text-xs text-gray-400">Suspensiones</p>
                </div>
                <div class="bg-red-500/5 border border-red-500/20 rounded-lg p-2.5 text-center">
                    <p class="text-lg font-bold text-red-400">${s.banear || 0}</p>
                    <p class="text-xs text-gray-400">Bans</p>
                </div>
            </div>`;

            if (totalSanciones === 0 && data.historial.length === 0) {
                html += '<p class="text-center text-gray-500 text-sm py-4">Este usuario no tiene historial previo.</p>';
            }

            // Timeline de reportes
            if (data.historial.length > 0) {
                html += '<p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Historial de reportes</p>';
                html += '<div class="space-y-2">';
                const motivoLabels = {spam:'Spam',ofensivo:'Ofensivo',suplantacion:'Suplantacion',inapropiado:'Inapropiado',fraude:'Fraude',otro:'Otro'};
                const estadoColors = {pendiente:'yellow',en_revision:'blue',resuelto:'green'};
                const accionLabels = {advertir:'Advertido',eliminar_contenido:'Contenido eliminado',suspender:'Suspendido',banear:'Baneado',resolver:'Resuelto'};

                data.historial.forEach(h => {
                    const color = estadoColors[h.estado] || 'gray';
                    const fecha = new Date(h.creado_en).toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit',year:'numeric'});
                    html += `<div class="flex items-start gap-3 p-2 bg-gray-800/40 rounded-lg border border-gray-700/30">
                        <div class="w-2 h-2 rounded-full bg-${color}-400 mt-1.5 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-medium text-gray-200">#${h.idReporte}</span>
                                <span class="px-1.5 py-0.5 text-xs rounded-full bg-${color}-500/10 text-${color}-400">${h.estado}</span>
                                ${h.motivo ? `<span class="text-xs text-gray-400">${motivoLabels[h.motivo] || h.motivo}</span>` : ''}
                                ${h.accion_tomada ? `<span class="text-xs text-red-400 font-medium">${accionLabels[h.accion_tomada] || h.accion_tomada}</span>` : ''}
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">por ${h.reporta_nombre || 'N/A'} - ${fecha}</p>
                        </div>
                    </div>`;
                });
                html += '</div>';
            }

            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = '<p class="text-center text-red-400 text-sm py-4">Error al cargar el historial</p>';
        });
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

</script>
