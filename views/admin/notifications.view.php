<?php $pageTitle = 'Notificaciones Masivas'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    $filtroLabels = [
        'todos'          => 'Todos los usuarios',
        'premium'        => 'Usuarios Premium',
        'verificados'    => 'Usuarios Verificados',
        'no_verificados' => 'Usuarios No Verificados',
    ];
    $iconOptions = [
        'fas fa-bullhorn'            => 'Megafono',
        'fas fa-bell'                => 'Campana',
        'fas fa-info-circle'         => 'Informacion',
        'fas fa-exclamation-triangle' => 'Advertencia',
        'fas fa-gift'                => 'Regalo',
        'fas fa-star'                => 'Estrella',
        'fas fa-tools'               => 'Herramientas',
    ];
?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Mensajes flash -->
    <?php if (!empty($successMsg)): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de envio -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 mb-6">
        <h2 class="text-lg font-semibold text-white mb-6">Enviar nueva notificacion</h2>

        <form id="notifForm" action="<?= url('/admin/notifications/send') ?>" method="POST">
            <!-- Mensaje -->
            <div class="mb-6">
                <label for="mensaje" class="block text-base font-medium text-gray-300 mb-2">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="4" required
                    class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-base text-white placeholder-gray-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                    placeholder="Escribe el mensaje de la notificacion..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Icono -->
                <div>
                    <label for="icono" class="block text-base font-medium text-gray-300 mb-2">Icono</label>
                    <select id="icono" name="icono"
                        class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-base text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <?php foreach ($iconOptions as $iconClass => $iconLabel): ?>
                            <option value="<?= $iconClass ?>"><?= $iconLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-base font-medium text-gray-300 mb-2">URL (opcional)</label>
                    <input type="url" id="url" name="url"
                        class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-base text-white placeholder-gray-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="https://...">
                </div>
            </div>

            <!-- Filtro de destinatarios -->
            <div class="mb-6">
                <label class="block text-base font-medium text-gray-300 mb-3">Destinatarios</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php foreach ($filtroLabels as $filtroKey => $filtroLabel): ?>
                        <label class="flex items-center gap-3 bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 cursor-pointer hover:border-gray-500 transition has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="filtro_tipo" value="<?= $filtroKey ?>"
                                <?= $filtroKey === 'todos' ? 'checked' : '' ?>
                                class="w-4 h-4 text-primary bg-gray-700 border-gray-500 focus:ring-primary focus:ring-offset-0"
                                onchange="updatePreview()">
                            <span class="text-base text-gray-300"><?= $filtroLabel ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Preview y envio -->
            <div class="flex items-center gap-4 pt-2">
                <button type="button" onclick="previewCount()"
                    class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white text-base font-medium rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-eye"></i>
                    Vista previa
                </button>

                <span id="previewResult" class="text-base text-gray-400 hidden">
                    Se enviara a <strong id="previewCount" class="text-primary">0</strong> usuarios
                </span>

                <button type="submit" onclick="return confirmSend()"
                    class="ml-auto px-8 py-3 bg-primary hover:bg-primary-dark text-gray-900 text-base font-semibold rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    Enviar notificacion
                </button>
            </div>
        </form>
    </div>

    <!-- Historial de notificaciones masivas -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Historial de envios</h2>
        </div>

        <?php if (empty($broadcasts)): ?>
            <div class="py-20 flex flex-col items-center justify-center">
                <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-inbox text-xl text-gray-500"></i>
                </div>
                <p class="text-gray-400 font-medium">No se han enviado notificaciones masivas todavia</p>
                <p class="text-gray-500 text-sm mt-1">Las notificaciones enviadas apareceran aqui</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Admin</th>
                            <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Mensaje</th>
                            <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Filtro</th>
                            <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Enviados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($broadcasts as $b): ?>
                            <tr class="border-b border-gray-700/30 hover:bg-gray-800/40 transition">
                                <td class="px-5 py-4 text-base text-gray-300 whitespace-nowrap">
                                    <?= date('d/m/Y H:i', strtotime($b['creado_en'])) ?>
                                </td>
                                <td class="px-5 py-4 text-base text-gray-300">
                                    <?= htmlspecialchars($b['admin_nombre'] ?? 'Desconocido') ?>
                                </td>
                                <td class="px-5 py-4 text-base text-gray-300 max-w-xs">
                                    <div class="flex items-center gap-2">
                                        <i class="<?= htmlspecialchars($b['icono'] ?? 'fas fa-bell') ?> text-gray-500 shrink-0"></i>
                                        <span class="truncate" title="<?= htmlspecialchars($b['mensaje']) ?>">
                                            <?= htmlspecialchars(mb_strimwidth($b['mensaje'], 0, 80, '...')) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-base whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        <?php
                                        echo match($b['filtro_tipo'] ?? 'todos') {
                                            'premium'        => 'bg-yellow-500/10 text-yellow-400',
                                            'verificados'    => 'bg-green-500/10 text-green-400',
                                            'no_verificados' => 'bg-red-500/10 text-red-400',
                                            default          => 'bg-blue-500/10 text-blue-400',
                                        };
                                        ?>">
                                        <?= htmlspecialchars($filtroLabels[$b['filtro_tipo'] ?? 'todos'] ?? $b['filtro_tipo']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-base text-white font-medium text-right">
                                    <?= number_format($b['total_enviados'] ?? 0) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    </div>
</main>
</div>

<script>
    function previewCount() {
        const filtro = document.querySelector('input[name="filtro_tipo"]:checked')?.value || 'todos';
        fetch('<?= url('/admin/notifications/preview') ?>?filtro_tipo=' + filtro)
            .then(r => r.json())
            .then(data => {
                document.getElementById('previewCount').textContent = data.count;
                document.getElementById('previewResult').classList.remove('hidden');
            })
            .catch(() => {
                alert('Error al obtener la vista previa');
            });
    }

    function updatePreview() {
        const result = document.getElementById('previewResult');
        if (!result.classList.contains('hidden')) {
            previewCount();
        }
    }

    function confirmSend() {
        const mensaje = document.getElementById('mensaje').value.trim();
        if (!mensaje) {
            alert('Escribe un mensaje');
            return false;
        }
        const filtro = document.querySelector('input[name="filtro_tipo"]:checked')?.value || 'todos';
        return confirm('¿Estas seguro de enviar esta notificacion a los usuarios con filtro "' + filtro + '"?');
    }
</script>

</body>
</html>
