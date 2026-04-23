<?php $pageTitle = 'Mi perfil'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= htmlspecialchars($flashData['message']) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base flex items-center gap-2">
            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
            <?= htmlspecialchars($flashData['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Hero -->
    <div class="bg-gradient-to-br from-blue-500/10 via-gray-800/50 to-gray-800/50 border border-gray-700 rounded-2xl p-4 sm:p-6 lg:p-8 mb-8 flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-6">
        <?php if (!empty($inst['logo']) && is_file(__DIR__ . '/../../public/uploads/institutions/' . $inst['logo'])): ?>
            <img src="<?= url('/public/uploads/institutions/' . htmlspecialchars($inst['logo'])) ?>"
                 alt="Logo" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-blue-500/40 shrink-0">
        <?php else: ?>
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-3xl font-bold shrink-0">
                <?= strtoupper(substr($inst['nombre'], 0, 2)) ?>
            </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0 text-center sm:text-left w-full">
            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-500/15 text-blue-400 border border-blue-500/30 mb-2">
                <i class="fas fa-university mr-1" aria-hidden="true"></i> Institución
            </span>
            <h1 class="text-xl sm:text-2xl font-bold text-white truncate"><?= htmlspecialchars($inst['nombre']) ?></h1>
            <p class="text-sm text-gray-400 mt-1 break-all"><i class="fas fa-envelope mr-2 text-gray-500"></i><?= htmlspecialchars($inst['correo']) ?></p>
            <?php if (!empty($inst['telefono'])): ?>
                <p class="text-sm text-gray-400 mt-0.5 break-all"><i class="fas fa-phone mr-2 text-gray-500"></i><?= htmlspecialchars($inst['telefono']) ?></p>
            <?php endif; ?>
            <p class="text-xs text-gray-500 mt-2">
                Registrada el <?= !empty($inst['creado_en']) ? date('d/m/Y', strtotime($inst['creado_en'])) : '-' ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Datos de la institucion -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-base font-semibold text-white mb-1">Datos de la institución</h3>
            <p class="text-xs text-gray-500 mb-5">Estos datos seran visibles para el equipo de Ride4Study.</p>

            <form method="POST" action="<?= url('/institution/profile/update') ?>" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                    <input type="text" name="nombre" required maxlength="150"
                           value="<?= htmlspecialchars($inst['nombre']) ?>"
                           class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Correo electrónico *</label>
                    <input type="email" name="correo" required maxlength="150"
                           value="<?= htmlspecialchars($inst['correo']) ?>"
                           class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                        <input type="text" name="telefono" maxlength="30"
                               value="<?= htmlspecialchars($inst['telefono'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dirección</label>
                        <input type="text" name="direccion" maxlength="255"
                               value="<?= htmlspecialchars($inst['direccion'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3" maxlength="500"
                              class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500 resize-none"><?= htmlspecialchars($inst['descripcion'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Logo (JPG, PNG, WEBP · max 2MB)</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-400 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-blue-500/10 file:text-blue-400 file:font-medium hover:file:bg-blue-500/20 file:cursor-pointer">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 text-base font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-save mr-1" aria-hidden="true"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Seguridad -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-base font-semibold text-white mb-1">Seguridad</h3>
            <p class="text-xs text-gray-500 mb-5">Cambia tu contraseña periódicamente para mantener la cuenta segura.</p>

            <form method="POST" action="<?= url('/institution/profile/password') ?>" class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Contraseña actual</label>
                    <input type="password" name="actual" required
                           class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nueva contraseña (mín. 8 caracteres)</label>
                    <input type="password" name="nueva" required minlength="8"
                           class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Confirmar nueva contraseña</label>
                    <input type="password" name="confirmar" required minlength="8"
                           class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-blue-500">
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-3 text-xs text-yellow-300 flex items-start gap-2">
                    <i class="fas fa-shield-halved mt-0.5" aria-hidden="true"></i>
                    <span>Recuerda que cada inicio de sesión requiere un código 2FA que se envía a tu correo.</span>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 text-base font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 rounded-lg hover:bg-yellow-500/30 transition">
                        <i class="fas fa-key mr-1" aria-hidden="true"></i> Cambiar contraseña
                    </button>
                </div>
            </form>
        </div>

    </div>

    </div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
