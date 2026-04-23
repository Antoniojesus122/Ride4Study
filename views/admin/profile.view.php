<?php $pageTitle = 'Mi perfil'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    // Helper local para traducir accion+entidad en el listado de ultimas acciones
    function profileFormatLog(string $accion, string $entidad): string {
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
            'resolver_reporte'           => 'Reporte resuelto',
            'tomar_reporte'              => 'Reporte asignado',
            'actualizar_configuracion'   => 'Configuración actualizada',
            'enviar_notificacion_masiva_notificacion' => 'Notificación masiva',
            'enviar_mensaje_institucion' => 'Mensaje a institución',
        ];
        $key = $accion . '_' . $entidad;
        if (isset($combos[$key]))    return $combos[$key];
        if (isset($combos[$accion])) return $combos[$accion];
        return ucfirst(str_replace('_', ' ', $accion));
    }

    $initial = mb_strtoupper(mb_substr($adminData['nombre'] ?? 'A', 0, 1));
?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10 max-w-5xl">

    <!-- Mensajes informativos -->
    <?php if ($successMsg): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['info_updated' => 'Datos actualizados correctamente', 'password_updated' => 'Contraseña cambiada correctamente'];
            echo $msgs[$successMsg] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">
            <?php
            $errors = [
                'campos_obligatorios' => 'Nombre y correo son obligatorios',
                'correo_en_uso' => 'Ese correo ya está en uso por otro usuario',
                'passwords_no_coinciden' => 'Las contraseñas no coinciden',
                'password_corta' => 'La contraseña debe tener al menos 12 caracteres',
                'password_incorrecta' => 'La contraseña actual es incorrecta',
            ];
            echo $errors[$errorMsg] ?? 'Ha ocurrido un error';
            ?>
        </div>
    <?php endif; ?>

    <!-- Hero con datos + stats -->
    <div class="bg-gradient-to-br from-gray-800/80 to-gray-800/40 border border-gray-700 rounded-xl p-7 mb-8">
        <div class="flex items-start gap-6 flex-wrap">
            <!-- Avatar -->
            <div class="w-20 h-20 rounded-full bg-primary/15 border-2 border-primary/30 flex items-center justify-center shrink-0">
                <span class="text-primary text-3xl font-black"><?= $initial ?></span>
            </div>

            <!-- Identidad -->
            <div class="flex-1 min-w-[200px]">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($adminData['nombre'] ?? '') ?></h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold bg-primary/10 text-primary border border-primary/30 rounded-full">
                        <i class="fas fa-shield-halved text-[10px]" aria-hidden="true"></i>
                        Administrador
                    </span>
                </div>
                <div class="mt-2 space-y-1 text-sm text-gray-400">
                    <p class="flex items-center gap-2">
                        <i class="fas fa-envelope text-gray-500 w-4" aria-hidden="true"></i>
                        <?= htmlspecialchars($adminData['correo'] ?? '') ?>
                    </p>
                    <?php if (!empty($adminData['telefono'])): ?>
                        <p class="flex items-center gap-2">
                            <i class="fas fa-phone text-gray-500 w-4" aria-hidden="true"></i>
                            <?= htmlspecialchars($adminData['telefono']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($adminData['creado_en'])): ?>
                        <p class="flex items-center gap-2">
                            <i class="fas fa-calendar text-gray-500 w-4" aria-hidden="true"></i>
                            Miembro desde <?= date('d/m/Y', strtotime($adminData['creado_en'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 shrink-0">
                <div class="bg-gray-900/60 border border-gray-700 rounded-lg px-5 py-4 text-center min-w-[130px]">
                    <p class="text-2xl font-bold text-white leading-none"><?= (int)($totalAcciones ?? 0) ?></p>
                    <p class="text-xs text-gray-500 mt-1.5 uppercase tracking-wider">Acciones</p>
                </div>
                <div class="bg-gray-900/60 border border-gray-700 rounded-lg px-5 py-4 text-center min-w-[130px]">
                    <p class="text-sm font-bold text-white leading-none mt-1">
                        <?= !empty($ultimaAccion) ? date('d/m/Y', strtotime($ultimaAccion)) : '—' ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-2 uppercase tracking-wider">Última</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de columnas: formularios + actividad -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna izquierda: formularios -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Información personal -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                <h3 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
                    <i class="fas fa-user-pen text-primary text-sm" aria-hidden="true"></i>
                    Información personal
                </h3>
                <form method="POST" action="<?= url('/admin/profile') ?>" class="space-y-5">
                    <input type="hidden" name="action" value="update_info">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($adminData['nombre'] ?? '') ?>" required
                                   class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Teléfono</label>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($adminData['telefono'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary" placeholder="Opcional">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Correo electrónico</label>
                        <input type="email" name="correo" value="<?= htmlspecialchars($adminData['correo'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-primary text-gray-900 rounded-lg hover:bg-primary-dark hover:text-white transition">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cambiar contraseña -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
                <h3 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
                    <i class="fas fa-key text-primary text-sm" aria-hidden="true"></i>
                    Cambiar contraseña
                </h3>
                <form method="POST" action="<?= url('/admin/profile') ?>" class="space-y-5">
                    <input type="hidden" name="action" value="change_password">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Contraseña actual</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Nueva contraseña</label>
                            <input type="password" name="new_password" required minlength="12"
                                   class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Confirmar nueva</label>
                            <input type="password" name="confirm_password" required minlength="12"
                                   class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Mínimo 12 caracteres. Usa una combinación de letras, números y símbolos.</p>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition border border-gray-700">
                            Cambiar contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Columna derecha: mis ultimas acciones -->
        <aside class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 h-fit">
            <h3 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-primary text-sm" aria-hidden="true"></i>
                Mis últimas acciones
            </h3>
            <?php if (!empty($ultimasAcciones)): ?>
                <ul class="space-y-4">
                    <?php foreach ($ultimasAcciones as $acc): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-primary mt-1.5 shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200"><?= htmlspecialchars(profileFormatLog($acc['accion'], $acc['entidad'] ?? '')) ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?= date('d/m/Y H:i', strtotime($acc['creado_en'])) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= url('/admin/logs') ?>?admin_id=<?= (int)($adminData['idUsuario'] ?? 0) ?>" class="mt-6 inline-block text-primary text-sm hover:underline">
                    Ver historial completo &rarr;
                </a>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Sin actividad todavía</p>
            <?php endif; ?>
        </aside>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
