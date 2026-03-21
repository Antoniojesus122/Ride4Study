<?php $pageTitle = 'Anuncios'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            Anuncio eliminado correctamente
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="GET" action="<?= url('/admin/ads') ?>" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Buscar usuario o localidad..."
               class="px-3 py-2 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-64">
        <select name="tipo" class="px-3 py-2 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Todos los tipos</option>
            <option value="ofrezco" <?= ($filters['tipo'] ?? '') === 'ofrezco' ? 'selected' : '' ?>>Ofrezco</option>
            <option value="busco" <?= ($filters['tipo'] ?? '') === 'busco' ? 'selected' : '' ?>>Busco</option>
        </select>
        <button type="submit" class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
        <?php if (!empty($filters['search']) || !empty($filters['tipo'])): ?>
        <a href="<?= url('/admin/ads') ?>" class="text-xs text-gray-400 hover:text-gray-200">Limpiar</a>
        <?php endif; ?>
        <span class="ml-auto text-sm text-gray-500"><?= $totalAds ?> anuncios</span>
    </form>

    <!-- Tabla con anuncios -->
    <?php if (empty($ads)): ?>
        <div class="text-center py-16 text-gray-500"><p class="text-sm">No hay anuncios</p></div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Origen</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Destino</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Precio</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Plazas</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($ads as $ad): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                        <td class="px-4 py-3 text-gray-400">#<?= $ad['idAnuncio'] ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full <?= $ad['tipo'] === 'ofrezco' ? 'bg-green-500/10 text-green-400' : 'bg-emerald-500/10 text-emerald-400' ?>">
                                <?= htmlspecialchars($ad['tipo']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-300 max-w-[120px] truncate"><?= htmlspecialchars($ad['nombreOrigen']) ?></td>
                        <td class="px-4 py-3 text-gray-300 max-w-[120px] truncate"><?= htmlspecialchars($ad['nombreDestino']) ?></td>
                        <td class="px-4 py-3 text-gray-400 text-xs"><?= date('d/m/Y', strtotime($ad['fechaSalida'])) ?></td>
                        <td class="px-4 py-3 text-gray-300"><?= htmlspecialchars($ad['usuario_nombre']) ?></td>
                        <td class="px-4 py-3 text-green-400 font-medium"><?= $ad['precio'] ? $ad['precio'] . '&euro;' : '-' ?></td>
                        <td class="px-4 py-3 text-gray-400"><?= $ad['plazasDisponibles'] ?? '-' ?></td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="<?= url('/admin/ads') ?>" class="inline" onsubmit="return confirm('Eliminar este anuncio?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $ad['idAnuncio'] ?>">
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">Eliminar</button>
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
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('/admin/ads') ?>?page=<?= $i ?>&tipo=<?= urlencode($filters['tipo'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>"
               class="px-3 py-1.5 text-xs rounded-lg transition <?= $i === $page ? 'bg-primary text-gray-900 font-bold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>
<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
