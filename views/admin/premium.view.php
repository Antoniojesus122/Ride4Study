<?php $pageTitle = 'Premium'; ?>
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
            $msgs = ['granted' => 'Premium concedido correctamente', 'revoked' => 'Premium revocado'];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base"><?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <!-- Pestañas -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex space-x-1.5 bg-gray-800/50 rounded-lg p-1.5">
            <a href="<?= url('/admin/premium') ?>?tab=usuarios" class="px-5 py-2.5 text-base font-medium rounded-md transition <?= ($tab ?? 'usuarios') === 'usuarios' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                Usuarios Premium
                <span class="ml-1.5 text-sm px-2 py-0.5 rounded-full bg-yellow-500/20 text-yellow-400"><?= $totalPremium ?></span>
            </a>
            <a href="<?= url('/admin/premium') ?>?tab=pagos" class="px-5 py-2.5 text-base font-medium rounded-md transition <?= ($tab ?? '') === 'pagos' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                Historial de pagos
            </a>
        </div>
    </div>

    <?php if (($tab ?? 'usuarios') === 'usuarios'): ?>
    <!-- Estadisticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-7">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-yellow-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-4xl font-bold text-white"><?= $totalPremium ?></p>
                    <p class="text-sm text-gray-400">Usuarios Premium activos</p>
                </div>
            </div>
        </div>
        <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-7">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-4xl font-bold text-white"><?= $expiringCount ?></p>
                    <p class="text-sm text-gray-400">Expiran en 7 dias</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hacer a un usuario premium -->
    <div class="mb-8 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
        <h3 class="text-lg font-semibold text-white mb-3">Conceder Premium manualmente</h3>
        <form method="POST" action="<?= url('/admin/premium') ?>" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="action" value="grant">
            <div class="flex-1 min-w-[200px]">
                <label class="text-sm text-gray-400 mb-1 block">ID del usuario</label>
                <input type="number" name="user_id" required placeholder="ID usuario" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>
            <div class="w-32">
                <label class="text-sm text-gray-400 mb-1 block">Dias</label>
                <input type="number" name="days" value="30" min="1" max="365" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            </div>
            <button type="submit" class="px-5 py-2.5 text-base font-medium bg-yellow-500 text-gray-900 rounded-lg hover:bg-yellow-400 transition">Conceder Premium</button>
        </form>
    </div>

    <!-- Tabla de usuarios premium -->
    <?php if (empty($premiumUsers)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </div>
            <p class="text-gray-400 font-medium">No hay usuarios Premium activos</p>
            <p class="text-sm text-gray-500">Concede Premium manualmente desde el formulario superior</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Usuario</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Correo</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Expira</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($premiumUsers as $pu):
                        $expiresAt = $pu['premium_hasta'] ? strtotime($pu['premium_hasta']) : null;
                        $daysLeft = $expiresAt ? max(0, (int)ceil(($expiresAt - time()) / 86400)) : null;
                        $isExpiring = $daysLeft !== null && $daysLeft <= 7;
                    ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                        <td class="px-5 py-4 text-gray-400">#<?= $pu['idUsuario'] ?></td>
                        <td class="px-5 py-4 text-gray-200 font-medium"><?= htmlspecialchars($pu['nombre']) ?></td>
                        <td class="px-5 py-4 text-gray-400"><?= htmlspecialchars($pu['correo']) ?></td>
                        <td class="px-5 py-4 text-xs <?= $isExpiring ? 'text-red-400' : 'text-gray-400' ?>">
                            <?= $pu['premium_hasta'] ? date('d/m/Y H:i', $expiresAt) : 'Sin limite' ?>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($isExpiring): ?>
                                <span class="px-2 py-0.5 text-sm rounded-full bg-red-500/10 text-red-400 font-medium"><?= $daysLeft ?> dias</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-sm rounded-full bg-green-500/10 text-green-400 font-medium">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <form method="POST" action="<?= url('/admin/premium') ?>" class="inline" onsubmit="return confirm('Revocar Premium de <?= htmlspecialchars(addslashes($pu['nombre'])) ?>?');">
                                <input type="hidden" name="action" value="revoke">
                                <input type="hidden" name="user_id" value="<?= $pu['idUsuario'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-sm font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">Revocar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Pestaña: Historial de pagos -->

    <!-- Estadísticas de pagos -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
        <div class="bg-green-500/5 border border-green-500/20 rounded-xl p-7">
            <p class="text-4xl font-bold text-white"><?= number_format($paymentStats['total_revenue'] ?? 0, 2) ?>&euro;</p>
            <p class="text-sm text-gray-400">Ingresos totales</p>
        </div>
        <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-7">
            <p class="text-4xl font-bold text-white"><?= $paymentStats['total_payments'] ?? 0 ?></p>
            <p class="text-sm text-gray-400">Total pagos</p>
        </div>
        <div class="bg-purple-500/5 border border-purple-500/20 rounded-xl p-7">
            <p class="text-4xl font-bold text-white"><?= $paymentStats['payments_this_month'] ?? 0 ?></p>
            <p class="text-sm text-gray-400">Pagos este mes</p>
        </div>
        <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-7">
            <p class="text-4xl font-bold text-white"><?= number_format($paymentStats['avg_payment_amount'] ?? 0, 2) ?>&euro;</p>
            <p class="text-sm text-gray-400">Importe medio</p>
        </div>
    </div>

    <!-- Header pagos -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= $totalPayments ?> pagos</p>
    </div>

    <!-- Filtros de pagos -->
    <form method="GET" action="<?= url('/admin/premium') ?>" class="flex flex-wrap items-center gap-4 mb-6">
        <input type="hidden" name="tab" value="pagos">
        <input type="text" name="psearch" value="<?= htmlspecialchars($paymentFilters['search'] ?? '') ?>" placeholder="Buscar usuario..."
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-60">
        <input type="date" name="pdate_from" value="<?= htmlspecialchars($paymentFilters['date_from'] ?? '') ?>"
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
        <input type="date" name="pdate_to" value="<?= htmlspecialchars($paymentFilters['date_to'] ?? '') ?>"
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
        <select name="porigen" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
            <option value="">Todos los origenes</option>
            <option value="stripe" <?= ($paymentFilters['origen'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
            <option value="admin" <?= ($paymentFilters['origen'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (manual)</option>
        </select>
        <button type="submit" class="px-5 py-2.5 text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
        <?php if (!empty($paymentFilters['search']) || !empty($paymentFilters['date_from']) || !empty($paymentFilters['date_to']) || !empty($paymentFilters['origen'])): ?>
        <a href="<?= url('/admin/premium') ?>?tab=pagos" class="text-sm text-gray-400 hover:text-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla de pagos -->
    <?php if (empty($payments)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
            </div>
            <p class="text-gray-400 font-medium">No hay pagos registrados</p>
            <p class="text-sm text-gray-500">Los pagos apareceran aqui cuando se realicen</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Usuario</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Importe</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Origen</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($payments as $pay): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                        <td class="px-5 py-4 text-gray-400">#<?= $pay['idPago'] ?? $pay['id'] ?? '-' ?></td>
                        <td class="px-5 py-4">
                            <p class="text-gray-200 font-medium"><?= htmlspecialchars($pay['usuario_nombre'] ?? '-') ?></p>
                            <p class="text-gray-500 text-xs"><?= htmlspecialchars($pay['usuario_correo'] ?? '') ?></p>
                        </td>
                        <td class="px-5 py-4 text-green-400 font-medium"><?= number_format((float)($pay['importe'] ?? 0), 2) ?>&euro;</td>
                        <td class="px-5 py-4">
                            <?php if (($pay['origen'] ?? '') === 'stripe'): ?>
                                <span class="px-2 py-0.5 text-sm rounded-full bg-purple-500/10 text-purple-400">Stripe</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-sm rounded-full bg-blue-500/10 text-blue-400">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-0.5 text-sm rounded-full <?= ($pay['estado'] ?? '') === 'completado' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400' ?>">
                                <?= htmlspecialchars($pay['estado'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 text-sm"><?= isset($pay['creado_en']) ? date('d/m/Y H:i', strtotime($pay['creado_en'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginacion de pagos -->
        <?php if ($totalPaymentPages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php for ($i = 1; $i <= $totalPaymentPages; $i++): ?>
            <a href="<?= url('/admin/premium') ?>?tab=pagos&ppage=<?= $i ?>&psearch=<?= urlencode($paymentFilters['search'] ?? '') ?>&pdate_from=<?= urlencode($paymentFilters['date_from'] ?? '') ?>&pdate_to=<?= urlencode($paymentFilters['date_to'] ?? '') ?>&porigen=<?= urlencode($paymentFilters['origen'] ?? '') ?>"
               class="px-4 py-2 text-sm rounded-lg transition <?= $i === $paymentPage ? 'bg-primary text-gray-900 font-bold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php endif; ?>

</div>
</main>
<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
