<?php $pageTitle = 'Premium'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?= ($_GET['success'] === 'granted') ? 'Premium concedido correctamente' : 'Premium revocado' ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">Usuario no encontrado.</div>
    <?php endif; ?>

    <!-- Estadisticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
        <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-yellow-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $totalPremium ?></p>
                    <p class="text-xs text-gray-400">Usuarios Premium activos</p>
                </div>
            </div>
        </div>
        <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $expiringCount ?></p>
                    <p class="text-xs text-gray-400">Expiran en 7 dias</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hacer a un usuario premium -->
    <div class="mb-6 bg-gray-800/50 border border-gray-700 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-3">Conceder Premium manualmente</h3>
        <form method="POST" action="<?= url('/admin/premium') ?>" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="action" value="grant">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs text-gray-400 mb-1 block">ID del usuario</label>
                <input type="number" name="user_id" required placeholder="ID usuario" class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>
            <div class="w-32">
                <label class="text-xs text-gray-400 mb-1 block">Dias</label>
                <input type="number" name="days" value="30" min="1" max="365" class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium bg-yellow-500 text-gray-900 rounded-lg hover:bg-yellow-400 transition">Conceder Premium</button>
        </form>
    </div>

    <!-- Tabla de usuarios premium -->
    <?php if (empty($premiumUsers)): ?>
        <div class="text-center py-16 text-gray-500"><p class="text-sm">No hay usuarios Premium activos</p></div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Correo</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Expira</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Estado</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($premiumUsers as $pu):
                        $expiresAt = $pu['premium_hasta'] ? strtotime($pu['premium_hasta']) : null;
                        $daysLeft = $expiresAt ? max(0, (int)ceil(($expiresAt - time()) / 86400)) : null;
                        $isExpiring = $daysLeft !== null && $daysLeft <= 7;
                    ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                        <td class="px-4 py-3 text-gray-400">#<?= $pu['idUsuario'] ?></td>
                        <td class="px-4 py-3 text-gray-200 font-medium"><?= htmlspecialchars($pu['nombre']) ?></td>
                        <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($pu['correo']) ?></td>
                        <td class="px-4 py-3 text-xs <?= $isExpiring ? 'text-red-400' : 'text-gray-400' ?>">
                            <?= $pu['premium_hasta'] ? date('d/m/Y H:i', $expiresAt) : 'Sin limite' ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($isExpiring): ?>
                                <span class="px-2 py-0.5 text-[10px] rounded-full bg-red-500/10 text-red-400 font-medium"><?= $daysLeft ?> dias</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-500/10 text-green-400 font-medium">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="<?= url('/admin/premium') ?>" class="inline" onsubmit="return confirm('Revocar Premium de <?= htmlspecialchars(addslashes($pu['nombre'])) ?>?');">
                                <input type="hidden" name="action" value="revoke">
                                <input type="hidden" name="user_id" value="<?= $pu['idUsuario'] ?>">
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">Revocar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>
</main>
<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
