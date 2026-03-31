<?php $pageTitle = 'Estudiantes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= count($students) ?> estudiantes registrados</p>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-500 text-sm"></i>
            </div>
            <input type="text" id="search-students" placeholder="Buscar estudiante..."
                class="pl-10 pr-4 py-2.5 bg-gray-800/50 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500/50 w-72">
        </div>
    </div>

    <?php if (empty($students)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-graduate text-2xl text-gray-500"></i>
            </div>
            <p class="text-gray-400 font-medium">No hay estudiantes</p>
            <p class="text-gray-500 text-sm mt-1">Aun no hay usuarios registrados de tu institucion</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estudiante</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Verificado</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Viajes</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Valoracion</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Registro</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition student-row">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                    <?= strtoupper(substr($s['nombre'], 0, 2)) ?>
                                </div>
                                <span class="text-gray-200 font-medium student-name"><?= htmlspecialchars($s['nombre']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-400 student-email"><?= htmlspecialchars($s['correo']) ?></td>
                        <td class="px-5 py-4">
                            <?php if ($s['estado_verificacion'] === 'verificado'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500/10 text-green-400 rounded-full text-xs font-semibold border border-green-500/20">
                                    <i class="fas fa-check-circle text-[10px]"></i> Si
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-500/10 text-gray-400 rounded-full text-xs font-semibold border border-gray-500/20">
                                    <i class="fas fa-clock text-[10px]"></i> No
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-semibold border border-blue-500/20">
                                <i class="fas fa-route text-[10px]"></i> <?= (int)$s['num_viajes'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($s['valoracion_media']): ?>
                                <span class="text-yellow-400 font-semibold text-sm">
                                    <?= number_format((float)$s['valoracion_media'], 1) ?> <i class="fas fa-star text-xs"></i>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-600 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($s['creado_en'])) ?></td>
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
    // Buscador de estudiantes
    document.getElementById('search-students')?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.student-row').forEach(row => {
            const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.student-email')?.textContent.toLowerCase() || '';
            row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
        });
    });
</script>
