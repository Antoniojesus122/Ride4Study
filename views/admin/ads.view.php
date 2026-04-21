<?php $pageTitle = 'Anuncios'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['deleted' => 'Anuncio eliminado correctamente'];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base"><?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= $totalAds ?> anuncios</p>
        <div class="flex items-center gap-3">
            <?php
                // Construimos el query string para exportar respetando todos los filtros
                $exportQs = http_build_query(array_filter([
                    'action'      => 'export_csv',
                    'tipo'        => $filters['tipo'] ?? '',
                    'search'      => $filters['search'] ?? '',
                    'periodo'     => $_GET['periodo'] ?? '',
                    'date_from'   => $filters['date_from'] ?? '',
                    'date_to'     => $filters['date_to'] ?? '',
                    'estado'      => $filters['estado'] ?? '',
                    'precio'      => $filters['precio'] ?? '',
                    'institucion' => $filters['institucion'] ?? '',
                ], fn($v) => $v !== '' && $v !== null));
            ?>
            <a href="<?= url('/admin/ads') ?>?<?= $exportQs ?>"
               class="px-4 py-2.5 text-base font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">Exportar CSV</a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="<?= url('/admin/ads') ?>" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Buscar usuario o localidad..."
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-64">
        <select name="tipo" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Todos los tipos</option>
            <option value="ofrezco" <?= ($filters['tipo'] ?? '') === 'ofrezco' ? 'selected' : '' ?>>Ofrezco</option>
            <option value="busco" <?= ($filters['tipo'] ?? '') === 'busco' ? 'selected' : '' ?>>Busco</option>
        </select>
        <select name="estado" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Cualquier estado</option>
            <option value="futuros" <?= ($filters['estado'] ?? '') === 'futuros' ? 'selected' : '' ?>>Futuros</option>
            <option value="activos" <?= ($filters['estado'] ?? '') === 'activos' ? 'selected' : '' ?>>Hoy</option>
            <option value="pasados" <?= ($filters['estado'] ?? '') === 'pasados' ? 'selected' : '' ?>>Pasados</option>
        </select>
        <select name="precio" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Precio</option>
            <option value="con_precio" <?= ($filters['precio'] ?? '') === 'con_precio' ? 'selected' : '' ?>>Con precio</option>
            <option value="gratis" <?= ($filters['precio'] ?? '') === 'gratis' ? 'selected' : '' ?>>Gratis</option>
        </select>
        <select name="institucion" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary max-w-[200px]">
            <option value="">Todas las instituciones</option>
            <?php foreach (($instituciones ?? []) as $inst): ?>
                <option value="<?= htmlspecialchars($inst['nombre']) ?>" <?= ($filters['institucion'] ?? '') === $inst['nombre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($inst['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php renderPeriodFilter($_GET); ?>
        <button type="submit" class="px-5 py-2.5 text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
        <?php $hasFilters = array_filter([$filters['search'] ?? '', $filters['tipo'] ?? '', $filters['date_from'] ?? '', $filters['date_to'] ?? '', $filters['estado'] ?? '', $filters['precio'] ?? '', $filters['institucion'] ?? '']); ?>
        <?php if (!empty($hasFilters)): ?>
            <a href="<?= url('/admin/ads') ?>" class="text-sm text-gray-400 hover:text-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla con anuncios -->
    <?php if (empty($ads)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0H3m10 0h2m4 0a1 1 0 001-1v-4a1 1 0 00-.3-.7l-3-3A1 1 0 0016 7h-3v9m4 0H13"/></svg>
            </div>
            <p class="text-gray-400 font-medium">No hay anuncios</p>
            <p class="text-gray-500 text-sm mt-1">No se encontraron anuncios con los filtros seleccionados</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Tipo</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Origen</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Destino</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Usuario</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Precio</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Plazas</th>
                    <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($ads as $ad): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                        <td class="px-5 py-4 text-gray-400">#<?= $ad['idAnuncio'] ?></td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-0.5 text-sm rounded-full <?= $ad['tipo'] === 'ofrezco' ? 'bg-green-500/10 text-green-400' : 'bg-emerald-500/10 text-emerald-400' ?>">
                                <?= htmlspecialchars($ad['tipo']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-300 max-w-[120px] truncate"><?= htmlspecialchars($ad['nombreOrigen']) ?></td>
                        <td class="px-5 py-4 text-gray-300 max-w-[120px] truncate"><?= htmlspecialchars($ad['nombreDestino']) ?></td>
                        <td class="px-5 py-4 text-gray-400 text-base"><?= date('d/m/Y', strtotime($ad['fechaSalida'])) ?></td>
                        <td class="px-5 py-4 text-gray-300"><?= htmlspecialchars($ad['usuario_nombre']) ?></td>
                        <td class="px-5 py-4 text-green-400 font-medium"><?= $ad['precio'] ? $ad['precio'] . '&euro;' : '-' ?></td>
                        <td class="px-5 py-4 text-gray-400"><?= $ad['plazasDisponibles'] ?? '-' ?></td>
                        <td class="px-5 py-4 text-right">
                            <form method="POST" action="<?= url('/admin/ads') ?>" class="inline" data-confirm="Eliminar este anuncio?" data-danger>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $ad['idAnuncio'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-sm font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagicación -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php
                $pagQs = http_build_query(array_filter([
                    'tipo'        => $filters['tipo'] ?? '',
                    'search'      => $filters['search'] ?? '',
                    'periodo'     => $_GET['periodo'] ?? '',
                    'date_from'   => $filters['date_from'] ?? '',
                    'date_to'     => $filters['date_to'] ?? '',
                    'estado'      => $filters['estado'] ?? '',
                    'precio'      => $filters['precio'] ?? '',
                    'institucion' => $filters['institucion'] ?? '',
                ], fn($v) => $v !== '' && $v !== null));
            ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('/admin/ads') ?>?page=<?= $i ?><?= $pagQs ? '&' . $pagQs : '' ?>"
               class="px-4 py-2 text-sm rounded-lg transition <?= $i === $page ? 'bg-primary text-gray-900 font-bold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>
<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
