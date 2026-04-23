<?php $pageTitle = 'Estudiantes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <p class="text-sm sm:text-base text-gray-400"><?= count($students) ?> estudiantes <?= !empty($_GET) ? 'filtrados' : 'registrados' ?></p>
        <a href="<?= url('/institution/students/export') ?><?= !empty($_GET) ? '?' . http_build_query(array_filter($_GET)) : '' ?>"
           class="px-4 py-2.5 text-sm sm:text-base font-medium bg-emerald-500/10 text-emerald-400 rounded-lg hover:bg-emerald-500/20 transition border border-emerald-500/20">
            <i class="fas fa-file-csv mr-1" aria-hidden="true"></i> Exportar CSV
        </a>
    </div>

    <!-- Filtros -->
    <?php
        $stuAdvFilters = [$_GET['verificado'] ?? '', $_GET['anuncios'] ?? '', $_GET['date_from'] ?? '', $_GET['date_to'] ?? '', $_GET['periodo'] ?? ''];
        $stuActiveAdv = count(array_filter($stuAdvFilters, fn($v) => $v !== '' && $v !== null));
    ?>
    <form method="GET" action="<?= url('/institution/students') ?>" class="mb-8">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Buscar por nombre o correo..."
                   class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500 w-full sm:w-64">
            <button type="button" onclick="toggleStuAdvFilters()"
                    class="sm:hidden w-full flex items-center justify-between px-4 py-2.5 rounded-lg border border-gray-700 bg-gray-800/60 text-sm font-medium text-gray-300 hover:border-gray-600 transition-all"
                    aria-expanded="<?= $stuActiveAdv > 0 ? 'true' : 'false' ?>" aria-controls="stu-adv-filters">
                <span class="flex items-center gap-2">
                    <i class="fas fa-sliders text-xs text-blue-400" aria-hidden="true"></i>
                    Más filtros
                    <?php if ($stuActiveAdv > 0): ?>
                        <span class="bg-blue-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $stuActiveAdv ?></span>
                    <?php endif; ?>
                </span>
                <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform <?= $stuActiveAdv > 0 ? 'rotate-180' : '' ?>" id="stu-adv-chevron" aria-hidden="true"></i>
            </button>
        </div>

        <div id="stu-adv-filters" class="<?= $stuActiveAdv > 0 ? '' : 'hidden' ?> flex flex-col sm:!flex-row sm:flex-wrap sm:items-center gap-3 mt-4">
            <select name="verificado" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 focus:outline-none focus:border-blue-500 w-full sm:w-auto">
                <option value="">Verificación: todos</option>
                <option value="verificado"    <?= ($_GET['verificado'] ?? '') === 'verificado'    ? 'selected' : '' ?>>Verificados</option>
                <option value="pendiente"     <?= ($_GET['verificado'] ?? '') === 'pendiente'     ? 'selected' : '' ?>>Pendientes</option>
                <option value="no_verificado" <?= ($_GET['verificado'] ?? '') === 'no_verificado' ? 'selected' : '' ?>>No verificados</option>
            </select>
            <select name="anuncios" class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-sm sm:text-base text-gray-200 focus:outline-none focus:border-blue-500 w-full sm:w-auto">
                <option value="">Anuncios: todos</option>
                <option value="con" <?= ($_GET['anuncios'] ?? '') === 'con' ? 'selected' : '' ?>>Con anuncios</option>
                <option value="sin" <?= ($_GET['anuncios'] ?? '') === 'sin' ? 'selected' : '' ?>>Sin anuncios</option>
            </select>
            <?php renderPeriodFilter($_GET); ?>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 text-sm sm:text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
                <?php if (!empty(array_filter($_GET))): ?>
                    <a href="<?= url('/institution/students') ?>" class="text-sm text-gray-400 hover:text-gray-200 whitespace-nowrap">Limpiar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <script>
        function toggleStuAdvFilters() {
            const w = document.getElementById('stu-adv-filters');
            const c = document.getElementById('stu-adv-chevron');
            const b = document.querySelector('[aria-controls="stu-adv-filters"]');
            const open = w.classList.contains('hidden');
            w.classList.toggle('hidden', !open);
            if (c) c.classList.toggle('rotate-180', open);
            if (b) b.setAttribute('aria-expanded', String(open));
        }
    </script>

    <?php if (empty($students)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-graduate text-2xl text-gray-500" aria-hidden="true"></i>
            </div>
            <p class="text-gray-400 font-medium">No hay estudiantes</p>
            <p class="text-gray-500 text-sm mt-1">Prueba a ajustar los filtros</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead><tr class="border-b border-gray-700">
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Estudiante</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Verificado</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Viajes</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Valoración</th>
                    <th class="px-5 py-3.5 text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">Registro</th>
                    <th class="px-5 py-3.5 text-right text-xs text-gray-500 font-semibold uppercase tracking-wider">Detalle</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr class="border-b border-gray-700/30 hover:bg-gray-800/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                    <?= strtoupper(substr($s['nombre'], 0, 2)) ?>
                                </div>
                                <span class="text-gray-200 font-medium"><?= htmlspecialchars($s['nombre']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-400"><?= htmlspecialchars($s['correo']) ?></td>
                        <td class="px-5 py-4">
                            <?php if ($s['estado_verificacion'] === 'verificado'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500/10 text-green-400 rounded-full text-xs font-semibold border border-green-500/20">
                                    <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i> Sí
                                </span>
                            <?php elseif ($s['estado_verificacion'] === 'pendiente'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-500/10 text-yellow-400 rounded-full text-xs font-semibold border border-yellow-500/20">
                                    <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Pendiente
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-500/10 text-gray-400 rounded-full text-xs font-semibold border border-gray-500/20">
                                    <i class="fas fa-minus text-[10px]" aria-hidden="true"></i> No
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-semibold border border-blue-500/20">
                                <i class="fas fa-route text-[10px]" aria-hidden="true"></i> <?= (int)$s['num_viajes'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($s['valoracion_media']): ?>
                                <span class="text-yellow-400 font-semibold text-sm">
                                    <?= number_format((float)$s['valoracion_media'], 1) ?> <i class="fas fa-star text-xs" aria-hidden="true"></i>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-600 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($s['creado_en'])) ?></td>
                        <td class="px-5 py-4 text-right">
                            <button type="button"
                                    onclick='openStudentDetail(<?= json_encode([
                                        "id"       => (int)$s["idUsuario"],
                                        "nombre"   => $s["nombre"],
                                        "correo"   => $s["correo"],
                                        "verif"    => $s["estado_verificacion"] ?? "",
                                        "viajes"   => (int)$s["num_viajes"],
                                        "val"      => $s["valoracion_media"] ? number_format((float)$s["valoracion_media"], 1) : null,
                                        "creado"   => date("d/m/Y", strtotime($s["creado_en"])),
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)'
                                    class="px-2.5 py-1.5 text-xs font-medium bg-blue-500/10 text-blue-400 rounded-md hover:bg-blue-500/20 transition border border-blue-500/20" title="Ver detalle">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php
            $stuPagParams = array_filter([
                'search'     => $_GET['search']     ?? '',
                'verificado' => $_GET['verificado'] ?? '',
                'anuncios'   => $_GET['anuncios']   ?? '',
                'periodo'    => $_GET['periodo']    ?? '',
                'date_from'  => $_GET['date_from']  ?? '',
                'date_to'    => $_GET['date_to']    ?? '',
            ], fn($v) => $v !== '' && $v !== null);
            renderPagination((int)($page ?? 1), (int)($totalPages ?? 1), url('/institution/students'), $stuPagParams);
        ?>
    <?php endif; ?>

    </div>
</main>

<!-- Modal de detalle -->
<div id="studentDetailModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeStudentDetail()"></div>
    <div class="relative w-full max-w-md bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 pt-6 pb-2 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Detalle del estudiante</h3>
            <button type="button" onclick="closeStudentDetail()" class="text-gray-500 hover:text-gray-200">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="px-6 pb-6 pt-2">
            <div class="flex items-center gap-4 mb-5">
                <div id="sd-avatar" class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg"></div>
                <div class="min-w-0">
                    <p id="sd-name" class="text-lg font-semibold text-white truncate"></p>
                    <p id="sd-email" class="text-sm text-gray-400 truncate"></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-900/50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Verificación</p>
                    <p id="sd-verif" class="text-gray-200 font-medium mt-0.5"></p>
                </div>
                <div class="bg-gray-900/50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Anuncios publicados</p>
                    <p id="sd-viajes" class="text-gray-200 font-medium mt-0.5"></p>
                </div>
                <div class="bg-gray-900/50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Valoración media</p>
                    <p id="sd-val" class="text-gray-200 font-medium mt-0.5"></p>
                </div>
                <div class="bg-gray-900/50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500">Registrado el</p>
                    <p id="sd-creado" class="text-gray-200 font-medium mt-0.5"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    function openStudentDetail(data) {
        document.getElementById('sd-name').textContent   = data.nombre;
        document.getElementById('sd-email').textContent  = data.correo;
        document.getElementById('sd-avatar').textContent = (data.nombre || '').substring(0,2).toUpperCase();
        const verifLabels = { verificado: 'Verificado', pendiente: 'Pendiente', no_verificado: 'No verificado' };
        document.getElementById('sd-verif').textContent  = verifLabels[data.verif] || '-';
        document.getElementById('sd-viajes').textContent = data.viajes;
        document.getElementById('sd-val').textContent    = data.val ? (data.val + ' / 5') : 'Sin valoraciones';
        document.getElementById('sd-creado').textContent = data.creado;
        const m = document.getElementById('studentDetailModal');
        m.classList.remove('hidden'); m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeStudentDetail() {
        const m = document.getElementById('studentDetailModal');
        m.classList.add('hidden'); m.classList.remove('flex');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStudentDetail(); });
</script>
