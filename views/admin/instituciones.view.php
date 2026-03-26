<?php $pageTitle = 'Instituciones'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-16 flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-8">

    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-5 p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?php
            $msgs = ['created' => 'Institucion creada', 'updated' => 'Institucion actualizada', 'deleted' => 'Institucion eliminada'];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-5 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm"><?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-400"><?= count($instituciones) ?> instituciones</p>
        <button onclick="document.getElementById('create-form').classList.toggle('hidden')" class="px-4 py-2 text-sm font-medium bg-primary text-gray-900 rounded-lg hover:bg-primary-dark transition">+ Nueva</button>
    </div>

    <!-- Formulario para crear institucion -->
    <div id="create-form" class="hidden mb-6 bg-gray-800/50 border border-gray-700 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Nueva institucion</h3>
        <form method="POST" action="<?= url('/admin/instituciones') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="create">
            <input type="text" name="nombre" placeholder="Nombre *" required class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            <input type="email" name="correo" placeholder="Correo *" required class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            <input type="text" name="telefono" placeholder="Telefono" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            <input type="text" name="direccion" placeholder="Direccion" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            <textarea name="descripcion" placeholder="Descripcion" rows="2" class="md:col-span-2 px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary resize-none"></textarea>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-form').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary text-gray-900 rounded-lg hover:bg-primary-dark transition">Crear</button>
            </div>
        </form>
    </div>

    <?php if (empty($instituciones)): ?>
        <div class="text-center py-16 text-gray-500"><p class="text-sm">No hay instituciones registradas</p></div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Correo</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Telefono</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Direccion</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500 font-medium">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($instituciones as $inst): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                        <td class="px-4 py-3 text-gray-200 font-medium"><?= htmlspecialchars($inst['nombre']) ?></td>
                        <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($inst['correo']) ?></td>
                        <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($inst['telefono'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-gray-400 max-w-[150px] truncate"><?= htmlspecialchars($inst['direccion'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-gray-500 text-xs"><?= isset($inst['creado_en']) ? date('d/m/Y', strtotime($inst['creado_en'])) : '-' ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="toggleEdit(<?= $inst['idInstitucion'] ?>)" class="px-2.5 py-1 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-md hover:bg-emerald-500/20 transition border border-emerald-500/20">Editar</button>
                                <form method="POST" action="<?= url('/admin/instituciones') ?>" class="inline" onsubmit="return confirm('Eliminar esta institucion?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="edit-<?= $inst['idInstitucion'] ?>" class="hidden">
                        <td colspan="6" class="px-4 py-3 bg-gray-800/80">
                            <form method="POST" action="<?= url('/admin/instituciones') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                <input type="text" name="nombre" value="<?= htmlspecialchars($inst['nombre']) ?>" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
                                <input type="email" name="correo" value="<?= htmlspecialchars($inst['correo']) ?>" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
                                <input type="text" name="telefono" value="<?= htmlspecialchars($inst['telefono'] ?? '') ?>" placeholder="Telefono" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
                                <input type="text" name="direccion" value="<?= htmlspecialchars($inst['direccion'] ?? '') ?>" placeholder="Direccion" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary">
                                <textarea name="descripcion" rows="1" class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-lg text-sm text-gray-200 focus:outline-none focus:border-primary resize-none"><?= htmlspecialchars($inst['descripcion'] ?? '') ?></textarea>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="px-3 py-2 text-xs font-medium bg-primary text-gray-900 rounded-lg">Guardar</button>
                                    <button type="button" onclick="toggleEdit(<?= $inst['idInstitucion'] ?>)" class="px-3 py-2 text-xs text-gray-400">Cancelar</button>
                                </div>
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
<script>function toggleEdit(id) { document.getElementById('edit-' + id).classList.toggle('hidden'); }</script>
