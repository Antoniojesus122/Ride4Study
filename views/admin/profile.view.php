<?php $pageTitle = 'Mi perfil'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8 max-w-2xl">

    <!-- Mensajes informativos -->
    <?php if ($successMsg): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['info_updated' => 'Datos actualizados correctamente', 'password_updated' => 'Contrasena cambiada correctamente'];
            echo $msgs[$successMsg] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
            <?php
            $errors = [
                'campos_obligatorios' => 'Nombre y correo son obligatorios',
                'correo_en_uso' => 'Ese correo ya esta en uso por otro usuario',
                'passwords_no_coinciden' => 'Las contrasenas no coinciden',
                'password_corta' => 'La contrasena debe tener al menos 6 caracteres',
                'password_incorrecta' => 'La contrasena actual es incorrecta',
            ];
            echo $errors[$errorMsg] ?? 'Ha ocurrido un error';
            ?>
        </div>
    <?php endif; ?>

    <!-- Informacion personal -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center">
                <span class="text-primary text-xl font-bold"><?= mb_strtoupper(mb_substr($adminData['nombre'] ?? 'A', 0, 1)) ?></span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-white"><?= htmlspecialchars($adminData['nombre'] ?? '') ?></h2>
                <p class="text-sm text-gray-400">Administrador</p>
            </div>
        </div>

        <h3 class="text-sm font-semibold text-white mb-4">Informacion personal</h3>
        <form method="POST" action="<?= url('/admin/profile') ?>" class="space-y-4">
            <input type="hidden" name="action" value="update_info">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($adminData['nombre'] ?? '') ?>" required
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Correo electronico</label>
                <input type="email" name="correo" value="<?= htmlspecialchars($adminData['correo'] ?? '') ?>" required
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Telefono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($adminData['telefono'] ?? '') ?>"
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary" placeholder="Opcional">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-primary text-gray-900 rounded-lg hover:bg-primary-dark transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Cambiar contraseña -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Cambiar contrasena</h3>
        <form method="POST" action="<?= url('/admin/profile') ?>" class="space-y-4">
            <input type="hidden" name="action" value="change_password">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Contrasena actual</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Nueva contrasena</label>
                <input type="password" name="new_password" required minlength="6"
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Confirmar nueva contrasena</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition border border-gray-600">
                    Cambiar contrasena
                </button>
            </div>
        </form>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
