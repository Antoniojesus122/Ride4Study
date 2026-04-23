<?php $pageTitle = 'Reportes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { scrollbar-width: none; -ms-overflow-style: none; }
</style>

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

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <!-- Mensajes -->
    <?php if ($successMsg): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
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
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">Error: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Pestañas principales-->
    <div class="mb-5">
        <nav class="flex gap-2 overflow-x-auto scrollbar-hide pb-1" aria-label="Secciones de reportes" style="scrollbar-width: none; -ms-overflow-style: none;">
            <?php
                $tabsConfig = [
                    'usuario' => ['label' => 'Usuarios', 'icon' => 'fas fa-user'],
                    'anuncio' => ['label' => 'Anuncios', 'icon' => 'fas fa-car-side'],
                    'chat'    => ['label' => 'Chats',    'icon' => 'fas fa-comment-dots'],
                    'stats'   => ['label' => 'Estadísticas', 'icon' => 'fas fa-chart-bar'],
                ];
                foreach ($tabsConfig as $key => $cfg):
                    $isActive = ($tab === $key);
            ?>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $key ?>"
               class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors border
                      <?= $isActive ? 'bg-primary/10 text-primary border-primary/20' : 'bg-transparent text-gray-400 border-transparent hover:text-white hover:bg-gray-800' ?>">
                <i class="<?= $cfg['icon'] ?> text-xs" aria-hidden="true"></i>
                <?= $cfg['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Filtro de estado + Exportar CSV -->
    <?php if ($tab !== 'stats'): ?>
    <div class="flex flex-col gap-3 mb-5">
        <!-- Pills de estado con scroll horizontal en móvil -->
        <div class="flex gap-1.5 overflow-x-auto scrollbar-hide pb-1" aria-label="Filtro de estado" style="scrollbar-width: none; -ms-overflow-style: none;">
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>" class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap <?= !$estadoFilter ? 'bg-gray-700 text-white' : 'bg-gray-800/50 text-gray-400 hover:text-gray-200' ?>">Todos</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=pendiente" class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap <?= $estadoFilter === 'pendiente' ? 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30' : 'bg-gray-800/50 text-gray-400 hover:text-gray-200' ?>">
                <i class="fas fa-clock text-[10px] mr-1" aria-hidden="true"></i> Pendientes
            </a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=en_revision" class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap <?= $estadoFilter === 'en_revision' ? 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30' : 'bg-gray-800/50 text-gray-400 hover:text-gray-200' ?>">
                <i class="fas fa-eye text-[10px] mr-1" aria-hidden="true"></i> En revisión
            </a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=resuelto" class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap <?= $estadoFilter === 'resuelto' ? 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30' : 'bg-gray-800/50 text-gray-400 hover:text-gray-200' ?>">
                <i class="fas fa-check text-[10px] mr-1" aria-hidden="true"></i> Resueltos
            </a>
        </div>

        <!-- Exportar CSV (debajo en móvil, a la derecha en desktop si hay espacio) -->
        <?php
            $exportQs = http_build_query(array_filter([
                'tab'       => $tab,
                'action'    => 'export_csv',
                'estado'    => $estadoFilter,
                'motivo'    => $_GET['motivo']    ?? '',
                'periodo'   => $_GET['periodo']   ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to'   => $_GET['date_to']   ?? '',
                'prioridad' => $_GET['prioridad'] ?? '',
                'evidencia' => $_GET['evidencia'] ?? '',
            ], fn($v) => $v !== '' && $v !== null));
        ?>
        <a href="<?= url('/admin/reports') ?>?<?= $exportQs ?>"
           class="inline-flex items-center justify-center gap-2 self-start px-4 py-2 text-sm font-semibold bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
            <i class="fas fa-file-csv" aria-hidden="true"></i> Exportar CSV
        </a>
    </div>

    <!-- Filtros -->
    <?php
        $reportsAdvFilters = [$_GET['motivo'] ?? '', $_GET['prioridad'] ?? '', $_GET['evidencia'] ?? '', $_GET['date_from'] ?? '', $_GET['date_to'] ?? '', $_GET['periodo'] ?? ''];
        $reportsActiveAdv = count(array_filter($reportsAdvFilters, fn($v) => $v !== '' && $v !== null));
    ?>
    <form method="GET" action="<?= url('/admin/reports') ?>" class="mb-8">
        <input type="hidden" name="tab" value="<?= $tab ?>">
        <?php if ($estadoFilter): ?><input type="hidden" name="estado" value="<?= htmlspecialchars($estadoFilter) ?>"><?php endif; ?>

        <!-- Toggle móvil "Más filtros" -->
        <button type="button" onclick="toggleReportsAdvFilters()"
                class="sm:hidden w-full flex items-center justify-between px-4 py-2.5 rounded-lg border border-gray-700 bg-gray-800/60 text-sm font-medium text-gray-300 hover:border-gray-600 transition-all"
                aria-expanded="<?= $reportsActiveAdv > 0 ? 'true' : 'false' ?>" aria-controls="reports-adv-filters">
            <span class="flex items-center gap-2">
                <i class="fas fa-sliders text-xs text-primary" aria-hidden="true"></i>
                Más filtros
                <?php if ($reportsActiveAdv > 0): ?>
                    <span class="bg-primary text-secondary text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $reportsActiveAdv ?></span>
                <?php endif; ?>
            </span>
            <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform <?= $reportsActiveAdv > 0 ? 'rotate-180' : '' ?>" id="reports-adv-chevron" aria-hidden="true"></i>
        </button>

        <div id="reports-adv-filters" class="<?= $reportsActiveAdv > 0 ? '' : 'hidden' ?> flex flex-col sm:!flex-row sm:flex-wrap sm:items-center gap-3 mt-4 sm:mt-0">
            <select name="motivo" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 focus:outline-none focus:border-primary w-full sm:w-auto">
                <option value="">Todos los motivos</option>
                <?php foreach ($motivoLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['motivo'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select name="prioridad" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 focus:outline-none focus:border-primary w-full sm:w-auto">
                <option value="">Cualquier prioridad</option>
                <option value="urgente" <?= ($_GET['prioridad'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                <option value="alta"    <?= ($_GET['prioridad'] ?? '') === 'alta'    ? 'selected' : '' ?>>Alta</option>
                <option value="media"   <?= ($_GET['prioridad'] ?? '') === 'media'   ? 'selected' : '' ?>>Media</option>
                <option value="baja"    <?= ($_GET['prioridad'] ?? '') === 'baja'    ? 'selected' : '' ?>>Baja</option>
            </select>
            <select name="evidencia" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 focus:outline-none focus:border-primary w-full sm:w-auto">
                <option value="">Evidencia</option>
                <option value="con" <?= ($_GET['evidencia'] ?? '') === 'con' ? 'selected' : '' ?>>Con evidencia</option>
                <option value="sin" <?= ($_GET['evidencia'] ?? '') === 'sin' ? 'selected' : '' ?>>Sin evidencia</option>
            </select>
            <?php renderPeriodFilter($_GET); ?>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 text-sm sm:text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
                <?php if ($reportsActiveAdv > 0): ?>
                    <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?><?= $estadoFilter ? '&estado=' . urlencode($estadoFilter) : '' ?>" class="text-sm text-gray-400 hover:text-gray-200 whitespace-nowrap">Limpiar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <script>
        function toggleReportsAdvFilters() {
            const w = document.getElementById('reports-adv-filters');
            const c = document.getElementById('reports-adv-chevron');
            const b = document.querySelector('[aria-controls="reports-adv-filters"]');
            const open = w.classList.contains('hidden');
            w.classList.toggle('hidden', !open);
            if (c) c.classList.toggle('rotate-180', open);
            if (b) b.setAttribute('aria-expanded', String(open));
        }
    </script>
    <?php endif; ?>

    <!-- Contenido de la pestaña activa -->
    <div>
        <?php if ($tab === 'stats'): ?>
        <!-- Pestaña Estadísticas -->
        <div>
            <!-- Cards resumen -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                    <p class="text-4xl font-bold text-white"><?= $stats['total'] ?? 0 ?></p>
                    <p class="text-sm text-gray-400 mt-2">Total reportes</p>
                </div>
                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-7">
                    <p class="text-4xl font-bold text-yellow-400"><?= $stats['pendientes'] ?? 0 ?></p>
                    <p class="text-sm text-gray-400 mt-2">Pendientes</p>
                </div>
                <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-7">
                    <p class="text-4xl font-bold text-blue-400"><?= $stats['en_revision'] ?? 0 ?></p>
                    <p class="text-sm text-gray-400 mt-2">En revision</p>
                </div>
                <div class="bg-green-500/5 border border-green-500/20 rounded-xl p-7">
                    <p class="text-4xl font-bold text-green-400"><?= $stats['resueltos'] ?? 0 ?></p>
                    <p class="text-sm text-gray-400 mt-2">Resueltos</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Tiempo medio -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-clock text-purple-400 text-sm" aria-hidden="true"></i> Tiempo medio de resolucion</h4>
                    <p class="text-4xl font-bold text-purple-400"><?= $stats['tiempo_medio_horas'] ?? 0 ?> <span class="text-base font-normal text-gray-400">horas</span></p>
                </div>

                <!-- Por prioridad -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-layer-group text-orange-400 text-sm" aria-hidden="true"></i> Pendientes por prioridad</h4>
                    <div class="space-y-3">
                        <?php foreach (['urgente', 'alta', 'media', 'baja'] as $p):
                            $count = $stats['por_prioridad'][$p] ?? 0;
                            $cfg = $prioridadConfig[$p];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-<?= $cfg['color'] ?>-400 flex items-center gap-2"><i class="<?= $cfg['icon'] ?> text-sm"></i> <?= $cfg['label'] ?></span>
                            <span class="text-base font-bold text-gray-200"><?= $count ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Por motivo -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2"><i class="fas fa-tags text-cyan-400 text-sm" aria-hidden="true"></i> Motivos más frecuentes</h4>
                    <div class="space-y-3">
                        <?php
                        $totalMotivos = array_sum(array_column($stats['por_motivo'] ?? [], 'total'));
                        foreach (($stats['por_motivo'] ?? []) as $m):
                            $pct = $totalMotivos > 0 ? round(($m['total'] / $totalMotivos) * 100) : 0;
                        ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-300"><?= htmlspecialchars($motivoLabels[$m['motivo']] ?? $m['motivo']) ?></span>
                                <span class="text-sm text-gray-500"><?= $m['total'] ?> (<?= $pct ?>%)</span>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-500/60 rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Usuarios más reportados -->
            <?php if (!empty($stats['usuarios_mas_reportados'])): ?>
            <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                <h4 class="text-lg font-semibold text-white mb-5 flex items-center gap-2"><i class="fas fa-user-shield text-red-400 text-sm" aria-hidden="true"></i> Usuarios más reportados</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-base">
                        <thead><tr class="text-gray-500 text-sm uppercase tracking-wider border-b border-gray-700">
                            <th class="pb-3 text-left">Usuario</th>
                            <th class="pb-3 text-center">Total reportes</th>
                            <th class="pb-3 text-center">Pendientes</th>
                            <th class="pb-3 text-right">Acciones</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($stats['usuarios_mas_reportados'] as $u): ?>
                        <tr class="border-b border-gray-700/30">
                            <td class="py-3.5 text-gray-200 font-medium"><?= htmlspecialchars($u['nombre'] ?? 'N/A') ?></td>
                            <td class="py-3.5 text-center text-gray-300"><?= $u['total_reportes'] ?></td>
                            <td class="py-3.5 text-center">
                                <?php if ($u['pendientes'] > 0): ?>
                                <span class="px-2.5 py-1 text-sm rounded-full bg-yellow-500/10 text-yellow-400"><?= $u['pendientes'] ?></span>
                                <?php else: ?>
                                <span class="text-gray-500">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 text-right">
                                <button onclick="previewContent('usuario', <?= $u['idUsuarioReportado'] ?>)" class="text-sm text-primary hover:text-primary-light underline">Ver perfil</button>
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
            <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                <h4 class="text-lg font-semibold text-white mb-5 flex items-center gap-2"><i class="fas fa-chart-line text-green-400 text-sm" aria-hidden="true"></i> Reportes por semana (ultimas 8 semanas)</h4>
                <div class="flex items-end gap-3 h-40">
                    <?php
                    $maxWeek = max(array_column($stats['por_semana'], 'total'));
                    foreach ($stats['por_semana'] as $w):
                        $height = $maxWeek > 0 ? round(($w['total'] / $maxWeek) * 100) : 0;
                    ?>
                    <div class="flex-1 flex flex-col items-center gap-1.5">
                        <span class="text-sm text-gray-400"><?= $w['total'] ?></span>
                        <div class="w-full bg-primary/30 rounded-t-md transition-all" style="height: <?= max($height, 4) ?>%"></div>
                        <span class="text-sm text-gray-500"><?= date('d/m', strtotime($w['fecha_inicio'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- Pestaña de reportes activa -->
        <?php
        $label = match($tab) { 'anuncio' => 'Anuncios', 'chat' => 'Chats', default => 'Usuarios' };
        $reportesFiltrados = $reportes;
        ?>
        <div>

            <?php if (empty($reportesFiltrados)): ?>
                <div class="text-center py-20">
                    <div class="w-14 h-14 bg-gray-700/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-gray-400 font-medium">Sin reportes</p>
                    <p class="text-gray-500 text-sm mt-1">No hay reportes de <?= strtolower($label) ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($reportesFiltrados as $r):
                        $pCfg = $prioridadConfig[$r['prioridad'] ?? 'media'] ?? $prioridadConfig['media'];
                    ?>
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden <?= ($r['prioridad'] ?? '') === 'urgente' ? 'border-l-2 border-l-red-500' : '' ?>">
                        <!-- Fila principal -->
                        <div class="flex flex-col lg:flex-row lg:items-center gap-3 px-5 py-4 cursor-pointer hover:bg-gray-800/70 transition" onclick="toggleDetail(<?= $r['idReporte'] ?>)">
                            <!-- ID + Estado + Prioridad -->
                            <div class="flex items-center gap-2.5 shrink-0">
                                <span class="text-sm text-gray-500">#<?= $r['idReporte'] ?></span>

                                <!-- Prioridad -->
                                <span class="px-2.5 py-1 text-sm rounded-full bg-<?= $pCfg['color'] ?>-500/10 text-<?= $pCfg['color'] ?>-400 font-medium flex items-center gap-1.5" title="Prioridad: <?= $pCfg['label'] ?>">
                                    <i class="<?= $pCfg['icon'] ?> text-sm"></i> <?= $pCfg['label'] ?>
                                </span>

                                <!-- Estado -->
                                <?php if ($r['estado'] === 'pendiente'): ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Pendiente</span>
                                <?php elseif ($r['estado'] === 'en_revision'): ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-blue-500/10 text-blue-400 font-medium flex items-center gap-1.5">
                                        <i class="fas fa-eye text-sm" aria-hidden="true"></i> En revision<?= !empty($r['admin_nombre']) ? ' (' . htmlspecialchars($r['admin_nombre']) . ')' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-green-500/10 text-green-400 font-medium">Resuelto</span>
                                <?php endif; ?>

                                <!-- Evidencia indicator -->
                                <?php if (!empty($r['evidencia_img'])): ?>
                                <span class="text-sm text-gray-500" title="Tiene evidencia adjunta"><i class="fas fa-image" aria-hidden="true"></i></span>
                                <?php endif; ?>
                            </div>

                            <!-- Motivo -->
                            <?php if (!empty($r['motivo'])): ?>
                            <span class="px-2.5 py-1 text-sm rounded-full bg-red-500/10 text-red-400 font-medium shrink-0">
                                <?= htmlspecialchars($motivoLabels[$r['motivo']] ?? $r['motivo']) ?>
                            </span>
                            <?php endif; ?>

                            <!-- Usuario reportado -->
                            <div class="flex-1 min-w-0">
                                <span class="text-base text-gray-300">
                                    <?= htmlspecialchars($r['reportado_nombre'] ?? 'N/A') ?>
                                    <?php if ($r['tipo'] === 'anuncio' && !empty($r['anuncio_origen'])): ?>
                                        <span class="text-gray-500 text-sm ml-1">(<?= htmlspecialchars($r['anuncio_origen']) ?> &rarr; <?= htmlspecialchars($r['anuncio_destino'] ?? '') ?>)</span>
                                    <?php elseif ($r['tipo'] === 'chat'): ?>
                                        <span class="text-gray-500 text-sm ml-1"><?= empty($r['idChat']) ? '(Conversacion)' : '(Mensaje)' ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- Usuario que reporta -->
                            <span class="text-sm text-gray-500 shrink-0">por <?= htmlspecialchars($r['reporta_nombre'] ?? 'N/A') ?></span>

                            <!-- Fecha -->
                            <span class="text-sm text-gray-500 shrink-0"><?= date('d/m/Y H:i', strtotime($r['creado_en'])) ?></span>

                            <!-- Flechita -->
                            <svg id="chevron-<?= $r['idReporte'] ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-500 shrink-0 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>

                        <!-- Detalle expandible -->
                        <div id="detail-<?= $r['idReporte'] ?>" class="hidden border-t border-gray-700/50 px-5 py-4 bg-gray-900/30">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <!-- Info reportado con link directo -->
                                <div>
                                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-1.5">Reportado</p>
                                    <p class="text-base text-gray-200 font-medium">
                                        <?= htmlspecialchars($r['reportado_nombre'] ?? 'N/A') ?>
                                        <?php if (!empty($r['idUsuarioReportado'])): ?>
                                        <button onclick="previewContent('usuario', <?= $r['idUsuarioReportado'] ?>)" class="ml-1.5 text-primary hover:text-primary-light underline text-xs"><i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i> Ver perfil</button>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($r['reportado_correo'])): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($r['reportado_correo']) ?></p>
                                    <?php endif; ?>

                                    <!-- Ver contenido reportado -->
                                    <?php if ($r['tipo'] === 'anuncio' && !empty($r['idAnuncio'])): ?>
                                    <button onclick="previewContent('anuncio', <?= $r['idAnuncio'] ?>)" class="inline-flex items-center gap-1 mt-1 text-xs text-blue-400 hover:text-blue-300 underline">
                                        <i class="fas fa-car text-xs" aria-hidden="true"></i> Ver anuncio #<?= $r['idAnuncio'] ?>
                                    </button>
                                    <?php elseif ($r['tipo'] === 'chat' && !empty($r['idChat'])): ?>
                                    <button onclick="previewContent('chat_msg', <?= $r['idChat'] ?>)" class="inline-flex items-center gap-1 mt-1 text-xs text-blue-400 hover:text-blue-300 underline">
                                        <i class="fas fa-comment text-xs" aria-hidden="true"></i> Ver mensaje reportado
                                    </button>
                                    <?php elseif ($r['tipo'] === 'chat' && empty($r['idChat']) && !empty($r['idAnuncio'])): ?>
                                    <button onclick="previewContent('chat_conv', <?= $r['idAnuncio'] ?>, <?= (int)$r['idUsuarioReportado'] ?>)" class="inline-flex items-center gap-1 mt-1 text-xs text-blue-400 hover:text-blue-300 underline">
                                        <i class="fas fa-comments text-xs" aria-hidden="true"></i> Ver conversacion reportada
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Info reportante -->
                                <div>
                                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-1.5">Reportado por</p>
                                    <p class="text-base text-gray-200 font-medium">
                                        <?= htmlspecialchars($r['reporta_nombre'] ?? 'N/A') ?>
                                        <?php if (!empty($r['idUsuarioQueReporta'])): ?>
                                        <button onclick="previewContent('usuario', <?= $r['idUsuarioQueReporta'] ?>)" class="ml-1.5 text-primary hover:text-primary-light underline text-xs"><i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i> Ver perfil</button>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Mensaje completo -->
                            <div class="mb-5">
                                <p class="text-sm text-gray-500 uppercase tracking-wider mb-1.5">Mensaje</p>
                                <p class="text-base text-gray-300 bg-gray-800/60 rounded-lg px-4 py-3 border border-gray-700/30"><?= nl2br(htmlspecialchars($r['mensaje'])) ?></p>
                            </div>

                            <!-- Evidencia adjunta -->
                            <?php if (!empty($r['evidencia_img'])): ?>
                            <div class="mb-5">
                                <p class="text-sm text-gray-500 uppercase tracking-wider mb-1.5">Evidencia adjunta</p>
                                <a href="<?= url('/public/uploads/reports/' . $r['evidencia_img']) ?>" target="_blank" class="inline-block">
                                    <img src="<?= url('/public/uploads/reports/' . $r['evidencia_img']) ?>" alt="Evidencia" class="max-w-sm max-h-48 rounded-lg border border-gray-700 hover:border-primary transition cursor-pointer">
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- Botón historial del usuario reportado -->
                            <?php if (!empty($r['idUsuarioReportado'])): ?>
                            <div class="mb-5">
                                <button onclick="loadHistory(<?= $r['idUsuarioReportado'] ?>, <?= $r['idReporte'] ?>)" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-2 transition">
                                    <i class="fas fa-history text-sm" aria-hidden="true"></i> Ver historial de sanciones del usuario
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
                                    Acción: <span class="font-medium text-gray-200"><?= match($r['accion_tomada']) {
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
                                <!-- Botón "Tomar reporte" -->
                                <form method="POST" action="<?= url('/admin/reports') ?>" class="mb-3">
                                    <input type="hidden" name="action" value="take">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">
                                    <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                    <button type="submit" class="text-sm px-4 py-2 font-medium bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition border border-blue-500/20 flex items-center gap-2">
                                        <i class="fas fa-hand-paper text-sm" aria-hidden="true"></i> Tomar reporte (asignarme)
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

                                    <!-- Acción a tomar -->
                                    <div>
                                        <span class="text-sm text-gray-400 block mb-2.5">Acción a tomar:</span>
                                        <div class="flex flex-wrap gap-2.5">
                                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-emerald-500/50 has-[:checked]:bg-emerald-500/5">
                                                <input type="radio" name="accion" value="resolver" checked class="accent-emerald-500"> Solo resolver
                                            </label>
                                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-yellow-500/50 has-[:checked]:bg-yellow-500/5">
                                                <input type="radio" name="accion" value="advertir" class="accent-yellow-500"> Advertir
                                            </label>
                                            <?php if ($r['tipo'] === 'anuncio' && !empty($r['idAnuncio'])): ?>
                                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-orange-500/50 has-[:checked]:bg-orange-500/5">
                                                <input type="radio" name="accion" value="eliminar_contenido" class="accent-orange-500"> Eliminar contenido
                                            </label>
                                            <?php endif; ?>
                                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5" onclick="toggleSuspension(<?= $r['idReporte'] ?>, true)">
                                                <input type="radio" name="accion" value="suspender" class="accent-red-500"> Suspender temporal
                                            </label>
                                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5">
                                                <input type="radio" name="accion" value="banear" class="accent-red-500"> Ban permanente
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Dias de suspension (solo visible si se selecciona suspender) -->
                                    <div id="suspension-days-<?= $r['idReporte'] ?>" class="hidden">
                                        <label class="text-sm text-gray-400 block mb-1.5">Dias de suspension:</label>
                                        <div class="flex items-center gap-2.5">
                                            <?php foreach ([3, 7, 15, 30] as $d): ?>
                                            <label class="flex items-center gap-1.5 text-sm text-gray-300 cursor-pointer px-3 py-1.5 bg-gray-800 rounded-md border border-gray-700 hover:border-gray-600 transition has-[:checked]:border-red-500/50 has-[:checked]:bg-red-500/5">
                                                <input type="radio" name="dias_suspension" value="<?= $d ?>" <?= $d === 7 ? 'checked' : '' ?> class="accent-red-500"> <?= $d ?>d
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Nota admin -->
                                    <input type="text" name="nota_admin" placeholder="Nota para el reportante (opcional)"
                                           class="w-full px-4 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">

                                    <div class="flex items-center gap-3">
                                        <button type="submit" class="text-sm px-4 py-2 font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
                                            Resolver reporte
                                        </button>
                                </form>
                                        <!-- Liberar reporte -->
                                        <form method="POST" action="<?= url('/admin/reports') ?>" class="inline">
                                            <input type="hidden" name="action" value="release">
                                            <input type="hidden" name="tab" value="<?= $tab ?>">
                                            <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                            <button type="submit" class="text-sm px-4 py-2 font-medium bg-gray-700/50 text-gray-400 rounded-lg hover:bg-gray-700 transition border border-gray-600">
                                                Liberar
                                            </button>
                                        </form>
                                        <!-- Descartar reporte -->
                                        <form method="POST" action="<?= url('/admin/reports') ?>" class="inline" data-confirm="Eliminar este reporte? Esta accion no se puede deshacer." data-danger>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="<?= $tab ?>">
                                            <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                            <button type="submit" class="text-sm px-4 py-2 font-medium bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition border border-red-500/20">
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

<!-- Modal de vista previa -->
<div id="preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60" onclick="if(event.target===this)closePreview()">
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-7 max-w-xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white" id="preview-title">Vista previa</h3>
            <button onclick="closePreview()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div id="preview-body" class="text-gray-300">
            <p class="text-gray-500">Cargando...</p>
        </div>
    </div>
</div>

<!-- Modal de historial -->
<div id="historyModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" onclick="if(event.target===this)closeHistoryModal()">
    <div class="bg-[#1a1b26] border border-gray-700 rounded-2xl shadow-2xl max-w-xl w-full max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2"><i class="fas fa-history text-purple-400" aria-hidden="true"></i> Historial de sanciones</h3>
            <button type="button" onclick="closeHistoryModal()" class="text-gray-500 hover:text-gray-300 text-lg" aria-label="Cerrar historial"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <div id="historyModalContent" class="p-5 overflow-y-auto flex-1">
            <div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></div>
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
        content.innerHTML = '<div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Cargando...</div>';

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

    // Vista previa del contenido reportado
    function previewContent(tipo, id, extraId) {
        const modal = document.getElementById('preview-modal');
        const body = document.getElementById('preview-body');
        const title = document.getElementById('preview-title');
        body.innerHTML = '<div class="flex items-center justify-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i> Cargando...</div>';
        modal.classList.remove('hidden');

        let url = '<?= url("/admin/reports") ?>?ajax=preview&tipo=' + tipo + '&id=' + id;
        if (extraId) url += '&extraId=' + extraId;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.error || !data._tipo) {
                    body.innerHTML = '<p class="text-red-400">No se pudo cargar el contenido. Es posible que haya sido eliminado.</p>';
                    return;
                }
                if (data._tipo === 'usuario') {
                    title.textContent = 'Perfil de usuario';
                    const verificado = data.estado_verificacion == 2
                        ? '<span class="px-2.5 py-1 text-xs rounded-full bg-green-500/10 text-green-400 font-medium">Verificado</span>'
                        : data.estado_verificacion == 1
                            ? '<span class="px-2.5 py-1 text-xs rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Pendiente</span>'
                            : '<span class="px-2.5 py-1 text-xs rounded-full bg-gray-600/30 text-gray-400 font-medium">No verificado</span>';
                    const premium = data.premium == 1 ? '<span class="px-2.5 py-1 text-xs rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Premium</span>' : '';
                    const baneado = data.baneado == 1 ? '<span class="px-2.5 py-1 text-xs rounded-full bg-red-500/10 text-red-400 font-medium">Baneado</span>' : '';
                    const fotoHtml = data.fotoPerfil
                        ? `<img src="<?= url('/') ?>public/uploads/profile/${encodeURIComponent(data.fotoPerfil)}" alt="${(data.nombre||'Usuario').replace(/"/g,'&quot;')}" class="w-14 h-14 rounded-full object-cover border-2 border-gray-700">`
                        : `<div class="w-14 h-14 rounded-full bg-gray-700 flex items-center justify-center text-xl font-bold text-primary">${(data.nombre||'?')[0].toUpperCase()}</div>`;
                    body.innerHTML = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                ${fotoHtml}
                                <div>
                                    <p class="text-white font-semibold text-lg">${data.nombre || 'Sin nombre'}</p>
                                    <p class="text-gray-400 text-sm">${data.correo || ''}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">${verificado}${premium}${baneado}</div>
                            <div class="bg-gray-900/60 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                                <div><p class="text-gray-500 text-xs mb-0.5">ID</p><p class="text-gray-300 font-medium">#${data.idUsuario}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Rol</p><p class="text-gray-300 font-medium">${data.nombreRol || 'Usuario'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Ciudad</p><p class="text-gray-300 font-medium">${data.ciudad || '-'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Teléfono</p><p class="text-gray-300 font-medium">${data.teléfono || '-'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Institución</p><p class="text-gray-300 font-medium">${data.institucion || '-'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Registro</p><p class="text-gray-300 font-medium">${data.creado_en ? new Date(data.creado_en).toLocaleDateString('es-ES') : '-'}</p></div>
                            </div>
                        </div>`;
                } else if (data._tipo === 'anuncio') {
                    title.textContent = 'Detalle del anuncio';
                    body.innerHTML = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg ${data.tipo === 'ofrezco' ? 'bg-green-500/10' : 'bg-emerald-500/10'} flex items-center justify-center">
                                    <i class="fas fa-car ${data.tipo === 'ofrezco' ? 'text-green-400' : 'text-emerald-400'}" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium ${data.tipo === 'ofrezco' ? 'bg-green-500/10 text-green-400' : 'bg-emerald-500/10 text-emerald-400'}">${data.tipo}</span>
                                    <span class="text-gray-500 text-sm ml-1">#${data.idAnuncio}</span>
                                </div>
                            </div>
                            <div class="bg-gray-900/60 rounded-xl p-4">
                                <p class="text-white font-semibold text-base flex items-center gap-2">${data.nombreOrigen} <i class="fas fa-arrow-right text-xs text-gray-500" aria-hidden="true"></i> ${data.nombreDestino}</p>
                                <p class="text-gray-400 text-sm mt-2">${data.descripcion || 'Sin descripcion'}</p>
                            </div>
                            <div class="bg-gray-900/60 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                                <div><p class="text-gray-500 text-xs mb-0.5">Publicado por</p><p class="text-gray-300 font-medium">${data.usuario_nombre}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Precio</p><p class="text-green-400 font-semibold">${data.precio ? data.precio + '\u20ac' : 'Gratis'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Plazas</p><p class="text-gray-300 font-medium">${data.plazasDisponibles ?? '-'}</p></div>
                                <div><p class="text-gray-500 text-xs mb-0.5">Fecha salida</p><p class="text-gray-300 font-medium">${data.fechaSalida ? new Date(data.fechaSalida).toLocaleDateString('es-ES') : '-'}</p></div>
                                ${data.horaSalida ? `<div><p class="text-gray-500 text-xs mb-0.5">Hora</p><p class="text-gray-300 font-medium">${data.horaSalida.substring(0,5)}</p></div>` : ''}
                            </div>
                        </div>`;
                } else if (data._tipo === 'chat') {
                    const isConvReport = !data.reported_message_id;
                    title.textContent = isConvReport ? 'Conversacion reportada' : 'Mensaje reportado';
                    let msgsHtml = '';
                    const reportedMsgId = data.reported_message_id || null;
                    if (data.mensajes && data.mensajes.length) {
                        msgsHtml = data.mensajes.map(m => {
                            const isReported = reportedMsgId && m.idMensaje == reportedMsgId;
                            const borderClass = isReported ? 'border-l-2 border-l-red-500 bg-red-500/5' : 'bg-gray-900/60';
                            return `
                            <div class="rounded-lg px-3 py-2.5 ${borderClass}">
                                ${isReported ? '<p class="text-[10px] text-red-400 font-semibold uppercase tracking-wider mb-1">Mensaje reportado</p>' : ''}
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-primary">${m.emisor_nombre}</span>
                                    <span class="text-[10px] text-gray-600">${m.fechaCreacion}</span>
                                </div>
                                <p class="text-gray-300 text-sm leading-relaxed">${m.mensaje}</p>
                            </div>`;
                        }).join('');
                    } else {
                        msgsHtml = '<p class="text-gray-500 text-sm text-center py-4">No hay mensajes</p>';
                    }
                    body.innerHTML = `
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <i class="fas fa-comments text-gray-500" aria-hidden="true"></i>
                                <span class="font-medium text-gray-300">${data.user1_nombre || '?'}</span>
                                <span>&harr;</span>
                                <span class="font-medium text-gray-300">${data.user2_nombre || '?'}</span>
                            </div>
                            <p class="text-xs text-gray-500">${isConvReport ? 'Mostrando mensajes recientes de la conversacion' : 'Mostrando mensajes alrededor del mensaje reportado'}</p>
                            <div class="max-h-72 overflow-y-auto space-y-2 pr-1">${msgsHtml}</div>
                        </div>`;
                }
            })
            .catch(() => {
                body.innerHTML = '<p class="text-red-400">Error al cargar la vista previa.</p>';
            });
    }

    function closePreview() {
        document.getElementById('preview-modal').classList.add('hidden');
    }

</script>
