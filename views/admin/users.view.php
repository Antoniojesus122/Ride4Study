<?php $pageTitle = 'Usuarios'; $tab = $tab ?? ($_GET['tab'] ?? 'todos'); ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Mensajes -->
    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['approved' => 'Verificacion aprobada', 'rejected' => 'Verificacion rechazada', 'role_updated' => 'Rol actualizado', 'banned' => 'Usuario suspendido', 'unbanned' => 'Usuario reactivado', 'deleted' => 'Usuario eliminado correctamente'];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">Error: <?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <!-- Pestañas + boton exportar -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex space-x-1.5 bg-gray-800/50 rounded-lg p-1.5 w-fit">
            <a href="<?= url('/admin/users') ?>?tab=todos" class="px-5 py-2.5 text-base font-medium rounded-md transition <?= $tab === 'todos' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                Todos los usuarios
                <span class="ml-1.5 text-sm px-2 py-0.5 rounded-full bg-gray-600/50"><?= count($allUsers) ?></span>
            </a>
            <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="px-5 py-2.5 text-base font-medium rounded-md transition <?= $tab === 'verificaciones' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                Verificaciones
                <?php if (!empty($pendingUsers)): ?>
                <span class="ml-1.5 text-sm px-2 py-0.5 rounded-full bg-yellow-500/20 text-yellow-400"><?= count($pendingUsers) ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('/admin/users') ?>?tab=baneados" class="px-5 py-2.5 text-base font-medium rounded-md transition <?= $tab === 'baneados' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>">
                Suspendidos
                <?php if (!empty($bannedUsers)): ?>
                <span class="ml-1.5 text-sm px-2 py-0.5 rounded-full bg-red-500/20 text-red-400"><?= count($bannedUsers) ?></span>
                <?php endif; ?>
            </a>
        </div>
        <a href="<?= url('/admin/users') ?>?action=export_csv" class="px-4 py-2.5 text-base font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
            Exportar CSV
        </a>
    </div>

    <!-- Pestaña 1: Todos los usuarios -->
    <?php if ($tab === 'todos'): ?>
    <div id="tab-todos">
        <!-- Filtros -->
        <form method="GET" action="<?= url('/admin/users') ?>" class="flex flex-wrap items-center gap-4 mb-6">
            <input type="hidden" name="tab" value="todos">
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Buscar por nombre o correo..."
                   class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-72">
            <select name="rol" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                <option value="">Todos los roles</option>
                <option value="2" <?= ($_GET['rol'] ?? '') === '2' ? 'selected' : '' ?>>Usuario</option>
                <option value="3" <?= ($_GET['rol'] ?? '') === '3' ? 'selected' : '' ?>>Premium</option>
                <option value="4" <?= ($_GET['rol'] ?? '') === '4' ? 'selected' : '' ?>>Institucion</option>
            </select>
            <select name="verificacion" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                <option value="">Verificacion</option>
                <option value="2" <?= ($_GET['verificacion'] ?? '') === '2' ? 'selected' : '' ?>>Verificado</option>
                <option value="1" <?= ($_GET['verificacion'] ?? '') === '1' ? 'selected' : '' ?>>Pendiente</option>
                <option value="0" <?= ($_GET['verificacion'] ?? '') === '0' ? 'selected' : '' ?>>No verificado</option>
            </select>
            <select name="premium_filter" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                <option value="">Premium</option>
                <option value="1" <?= ($_GET['premium_filter'] ?? '') === '1' ? 'selected' : '' ?>>Si</option>
                <option value="0" <?= ($_GET['premium_filter'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
            </select>
            <button type="submit" class="px-5 py-2.5 text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['rol']) || isset($_GET['verificacion']) && $_GET['verificacion'] !== '' || isset($_GET['premium_filter']) && $_GET['premium_filter'] !== ''): ?>
            <a href="<?= url('/admin/users') ?>?tab=todos" class="text-sm text-gray-400 hover:text-gray-200">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (empty($allUsers)): ?>
            <div class="text-center py-20">
                <div class="w-14 h-14 bg-gray-700/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <p class="text-gray-400 font-medium">Sin resultados</p>
                <p class="text-gray-500 text-sm mt-1">No se encontraron usuarios</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-gray-700">
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Usuario</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Correo</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Rol</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Verificacion</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Premium</th>
                        <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Registro</th>
                        <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                        <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                            <td class="px-5 py-4 text-gray-400">#<?= $u['idUsuario'] ?></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center shrink-0">
                                        <span class="text-sm font-bold text-gray-300"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
                                    </div>
                                    <span class="text-gray-200 font-medium truncate max-w-[180px]"><?= htmlspecialchars($u['nombre']) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-400 text-sm"><?= htmlspecialchars($u['correo']) ?></td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 text-sm rounded-full font-medium
                                    <?php
                                    $roleName = $u['nombreRol'] ?? 'Usuario';
                                    if ($roleName === 'Usuario Premium') echo 'bg-yellow-500/10 text-yellow-400';
                                    elseif ($roleName === 'Institucion' || $roleName === 'Institución') echo 'bg-purple-500/10 text-purple-400';
                                    else echo 'bg-gray-700 text-gray-300';
                                    ?>">
                                    <?= htmlspecialchars($roleName) ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <?php if ((int)$u['estado_verificacion'] === 2): ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-green-500/10 text-green-400">Verificado</span>
                                <?php elseif ((int)$u['estado_verificacion'] === 1): ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-yellow-500/10 text-yellow-400">Pendiente</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-gray-700 text-gray-400">No verif.</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <?php if ($u['premium']): ?>
                                    <span class="px-2.5 py-1 text-sm rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Premium</span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openBanModal(<?= $u['idUsuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>')"
                                            class="px-2.5 py-1.5 text-sm font-medium bg-red-500/10 text-red-400 rounded hover:bg-red-500/20 transition" title="Suspender">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                    <form method="POST" action="<?= url('/admin/users') ?>" class="inline" onsubmit="return confirm('¿Eliminar al usuario <?= htmlspecialchars(addslashes($u['nombre'])) ?>? Esta accion no se puede deshacer.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $u['idUsuario'] ?>">
                                        <button type="submit" class="px-2.5 py-1.5 text-sm font-medium bg-red-500/10 text-red-400 rounded hover:bg-red-500/20 transition" title="Eliminar usuario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Pestaña 2: Verificaciones -->
    <?php if ($tab === 'verificaciones'): ?>
    <div id="tab-verificaciones">
        <?php if (empty($pendingUsers)): ?>
            <div class="text-center py-20">
                <div class="w-14 h-14 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-gray-400 font-medium">Todo al dia</p>
                <p class="text-gray-500 text-sm mt-1">No hay solicitudes de verificacion pendientes</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($pendingUsers as $u): ?>
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                                <span class="text-primary font-bold"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-white truncate"><?= htmlspecialchars($u['nombre']) ?></p>
                                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($u['correo']) ?></p>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 font-medium shrink-0">Pendiente</span>
                        </div>
                        <p class="text-xs text-gray-500 shrink-0"><?= date('d/m/Y H:i', strtotime($u['creado_en'])) ?></p>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="<?= url('/') ?>public/uploads/verification/<?= urlencode($u['documento_verificacion']) ?>"
                               target="_blank"
                               class="px-3 py-1.5 text-xs font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition border border-gray-600">
                                Ver documento
                            </a>
                            <form method="POST" action="<?= url('/admin/users') ?>" class="inline">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="user_id" value="<?= (int)$u['idUsuario'] ?>">
                                <button type="submit" onclick="return confirm('Aprobar verificacion de <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')"
                                        class="px-3 py-1.5 text-xs font-medium bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition border border-green-500/20">
                                    Aprobar
                                </button>
                            </form>
                            <div class="relative">
                                <button onclick="this.nextElementSibling.classList.toggle('hidden')"
                                        class="px-3 py-1.5 text-xs font-medium bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition border border-red-500/20">
                                    Rechazar
                                </button>
                                <div class="hidden absolute right-0 top-full mt-2 w-72 bg-gray-800 border border-gray-700 rounded-xl p-3 shadow-xl z-10">
                                    <form method="POST" action="<?= url('/admin/users') ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['idUsuario'] ?>">
                                        <input type="text" name="reason" placeholder="Motivo del rechazo (opcional)"
                                               class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-300 placeholder-gray-500 focus:outline-none focus:border-red-500 mb-2">
                                        <button type="submit" onclick="return confirm('Rechazar verificacion?')"
                                                class="w-full px-3 py-2 text-xs font-medium bg-red-600 text-white rounded-lg hover:bg-red-500 transition">
                                            Confirmar rechazo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="mt-4 text-xs text-gray-500 text-right">
                <?= count($pendingUsers) ?> solicitud<?= count($pendingUsers) !== 1 ? 'es' : '' ?> pendiente<?= count($pendingUsers) !== 1 ? 's' : '' ?>
            </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Pestaña 3: Usuarios suspendidos -->
    <?php if ($tab === 'baneados'): ?>
    <div id="tab-baneados">
        <?php if (empty($bannedUsers)): ?>
            <div class="text-center py-20">
                <div class="w-14 h-14 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-gray-400 font-medium">Sin suspensiones</p>
                <p class="text-gray-500 text-sm mt-1">No hay usuarios suspendidos actualmente</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($bannedUsers as $u): ?>
                <div class="bg-gray-800/50 border border-red-500/20 rounded-xl p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                                <span class="text-red-400 font-bold"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-white truncate"><?= htmlspecialchars($u['nombre']) ?></p>
                                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($u['correo']) ?></p>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-red-500/10 text-red-400 border border-red-500/20 font-medium shrink-0">Suspendido</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-400"><span class="text-gray-500">Motivo:</span> <?= htmlspecialchars($u['ban_motivo'] ?? '-') ?></p>
                            <?php if ($u['ban_hasta']): ?>
                                <p class="text-xs text-gray-500 mt-0.5">Hasta: <?= date('d/m/Y H:i', strtotime($u['ban_hasta'])) ?></p>
                            <?php else: ?>
                                <p class="text-xs text-red-400/70 mt-0.5">Permanente</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="<?= url('/admin/users') ?>" class="shrink-0">
                            <input type="hidden" name="action" value="unban">
                            <input type="hidden" name="user_id" value="<?= (int)$u['idUsuario'] ?>">
                            <button type="submit" onclick="return confirm('Reactivar cuenta de <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')"
                                    class="px-4 py-2 text-xs font-medium bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition border border-green-500/20">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                Reactivar
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</main>

<!-- Modal para suspender usuario -->
<div id="ban-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4" onclick="if(event.target===this) closeBanModal()">
    <div class="bg-gray-900 rounded-2xl border border-gray-700 shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Suspender usuario</h3>
                    <p class="text-sm text-gray-400" id="ban-username"></p>
                </div>
            </div>
        </div>
        <form method="POST" action="<?= url('/admin/users') ?>">
            <input type="hidden" name="action" value="ban">
            <input type="hidden" name="user_id" id="ban-user-id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Motivo de la suspension *</label>
                    <textarea name="motivo" required rows="2" placeholder="Describe el motivo..."
                              class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Duracion</label>
                    <select name="duracion" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-xl text-sm text-white focus:outline-none focus:border-red-500">
                        <option value="7">7 dias</option>
                        <option value="15">15 dias</option>
                        <option value="30" selected>30 dias</option>
                        <option value="90">90 dias</option>
                        <option value="permanente">Permanente</option>
                    </select>
                </div>
            </div>
            <div class="p-6 bg-gray-800/50 border-t border-gray-700 flex gap-3">
                <button type="button" onclick="closeBanModal()" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition">Suspender</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    // Modal de ban
    function openBanModal(userId, userName) {
        document.getElementById('ban-user-id').value = userId;
        document.getElementById('ban-username').textContent = userName;
        document.getElementById('ban-modal').classList.remove('hidden');
    }
    function closeBanModal() {
        document.getElementById('ban-modal').classList.add('hidden');
    }
</script>
