<?php $pageTitle = 'Registro de Actividad'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    $actionLabels = [
        'aprobar_verificacion'  => 'Verificacion aprobada',
        'rechazar_verificacion' => 'Verificacion rechazada',
        'ban_usuario'           => 'Usuario suspendido',
        'unban_usuario'         => 'Usuario reactivado',
        'cambiar_rol'           => 'Rol cambiado',
        'eliminar_anuncio'      => 'Anuncio eliminado',
        'resolver_reporte'      => 'Reporte resuelto',
        'eliminar_reporte'      => 'Reporte eliminado',
        'tomar_reporte'         => 'Reporte asignado',
        'liberar_reporte'       => 'Reporte liberado',
        'conceder_premium'      => 'Premium concedido',
        'revocar_premium'       => 'Premium revocado',
        'crear_institucion'     => 'Institucion creada',
        'editar_institucion'    => 'Institucion editada',
        'eliminar_institucion'  => 'Institucion eliminada',
        'actualizar_config'     => 'Configuracion actualizada',
        'notificacion_masiva'   => 'Notificacion masiva',
    ];

    function getActionColor(string $accion): string {
        $green  = ['aprobar_verificacion', 'crear_institucion', 'conceder_premium', 'unban_usuario'];
        $yellow = ['cambiar_rol', 'editar_institucion', 'actualizar_config', 'tomar_reporte', 'liberar_reporte'];
        $red    = ['rechazar_verificacion', 'ban_usuario', 'eliminar_anuncio', 'eliminar_reporte', 'eliminar_institucion', 'revocar_premium'];
        $blue   = ['resolver_reporte', 'notificacion_masiva'];

        if (in_array($accion, $green))  return 'bg-green-500/10 text-green-400 border-green-500/20';
        if (in_array($accion, $yellow)) return 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
        if (in_array($accion, $red))    return 'bg-red-500/10 text-red-400 border-red-500/20';
        if (in_array($accion, $blue))   return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        return 'bg-gray-700 text-gray-300 border-gray-600';
    }

    $entities = [
        'usuario'        => 'Usuario',
        'anuncio'        => 'Anuncio',
        'reporte'        => 'Reporte',
        'institucion'    => 'Institucion',
        'premium'        => 'Premium',
        'configuracion'  => 'Configuracion',
    ];
?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= $totalLogs ?> registro<?= $totalLogs !== 1 ? 's' : '' ?></p>
    </div>

    <!-- Filtros -->
    <form method="GET" action="<?= url('/admin/logs') ?>" class="flex flex-wrap items-center gap-4 mb-6">
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
        <select name="entidad" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Todas las entidades</option>
            <?php foreach ($entities as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($filters['entidad'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="accion" value="<?= htmlspecialchars($filters['accion'] ?? '') ?>" placeholder="Buscar accion..."
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-52">
        <button type="submit" class="px-5 py-2.5 text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['entidad']) || !empty($filters['accion']) || !empty($filters['admin_id'])): ?>
        <a href="<?= url('/admin/logs') ?>" class="text-sm text-gray-400 hover:text-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla -->
    <?php if (empty($logs)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin registros</p>
            <p class="text-gray-500 text-sm mt-1">No se encontraron acciones con los filtros seleccionados</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-base">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Admin</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Accion</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Entidad</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Detalles</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                        <td class="px-5 py-4 text-gray-400 text-sm whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['creado_en'])) ?></td>
                        <td class="px-5 py-4">
                            <span class="text-gray-200 font-medium"><?= htmlspecialchars($log['admin_nombre'] ?? 'Desconocido') ?></span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-sm rounded-full font-medium border <?= getActionColor($log['accion']) ?>">
                                <?= $actionLabels[$log['accion']] ?? htmlspecialchars($log['accion']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-gray-300 capitalize"><?= htmlspecialchars($log['entidad']) ?></span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 text-sm"><?= $log['idEntidad'] ? '#' . (int)$log['idEntidad'] : '-' ?></td>
                        <td class="px-5 py-4 max-w-[250px]">
                            <?php if (!empty($log['detalles'])): ?>
                                <span class="text-gray-400 text-sm truncate block cursor-help" title="<?= htmlspecialchars($log['detalles']) ?>">
                                    <?= htmlspecialchars(mb_strimwidth($log['detalles'], 0, 60, '...')) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginacion -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php
            $queryParams = $filters;
            $queryParams = array_filter($queryParams, fn($v) => $v !== '');
            ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('/admin/logs') ?>?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>"
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
