<?php $pageTitle = 'Reportes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
$motivoLabels = [
    'spam' => 'Spam', 'ofensivo' => 'Contenido ofensivo', 'suplantacion' => 'Suplantacion',
    'inapropiado' => 'Comportamiento inapropiado', 'fraude' => 'Fraude', 'otro' => 'Otro',
];
$estadoFilter = $_GET['estado'] ?? '';
?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <!-- Mensajes -->
    <?php if ($successMsg): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?= $successMsg === 'resolved' ? 'Reporte resuelto correctamente' : 'Reporte eliminado' ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">Error: <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Pestañas de tipo -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex space-x-1 bg-gray-800/50 rounded-lg p-1">
            <?php foreach (['usuario' => 'Usuarios', 'anuncio' => 'Anuncios', 'chat' => 'Chats'] as $key => $label): ?>
            <button class="tab-button px-4 py-2 text-sm font-medium rounded-md transition
                <?= $tab === $key ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>" data-tab="<?= $key ?>">
                <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Filtro de estado -->
        <div class="flex space-x-1 bg-gray-800/50 rounded-lg p-1 ml-auto">
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= !$estadoFilter ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">Todos</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=pendiente" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= $estadoFilter === 'pendiente' ? 'bg-yellow-500/20 text-yellow-400' : 'text-gray-400 hover:text-gray-200' ?>">Pendientes</a>
            <a href="<?= url('/admin/reports') ?>?tab=<?= $tab ?>&estado=resuelto" class="px-3 py-1.5 text-xs font-medium rounded-md transition <?= $estadoFilter === 'resuelto' ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:text-gray-200' ?>">Resueltos</a>
        </div>
    </div>

    <!-- Pestaña de contenido -->
    <div id="tab-content">
        <?php foreach (['usuario' => 'Usuarios', 'anuncio' => 'Anuncios', 'chat' => 'Chats'] as $key => $label): ?>
        <div class="tab-panel <?= $key !== $tab ? 'hidden' : '' ?>" id="tab-<?= $key ?>">
            <?php
            $reportesFiltrados = array_filter($reportes, fn($r) => $r['tipo'] === $key);
            ?>

            <?php if (empty($reportesFiltrados)): ?>
                <div class="text-center py-16 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-3 opacity-40">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="text-sm">No hay reportes de <?= strtolower($label) ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($reportesFiltrados as $r): ?>
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
                        <!-- Fila principal -->
                        <div class="flex flex-col lg:flex-row lg:items-center gap-3 px-5 py-4 cursor-pointer hover:bg-gray-800/70 transition" onclick="toggleDetail(<?= $r['idReporte'] ?>)">
                            <!-- ID + Estado -->
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs text-gray-500">#<?= $r['idReporte'] ?></span>
                                <?php if ($r['estado'] === 'pendiente'): ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Pendiente</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-500/10 text-green-400 font-medium">Resuelto</span>
                                <?php endif; ?>
                            </div>

                            <!-- Motivo -->
                            <?php if (!empty($r['motivo'])): ?>
                            <span class="px-2 py-0.5 text-[10px] rounded-full bg-red-500/10 text-red-400 font-medium shrink-0">
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
                                <!-- Info reportado -->
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Reportado</p>
                                    <p class="text-sm text-gray-200 font-medium"><?= htmlspecialchars($r['reportado_nombre'] ?? 'N/A') ?></p>
                                    <?php if (!empty($r['reportado_correo'])): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($r['reportado_correo']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <!-- Info reportante -->
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Reportado por</p>
                                    <p class="text-sm text-gray-200 font-medium"><?= htmlspecialchars($r['reporta_nombre'] ?? 'N/A') ?></p>
                                </div>
                            </div>

                            <!-- Mensaje completo -->
                            <div class="mb-4">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Mensaje</p>
                                <p class="text-sm text-gray-300 bg-gray-800/60 rounded-lg px-3 py-2 border border-gray-700/30"><?= nl2br(htmlspecialchars($r['mensaje'])) ?></p>
                            </div>

                            <!-- Nota admin (si resuelto) -->
                            <?php if ($r['estado'] === 'resuelto' && !empty($r['nota_admin'])): ?>
                            <div class="mb-4">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Nota del admin</p>
                                <p class="text-sm text-gray-400 bg-gray-800/60 rounded-lg px-3 py-2 border border-gray-700/30 italic"><?= nl2br(htmlspecialchars($r['nota_admin'])) ?></p>
                            </div>
                            <?php endif; ?>

                            <!-- Acciones -->
                            <?php if ($r['estado'] === 'pendiente'): ?>
                            <div class="border-t border-gray-700/30 pt-4">
                                <form method="POST" action="<?= url('/admin/reports') ?>" class="space-y-3">
                                    <input type="hidden" name="action" value="resolve">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">
                                    <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">

                                    <!-- Accion a tomar -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs text-gray-400">Accion:</span>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer">
                                            <input type="radio" name="accion" value="resolver" checked class="accent-emerald-500"> Solo resolver
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer">
                                            <input type="radio" name="accion" value="advertir" class="accent-yellow-500"> Resolver y advertir
                                        </label>
                                        <?php if ($r['tipo'] === 'anuncio' && !empty($r['idAnuncio'])): ?>
                                        <label class="flex items-center gap-1.5 text-xs text-gray-300 cursor-pointer">
                                            <input type="radio" name="accion" value="eliminar_contenido" class="accent-red-500"> Resolver y eliminar contenido
                                        </label>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nota admin -->
                                    <input type="text" name="nota_admin" placeholder="Nota para el reportante (opcional)"
                                           class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">

                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="px-4 py-2 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
                                            Resolver reporte
                                        </button>
                                </form>
                                        <form method="POST" action="<?= url('/admin/reports') ?>" class="inline" onsubmit="return confirm('Eliminar este reporte? Esta accion no se puede deshacer.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="<?= $tab ?>">
                                            <input type="hidden" name="idReporte" value="<?= $r['idReporte'] ?>">
                                            <button type="submit" class="px-4 py-2 text-xs font-medium bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition border border-red-500/20">
                                                Descartar reporte
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
        <?php endforeach; ?>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    // Toggle detalle expandible
    function toggleDetail(id) {
        const detail = document.getElementById('detail-' + id);
        const chevron = document.getElementById('chevron-' + id);
        detail.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    // Pestañas
    const buttons = document.querySelectorAll('.tab-button');
    const panels = document.querySelectorAll('.tab-panel');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            panels.forEach(p => p.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            buttons.forEach(b => {
                b.classList.toggle('bg-gray-700', b === btn);
                b.classList.toggle('text-white', b === btn);
                b.classList.toggle('text-gray-400', b !== btn);
            });
            window.history.replaceState({}, '', `<?= url('/admin/reports') ?>?tab=${tab}<?= $estadoFilter ? '&estado=' . $estadoFilter : '' ?>`);
        });
    });
</script>
