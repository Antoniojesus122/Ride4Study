<?php
    require_once __DIR__ . '/layout/header.view.php';
    require_once __DIR__ . '/layout/sidebar.view.php';
?>

<main class="flex-1 p-8">
    <header class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold">Panel de Reportes</h1>
        <div class="text-sm text-gray-400">
            Admin: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?>
        </div>
    </header>

    <!-- Mensajes de éxito o error -->
    <?php if ($successMsg): ?>
        <div class="mb-6 p-4 bg-gray-700/50 border border-gray-600 rounded-lg text-gray-200">
            <?= $successMsg === 'resolved' ? '✓ Reporte marcado como resuelto' : '✓ Reporte eliminado correctamente' ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="mb-6 p-4 bg-gray-700/50 border border-gray-600 rounded-lg text-gray-200">
            ✗ Error: <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Pestañas -->
    <div class="flex space-x-2 mb-6 border-b border-gray-700">
        <button class="tab-button px-4 py-3 font-semibold border-b-2 transition <?= $tab === 'usuario' ? 'border-primary text-primary' : 'border-transparent text-gray-400 hover:text-gray-300' ?>" data-tab="usuario">
            Reportes de Usuarios
        </button>
        <button class="tab-button px-4 py-3 font-semibold border-b-2 transition <?= $tab === 'anuncio' ? 'border-primary text-primary' : 'border-transparent text-gray-400 hover:text-gray-300' ?>" data-tab="anuncio">
            Reportes de Anuncios
        </button>
        <button class="tab-button px-4 py-3 font-semibold border-b-2 transition <?= $tab === 'chat' ? 'border-primary text-primary' : 'border-transparent text-gray-400 hover:text-gray-300' ?>" data-tab="chat">
            Reportes de Chats
        </button>
    </div>

    <!-- Contenido de las pestañas -->
    <div id="tab-content">
        <?php foreach (['usuario' => 'Usuarios', 'anuncio' => 'Anuncios', 'chat' => 'Chats'] as $key => $label): ?>
            <div class="tab-panel <?= $key !== $tab ? 'hidden' : '' ?>" id="tab-<?= $key ?>">
                <?php
                $reportesFiltrados = array_filter($reportes, function($r) use ($key) {
                    return $r['tipo'] === $key;
                });
                ?>
                
                <?php if (empty($reportesFiltrados)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-400 text-lg">No hay reportes de <?= strtolower($label) ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                            <thead class="bg-gray-700 text-left">
                                <tr>
                                    <th class="px-6 py-3 text-gray-300">ID</th>
                                    <th class="px-6 py-3 text-gray-300">Tipo</th>
                                    <th class="px-6 py-3 text-gray-300">Usuario Reportado</th>
                                    <th class="px-6 py-3 text-gray-300">Usuario que Reporta</th>
                                    <th class="px-6 py-3 text-gray-300">Mensaje</th>
                                    <th class="px-6 py-3 text-gray-300">Estado</th>
                                    <th class="px-6 py-3 text-gray-300">Fecha</th>
                                    <th class="px-6 py-3 text-gray-300">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportesFiltrados as $reporte): ?>
                                <tr class="border-b border-gray-700 hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-3 text-sm"><?= $reporte['idReporte'] ?></td>
                                    <td class="px-6 py-3 text-sm capitalize">
                                        <span class="px-2 py-1 bg-gray-700 rounded"><?= htmlspecialchars($reporte['tipo']) ?></span>
                                    </td>
                                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($reporte['reportado_nombre'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($reporte['reporta_nombre'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-3 text-sm max-w-xs truncate" title="<?= htmlspecialchars($reporte['mensaje']) ?>">
                                        <?= htmlspecialchars($reporte['mensaje']) ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm">
                                        <?php if ($reporte['estado'] === 'resuelto'): ?>
                                            <span class="text-green-400 font-semibold">✓ Resuelto</span>
                                        <?php elseif ($reporte['estado'] === 'pendiente'): ?>
                                            <span class="text-yellow-400 font-semibold">⏳ Pendiente</span>
                                        <?php else: ?>
                                            <span class="text-gray-400 font-semibold"><?= htmlspecialchars($reporte['estado']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-400">
                                        <?= isset($reporte['creado_en']) ? date('d/m/Y H:i', strtotime($reporte['creado_en'])) : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm space-x-1">
                                        <?php if ($reporte['estado'] !== 'resuelto'): ?>
                                            <form class="inline" method="post" action="reports.php">
                                                <input type="hidden" name="action" value="resolve">
                                                <input type="hidden" name="idReporte" value="<?= $reporte['idReporte'] ?>">
                                                <button type="submit" class="px-3 py-1 bg-green-600 rounded hover:bg-green-500 text-white transition text-xs font-semibold">
                                                    Resolver
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form class="inline" method="post" action="reports.php" onsubmit="return confirm('¿Eliminar este reporte?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="idReporte" value="<?= $reporte['idReporte'] ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-600 rounded hover:bg-red-500 text-white transition text-xs font-semibold">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    const buttons = document.querySelectorAll('.tab-button');
    const panels = document.querySelectorAll('.tab-panel');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            
            // Ocultar todas las pestañas
            panels.forEach(p => p.classList.add('hidden'));
            
            // Mostrar la pestaña seleccionada
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            // Actualizar estilos de botones
            buttons.forEach(b => {
                if (b === btn) {
                    b.classList.remove('border-transparent', 'text-gray-400');
                    b.classList.add('border-primary', 'text-primary');
                } else {
                    b.classList.remove('border-primary', 'text-primary');
                    b.classList.add('border-transparent', 'text-gray-400');
                }
            });
            
            // Actualizar URL (ruta relativa para entornos con subcarpeta)
            window.history.replaceState({}, '', `reports.php?tab=${tab}`);
        });
    });
</script>
