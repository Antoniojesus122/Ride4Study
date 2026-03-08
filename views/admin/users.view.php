<?php
    require_once __DIR__ . '/layout/header.view.php';
    require_once __DIR__ . '/layout/sidebar.view.php';
?>

<main class="flex-1 p-8">
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Verificaciones de estudiantes</h1>
            <p class="text-gray-400 mt-1 text-sm">Revisa y gestiona las solicitudes de verificación pendientes</p>
        </div>
        <div class="text-sm text-gray-400">
            Admin: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?>
        </div>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <?= $_GET['success'] === 'approved' ? 'Verificación aprobada correctamente.' : 'Verificación rechazada.' ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pendingUsers)): ?>
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-green-400 text-2xl"></i>
            </div>
            <p class="text-gray-400 text-lg font-medium">Todo al día</p>
            <p class="text-gray-500 text-sm mt-1">No hay solicitudes de verificación pendientes</p>
        </div>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($pendingUsers as $u): ?>
                <div class="bg-gray-800 border border-gray-700 rounded-2xl p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-6">

                        <!-- Info del usuario -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-lg">
                                    <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-white"><?= htmlspecialchars($u['nombre']) ?></p>
                                    <p class="text-sm text-gray-400"><?= htmlspecialchars($u['correo']) ?></p>
                                </div>
                                <span class="ml-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            </div>

                            <div class="text-sm text-gray-400 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-gray-500"></i>
                                Solicitud enviada el <?= date('d/m/Y H:i', strtotime($u['creado_en'])) ?>
                            </div>
                        </div>

                        <!-- Documento -->
                        <div class="flex items-center gap-3">
                            <a href="<?= url('/') . 'public/uploads/verification/' . urlencode($u['documento_verificacion']) ?>"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition-colors border border-gray-600">
                                <i class="fas fa-file-alt text-primary"></i>
                                Ver documento
                            </a>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-col gap-3 lg:min-w-[220px]">
                            <!-- Aprobar -->
                            <form method="POST" action="<?= url('/admin/users') ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="user_id" value="<?= (int)$u['idUsuario'] ?>">
                                <button type="submit"
                                        onclick="return confirm('¿Aprobar la verificación de <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-bold transition-colors shadow-lg shadow-green-900/20">
                                    <i class="fas fa-check"></i>
                                    Aprobar verificación
                                </button>
                            </form>

                            <!-- Rechazar con motivo -->
                            <form method="POST" action="<?= url('/admin/users') ?>" class="flex flex-col gap-2">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="user_id" value="<?= (int)$u['idUsuario'] ?>">
                                <input type="text"
                                       name="reason"
                                       placeholder="Motivo del rechazo (opcional)"
                                       class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-xl text-sm text-gray-300 placeholder-gray-500 focus:outline-none focus:border-red-500 transition-colors">
                                <button type="submit"
                                        onclick="return confirm('¿Rechazar la verificación de <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-xl text-sm font-bold transition-colors border border-red-500/30">
                                    <i class="fas fa-times"></i>
                                    Rechazar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="mt-6 text-sm text-gray-500 text-right">
            <?= count($pendingUsers) ?> solicitud<?= count($pendingUsers) !== 1 ? 'es' : '' ?> pendiente<?= count($pendingUsers) !== 1 ? 's' : '' ?>
        </p>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
