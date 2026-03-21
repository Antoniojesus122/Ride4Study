<?php $pageTitle = 'Usuarios'; $tab = $tab ?? ($_GET['tab'] ?? 'todos'); ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <!-- Mensajes -->
    <?php if (isset($_GET['success'])): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['approved' => 'Verificacion aprobada', 'rejected' => 'Verificacion rechazada', 'role_updated' => 'Rol actualizado'];
            echo $msgs[$_GET['success']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">Error: <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Pestañas -->
    <div class="flex space-x-1 mb-6 bg-gray-800/50 rounded-lg p-1 w-fit">
        <button class="tab-btn px-4 py-2 text-sm font-medium rounded-md transition <?= $tab === 'todos' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>" data-tab="todos">
            Todos los usuarios
            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-gray-600/50"><?= count($allUsers) ?></span>
        </button>
        <button class="tab-btn px-4 py-2 text-sm font-medium rounded-md transition <?= $tab === 'verificaciones' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200' ?>" data-tab="verificaciones">
            Verificaciones
            <?php if (!empty($pendingUsers)): ?>
            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-yellow-500/20 text-yellow-400"><?= count($pendingUsers) ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Pestaña 1: Todos los usuarios -->
    <div class="tab-panel <?= $tab !== 'todos' ? 'hidden' : '' ?>" id="tab-todos">
        <!-- Filtros -->
        <form method="GET" action="<?= url('/admin/users') ?>" class="flex flex-wrap items-center gap-3 mb-5">
            <input type="hidden" name="tab" value="todos">
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Buscar por nombre o correo..."
                   class="px-3 py-2 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-64">
            <select name="rol" class="px-3 py-2 bg-gray-800/60 border border-gray-700 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
                <option value="">Todos los roles</option>
                <option value="2" <?= ($_GET['rol'] ?? '') === '2' ? 'selected' : '' ?>>Usuario</option>
                <option value="3" <?= ($_GET['rol'] ?? '') === '3' ? 'selected' : '' ?>>Premium</option>
                <option value="4" <?= ($_GET['rol'] ?? '') === '4' ? 'selected' : '' ?>>Institucion</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['rol'])): ?>
            <a href="<?= url('/admin/users') ?>?tab=todos" class="text-xs text-gray-400 hover:text-gray-200">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (empty($allUsers)): ?>
            <div class="text-center py-16 text-gray-500"><p class="text-sm">No se encontraron usuarios</p></div>
        <?php else: ?>
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-gray-700">
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Correo</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Rol</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Verificacion</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Premium</th>
                        <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Registro</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Rol</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                        <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-gray-300"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
                                    </div>
                                    <span class="text-gray-200 font-medium truncate max-w-[150px]"><?= htmlspecialchars($u['nombre']) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs"><?= htmlspecialchars($u['correo']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-[10px] rounded-full font-medium
                                    <?php
                                    $roleName = $u['nombreRol'] ?? 'Usuario';
                                    if ($roleName === 'Usuario Premium') echo 'bg-yellow-500/10 text-yellow-400';
                                    elseif ($roleName === 'Institucion' || $roleName === 'Institución') echo 'bg-purple-500/10 text-purple-400';
                                    else echo 'bg-gray-700 text-gray-300';
                                    ?>">
                                    <?= htmlspecialchars($roleName) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ((int)$u['estado_verificacion'] === 2): ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-500/10 text-green-400">Verificado</span>
                                <?php elseif ((int)$u['estado_verificacion'] === 1): ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-yellow-500/10 text-yellow-400">Pendiente</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-gray-700 text-gray-400">No verif.</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($u['premium']): ?>
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-yellow-500/10 text-yellow-400 font-medium">Premium</span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="<?= url('/admin/users') ?>" class="inline-flex items-center gap-1">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?= $u['idUsuario'] ?>">
                                    <select name="new_role" class="px-2 py-1 bg-gray-900 border border-gray-600 rounded text-[11px] text-gray-300 focus:outline-none focus:border-primary">
                                        <option value="2" <?= (int)($u['idRol'] ?? 2) === 2 ? 'selected' : '' ?>>Usuario</option>
                                        <option value="3" <?= (int)($u['idRol'] ?? 2) === 3 ? 'selected' : '' ?>>Premium</option>
                                        <option value="4" <?= (int)($u['idRol'] ?? 2) === 4 ? 'selected' : '' ?>>Institucion</option>
                                    </select>
                                    <button type="submit" class="px-2 py-1 text-[10px] font-medium bg-primary/10 text-primary rounded hover:bg-primary/20 transition">Cambiar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pestaña 2: Verificaciones -->
    <div class="tab-panel <?= $tab !== 'verificaciones' ? 'hidden' : '' ?>" id="tab-verificaciones">
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
                            <span class="px-2 py-0.5 text-[10px] rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 font-medium shrink-0">Pendiente</span>
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

</div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            tabPanels.forEach(p => p.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            tabBtns.forEach(b => {
                b.classList.toggle('bg-gray-700', b === btn);
                b.classList.toggle('text-white', b === btn);
                b.classList.toggle('text-gray-400', b !== btn);
            });
            window.history.replaceState({}, '', `<?= url('/admin/users') ?>?tab=${tab}`);
        });
    });
</script>
