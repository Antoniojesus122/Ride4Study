<?php $pageTitle = 'Registro de Actividad'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    // Etiquetas legibles de entidades (para filtro y columna)
    $entities = [
        'usuario'              => 'Usuario',
        'anuncio'              => 'Anuncio',
        'reporte'              => 'Reporte',
        'institucion'          => 'Institución',
        'premium'              => 'Premium',
        'configuración'        => 'Configuración',
        'notificacion'         => 'Notificación',
        'mensaje'              => 'Mensaje',
        'mensaje_institucion'  => 'Mensaje institución',
    ];

    // Genera el texto legible de una accion combinando accion + entidad.
    // Se intenta primero la combinacion exacta (accion_entidad) y luego la accion sola.
    function formatAction(string $accion, string $entidad): string {
        $combos = [
            'aprobar_verificacion'          => 'Verificación aprobada',
            'rechazar_verificacion'         => 'Verificación rechazada',
            'banear_usuario'                => 'Usuario suspendido',
            'desbanear_usuario'             => 'Usuario reactivado',
            'eliminar_usuario_usuario'      => 'Usuario eliminado',
            'eliminar_usuario'              => 'Usuario eliminado',
            'cambiar_rol_usuario'           => 'Rol cambiado',
            'conceder_premium_usuario'      => 'Premium concedido',
            'revocar_premium_usuario'       => 'Premium revocado',
            'crear_institucion'             => 'Institución creada',
            'editar_institucion'            => 'Institución editada',
            'eliminar_institucion'          => 'Institución eliminada',
            'activar_institucion'           => 'Institución activada',
            'desactivar_institucion'        => 'Institución desactivada',
            'reset_password_institucion'    => 'Contraseña regenerada',
            'eliminar_anuncio'              => 'Anuncio eliminado',
            'tomar_reporte'                 => 'Reporte asignado',
            'liberar_reporte'               => 'Reporte liberado',
            'resolver_reporte'              => 'Reporte resuelto',
            'eliminar_reporte'              => 'Reporte descartado',
            'actualizar_configuracion'      => 'Configuración actualizada',
            'enviar_notificacion_masiva_notificacion' => 'Notificación masiva enviada',
            'enviar_mensaje_institucion'    => 'Mensaje enviado a institución',
        ];

        $key = $accion . '_' . $entidad;
        if (isset($combos[$key]))    return $combos[$key];
        if (isset($combos[$accion])) return $combos[$accion];

        // Fallback: capitalizar y normalizar guiones bajos
        return ucfirst(str_replace('_', ' ', $accion));
    }

    // Color de la badge segun tipo de accion (no depende de la entidad)
    function getActionColor(string $accion): string {
        $green  = ['aprobar_verificacion', 'crear', 'conceder_premium', 'desbanear', 'activar', 'resolver_reporte'];
        $yellow = ['cambiar_rol', 'editar', 'actualizar', 'tomar_reporte', 'liberar_reporte', 'reset_password', 'desactivar'];
        $red    = ['rechazar_verificacion', 'banear', 'eliminar', 'eliminar_usuario', 'eliminar_reporte', 'revocar_premium'];
        $blue   = ['enviar', 'enviar_notificacion_masiva'];

        if (in_array($accion, $green))  return 'bg-green-500/10 text-green-400 border-green-500/20';
        if (in_array($accion, $yellow)) return 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
        if (in_array($accion, $red))    return 'bg-red-500/10 text-red-400 border-red-500/20';
        if (in_array($accion, $blue))   return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        return 'bg-gray-700 text-gray-300 border-gray-600';
    }
?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { scrollbar-width: none; -ms-overflow-style: none; }
    </style>

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between mb-6 gap-3">
        <p class="text-sm sm:text-base text-gray-400"><?= $totalLogs ?> registro<?= $totalLogs !== 1 ? 's' : '' ?></p>
        <?php
            $exportParams = array_filter([
                'periodo'      => $_GET['periodo']         ?? '',
                'date_from'    => $filters['date_from']    ?? '',
                'date_to'      => $filters['date_to']      ?? '',
                'entidad'      => $filters['entidad']      ?? '',
                'accion'       => $filters['accion']       ?? '',
                'admin_id'     => $filters['admin_id']     ?? '',
                'admin_search' => $filters['admin_search'] ?? '',
            ], fn($v) => $v !== '' && $v !== null);
            $exportQs = !empty($exportParams) ? ('?' . http_build_query($exportParams)) : '';
        ?>
        <a href="<?= url('/admin/logs/export') . $exportQs ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
            <i class="fas fa-file-csv" aria-hidden="true"></i> Exportar CSV
        </a>
    </div>

    <!-- Filtros -->
    <?php
        $logsAdvFilters = [$filters['entidad'] ?? '', $filters['admin_id'] ?? '', $filters['admin_search'] ?? '', $filters['accion'] ?? '', $filters['date_from'] ?? '', $filters['date_to'] ?? '', $_GET['periodo'] ?? ''];
        $logsActiveAdv = count(array_filter($logsAdvFilters, fn($v) => $v !== '' && $v !== null));
    ?>
    <form method="GET" action="<?= url('/admin/logs') ?>" class="mb-8">
        <!-- Toggle móvil "Más filtros" -->
        <button type="button" onclick="toggleLogsAdvFilters()"
                class="sm:hidden w-full flex items-center justify-between px-4 py-2.5 rounded-lg border border-gray-700 bg-gray-800/60 text-sm font-medium text-gray-300 hover:border-gray-600 transition-all"
                aria-expanded="<?= $logsActiveAdv > 0 ? 'true' : 'false' ?>" aria-controls="logs-adv-filters">
            <span class="flex items-center gap-2">
                <i class="fas fa-sliders text-xs text-primary" aria-hidden="true"></i>
                Más filtros
                <?php if ($logsActiveAdv > 0): ?>
                    <span class="bg-primary text-secondary text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $logsActiveAdv ?></span>
                <?php endif; ?>
            </span>
            <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform <?= $logsActiveAdv > 0 ? 'rotate-180' : '' ?>" id="logs-adv-chevron" aria-hidden="true"></i>
        </button>

        <div id="logs-adv-filters" class="<?= $logsActiveAdv > 0 ? '' : 'hidden' ?> flex flex-col sm:!flex-row sm:flex-wrap sm:items-center gap-3 mt-4 sm:mt-0">
            <?php renderPeriodFilter($_GET); ?>
            <select name="entidad" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary w-full sm:w-auto">
                <option value="">Todas las entidades</option>
                <?php foreach ($entities as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['entidad'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select name="admin_id" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary w-full sm:max-w-[200px]">
                <option value="">Todos los admin</option>
                <?php foreach (($adminList ?? []) as $a): ?>
                    <option value="<?= (int)$a['idUsuario'] ?>" <?= (int)($filters['admin_id'] ?? 0) === (int)$a['idUsuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="admin_search" value="<?= htmlspecialchars($filters['admin_search'] ?? '') ?>" placeholder="Buscar admin por nombre..."
                   class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-full sm:w-52">
            <input type="text" name="accion" value="<?= htmlspecialchars($filters['accion'] ?? '') ?>" placeholder="Buscar acción..."
                   class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-full sm:w-48">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 text-sm sm:text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
                <?php if ($logsActiveAdv > 0): ?>
                    <a href="<?= url('/admin/logs') ?>" class="text-sm text-gray-400 hover:text-gray-200 whitespace-nowrap">Limpiar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <script>
        function toggleLogsAdvFilters() {
            const w = document.getElementById('logs-adv-filters');
            const c = document.getElementById('logs-adv-chevron');
            const b = document.querySelector('[aria-controls="logs-adv-filters"]');
            const open = w.classList.contains('hidden');
            w.classList.toggle('hidden', !open);
            if (c) c.classList.toggle('rotate-180', open);
            if (b) b.setAttribute('aria-expanded', String(open));
        }
    </script>

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
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-x-auto">
            <table class="w-full text-sm min-w-[860px]">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Admin</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Acción</th>
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
                            <span class="inline-flex items-center px-2.5 py-1 text-sm rounded-full font-medium border whitespace-nowrap <?= getActionColor($log['accion']) ?>">
                                <?= htmlspecialchars(formatAction($log['accion'], $log['entidad'] ?? '')) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-gray-300"><?= htmlspecialchars($entities[$log['entidad']] ?? ucfirst(str_replace('_', ' ', $log['entidad']))) ?></span>
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

        <!-- Paginación -->
        <?php
            $queryParams = array_filter($filters, fn($v) => $v !== '' && $v !== null);
            // Incluir periodo si no está en $filters
            if (!empty($_GET['periodo'])) $queryParams['periodo'] = $_GET['periodo'];
            renderPagination((int)$page, (int)$totalPages, url('/admin/logs'), $queryParams);
        ?>
    <?php endif; ?>

    </div>
</main>
<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
