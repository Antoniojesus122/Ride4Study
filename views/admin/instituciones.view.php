<?php $pageTitle = 'Instituciones'; ?>
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
            $msgs = [
                'created' => 'Institucion creada correctamente. Se han enviado las credenciales por email.',
                'updated' => 'Institucion actualizada correctamente.',
                'deleted' => 'Institucion eliminada correctamente.',
                'password_reset' => 'Contraseña regenerada y enviada por email.',
                'activated' => 'Cuenta de institucion activada.',
                'deactivated' => 'Cuenta de institucion desactivada.',
            ];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada correctamente.';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 112 0 1 1 0 01-2 0zm0-8a1 1 0 012 0v4a1 1 0 01-2 0V5z" clip-rule="evenodd" /></svg>
            <?php
            $errMsgs = [
                'campos_obligatorios' => 'Los campos nombre y correo son obligatorios.',
                'correo_duplicado' => 'Ya existe una institucion con ese correo electronico.',
                'error_crear' => 'Error al crear la institucion. Intentalo de nuevo.',
                'id_invalido' => 'ID de institucion no valido.',
                'no_encontrada' => 'Institucion no encontrada.',
            ];
            echo $errMsgs[$flashData['message']] ?? htmlspecialchars($flashData['message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= count($instituciones) ?> instituciones registradas</p>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/instituciones') ?>?action=export_csv"
               class="px-4 py-2.5 text-base font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
                <i class="fas fa-file-csv mr-1" aria-hidden="true"></i> Exportar CSV
            </a>
            <button onclick="document.getElementById('create-form').classList.toggle('hidden')" class="px-5 py-2.5 text-base font-medium bg-primary text-gray-900 rounded-lg hover:bg-primary-dark transition">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Nueva institucion
            </button>
        </div>
    </div>

    <!-- Formulario para crear institucion -->
    <div id="create-form" class="hidden mb-6 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
        <h3 class="text-lg font-semibold text-white mb-1">Nueva institucion</h3>
        <p class="text-sm text-gray-500 mb-5">Se generara una contraseña automaticamente y se enviara por email a la institucion.</p>
        <form method="POST" action="<?= url('/admin/instituciones') ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="create">

            <!-- Nombre con autocompletado -->
            <div class="relative">
                <label class="block text-xs text-gray-500 mb-1">Nombre de la institucion *</label>
                <input type="text" name="nombre" id="admin-inst-nombre" required autocomplete="off" placeholder="Escribe para buscar..."
                    class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary" aria-label="Escribe para buscar...">
                <ul id="admin-inst-autocomplete" class="hidden absolute z-20 w-full bg-gray-800 border border-gray-600 rounded-lg mt-1 shadow-lg max-h-48 overflow-y-auto"></ul>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Correo electronico *</label>
                <input type="email" name="correo" placeholder="correo@institucion.es" required
                    class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Telefono</label>
                <input type="text" name="telefono" placeholder="959 000 000"
                    class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Direccion</label>
                <input type="text" name="direccion" placeholder="Calle, numero, ciudad"
                    class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Descripcion</label>
                <textarea name="descripcion" placeholder="Descripcion breve de la institucion" rows="2"
                    class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary resize-none"></textarea>
            </div>

            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-form').classList.add('hidden')" class="px-4 py-2 text-base text-gray-400 hover:text-gray-200">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 text-base font-medium bg-primary text-gray-900 rounded-lg hover:bg-primary-dark transition">
                    <i class="fas fa-paper-plane mr-1" aria-hidden="true"></i> Crear y enviar credenciales
                </button>
            </div>
        </form>
    </div>

    <?php if (empty($instituciones)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-university text-2xl text-gray-500" aria-hidden="true"></i>
            </div>
            <p class="text-gray-400 font-medium">No hay instituciones</p>
            <p class="text-gray-500 text-sm mt-1">Crea la primera institucion para comenzar</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Institucion</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Correo</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estudiantes</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Ultimo acceso</th>
                    <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Acciones</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($instituciones as $inst): ?>
                    <?php $isActive = (int)($inst['activo'] ?? 1) === 1; ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition <?= !$isActive ? 'opacity-60' : '' ?>">
                        <td class="px-5 py-4 text-gray-400">#<?= $inst['idInstitucion'] ?></td>
                        <td class="px-5 py-4">
                            <div>
                                <p class="text-gray-200 font-medium"><?= htmlspecialchars($inst['nombre']) ?></p>
                                <?php if ($inst['telefono']): ?>
                                    <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone text-[10px] mr-1" aria-hidden="true"></i><?= htmlspecialchars($inst['telefono']) ?></p>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-400"><?= htmlspecialchars($inst['correo']) ?></td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-semibold border border-blue-500/20">
                                <i class="fas fa-users text-[10px]" aria-hidden="true"></i> <?= $inst['num_estudiantes'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($isActive): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500/10 text-green-400 rounded-full text-xs font-semibold border border-green-500/20">
                                    <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Activa
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-500/10 text-red-400 rounded-full text-xs font-semibold border border-red-500/20">
                                    <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i> Inactiva
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-sm">
                            <?= !empty($inst['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($inst['ultimo_acceso'])) : '<span class="text-gray-600 italic">Nunca</span>' ?>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="toggleEdit(<?= $inst['idInstitucion'] ?>)" class="px-2.5 py-1.5 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-md hover:bg-emerald-500/20 transition border border-emerald-500/20" title="Editar">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>

                                <!-- Regenerar contraseña -->
                                <form method="POST" action="<?= url('/admin/instituciones') ?>" class="inline" onsubmit="return confirm('¿Regenerar contraseña para <?= htmlspecialchars($inst['nombre']) ?>?\n\nSe enviara la nueva contraseña por email.');">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium bg-yellow-500/10 text-yellow-400 rounded-md hover:bg-yellow-500/20 transition border border-yellow-500/20" title="Regenerar contraseña" aria-label="Regenerar contraseña">
                                        <i class="fas fa-key" aria-hidden="true"></i>
                                    </button>
                                </form>

                                <!-- Activar/Desactivar -->
                                <form method="POST" action="<?= url('/admin/instituciones') ?>" class="inline" onsubmit="return confirm('<?= $isActive ? '¿Desactivar' : '¿Activar' ?> la cuenta de <?= htmlspecialchars($inst['nombre']) ?>?');">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium <?= $isActive ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' : 'bg-green-500/10 text-green-400 border-green-500/20' ?> rounded-md hover:opacity-80 transition border" title="<?= $isActive ? 'Desactivar' : 'Activar' ?>">
                                        <i class="fas <?= $isActive ? 'fa-ban' : 'fa-check' ?>" aria-hidden="true"></i>
                                    </button>
                                </form>

                                <!-- Eliminar -->
                                <form method="POST" action="<?= url('/admin/instituciones') ?>" class="inline" onsubmit="return confirm('¿Eliminar la institucion <?= htmlspecialchars($inst['nombre']) ?>?\n\nEsta accion no se puede deshacer.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20" title="Eliminar" aria-label="Eliminar">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <!-- Fila de edicion -->
                    <tr id="edit-<?= $inst['idInstitucion'] ?>" class="hidden">
                        <td colspan="7" class="px-5 py-4 bg-gray-800/80">
                            <form method="POST" action="<?= url('/admin/instituciones') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $inst['idInstitucion'] ?>">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nombre</label>
                                    <input type="text" name="nombre" value="<?= htmlspecialchars($inst['nombre']) ?>" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Correo</label>
                                    <input type="email" name="correo" value="<?= htmlspecialchars($inst['correo']) ?>" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Telefono</label>
                                    <input type="text" name="telefono" value="<?= htmlspecialchars($inst['telefono'] ?? '') ?>" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Direccion</label>
                                    <input type="text" name="direccion" value="<?= htmlspecialchars($inst['direccion'] ?? '') ?>" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Descripcion</label>
                                    <textarea name="descripcion" rows="1" class="w-full px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 focus:outline-none focus:border-primary resize-none"><?= htmlspecialchars($inst['descripcion'] ?? '') ?></textarea>
                                </div>
                                <div class="flex items-end gap-2">
                                    <button type="submit" class="px-4 py-2.5 text-sm font-medium bg-primary text-gray-900 rounded-lg">Guardar</button>
                                    <button type="button" onclick="toggleEdit(<?= $inst['idInstitucion'] ?>)" class="px-4 py-2.5 text-sm text-gray-400">Cancelar</button>
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

<script>
    function toggleEdit(id) {
        document.getElementById('edit-' + id).classList.toggle('hidden');
    }

    // Autocompletado para el nombre de la institucion
    const adminInstInput = document.getElementById('admin-inst-nombre');
    const adminInstList = document.getElementById('admin-inst-autocomplete');
    let adminDebounce;

    if (adminInstInput) {
        adminInstInput.addEventListener('input', function() {
            clearTimeout(adminDebounce);
            const query = this.value.trim();

            if (query.length < 3) {
                adminInstList.classList.add('hidden');
                adminInstList.innerHTML = '';
                return;
            }

            adminDebounce = setTimeout(() => {
                fetch('<?= url('/api/instituciones-search') ?>?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        adminInstList.innerHTML = '';
                        if (data.length === 0) {
                            adminInstList.classList.add('hidden');
                            return;
                        }
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.nombre;
                            li.className = 'px-4 py-2.5 text-sm text-gray-300 cursor-pointer border-b border-gray-700/50 last:border-0 hover:bg-primary/10 transition-colors';
                            li.addEventListener('click', () => {
                                adminInstInput.value = item.nombre;
                                adminInstList.classList.add('hidden');
                            });
                            adminInstList.appendChild(li);
                        });
                        adminInstList.classList.remove('hidden');
                    })
                    .catch(() => adminInstList.classList.add('hidden'));
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!adminInstInput.contains(e.target) && !adminInstList.contains(e.target)) {
                adminInstList.classList.add('hidden');
            }
        });
    }
</script>
