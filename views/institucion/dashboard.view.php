<?php $pageTitle = 'Dashboard'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <!-- Widget de mensajes sin leer (si hay) -->
    <?php if (!empty($stats['unreadMessages'])): ?>
    <a href="<?= url('/institution/messages') ?>" class="block mb-6 bg-gradient-to-r from-red-500/10 to-red-500/5 border border-red-500/30 rounded-xl p-5 hover:from-red-500/15 transition group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                <i class="fas fa-envelope text-xl" aria-hidden="true"></i>
            </div>
            <div class="flex-1">
                <p class="text-base font-semibold text-white">
                    Tienes <?= (int)$stats['unreadMessages'] ?> mensaje<?= (int)$stats['unreadMessages'] !== 1 ? 's' : '' ?> sin leer del administrador
                </p>
                <p class="text-sm text-gray-400 mt-0.5">Pulsa para ver los hilos pendientes de respuesta.</p>
            </div>
            <i class="fas fa-arrow-right text-gray-400 group-hover:text-white transition" aria-hidden="true"></i>
        </div>
    </a>
    <?php endif; ?>

    <!-- Tarjetas de estadisticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-users text-blue-400" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['totalStudents'] ?></p>
            <p class="text-sm text-gray-400 mt-1">Estudiantes registrados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-route text-primary" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['totalTrips'] ?></p>
            <p class="text-sm text-gray-400 mt-1">Viajes publicados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-check-double text-green-400" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['completedTrips'] ?></p>
            <p class="text-sm text-gray-400 mt-1">Viajes completados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-star text-yellow-400" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['avgRating'] ?: '-' ?> <span class="text-lg text-gray-500">/ 5</span></p>
            <p class="text-sm text-gray-400 mt-1">Valoración media</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-emerald-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-leaf text-emerald-400" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['co2Saved'] ?> <span class="text-lg text-gray-500">kg</span></p>
            <p class="text-sm text-gray-400 mt-1">CO₂ ahorrado</p>
        </div>
        <!-- Nuevos estudiantes este mes + delta -->
        <?php $delta = (int)($stats['newStudentsDelta'] ?? 0); ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-user-plus text-purple-400" aria-hidden="true"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= (int)$stats['newStudentsMonth'] ?></p>
            <p class="text-sm text-gray-400 mt-1 flex items-center gap-1 flex-wrap">
                <span>Nuevos este mes</span>
                <?php if ($delta > 0): ?>
                    <span class="text-green-400 font-semibold">+<?= $delta ?></span>
                <?php elseif ($delta < 0): ?>
                    <span class="text-red-400 font-semibold"><?= $delta ?></span>
                <?php else: ?>
                    <span class="text-gray-600">=</span>
                <?php endif; ?>
                <span class="text-gray-600">vs mes anterior</span>
            </p>
        </div>
    </div>

    <!-- Graficas y tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <!-- Grafica: Viajes (con filtro de periodo) -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h3 class="text-base font-semibold text-white">Viajes publicados</h3>
                <form method="GET" action="<?= url('/institution/dashboard') ?>" class="flex items-center gap-2" onchange="this.submit()">
                    <?php renderPeriodFilter($_GET); ?>
                </form>
            </div>
            <div class="h-64">
                <canvas id="chartTrips"></canvas>
            </div>
        </div>

        <!-- Rutas mas frecuentes -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-base font-semibold text-white mb-6">Rutas mas frecuentes</h3>
            <?php if (empty($topRoutes)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-map-signs text-3xl text-gray-600 mb-3" aria-hidden="true"></i>
                    <p class="text-gray-500 text-sm">Aun no hay datos de rutas</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topRoutes as $i => $route): ?>
                        <div class="flex items-center gap-4 bg-gray-900/50 p-4 rounded-lg">
                            <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 font-bold text-sm shrink-0">
                                <?= $i + 1 ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 font-medium truncate">
                                    <?= htmlspecialchars($route['origen']) ?> <i class="fas fa-arrow-right text-xs text-gray-500 mx-1" aria-hidden="true"></i> <?= htmlspecialchars($route['destino']) ?>
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-blue-400"><?= $route['total'] ?> viajes</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ultimos estudiantes -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-white">Últimos estudiantes registrados</h3>
            <a href="<?= url('/institution/students') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors whitespace-nowrap shrink-0">
                Ver todos <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            </a>
        </div>
        <?php if (empty($recentStudents)): ?>
            <div class="text-center py-12">
                <i class="fas fa-user-graduate text-3xl text-gray-600 mb-3" aria-hidden="true"></i>
                <p class="text-gray-500 text-sm">Aun no hay estudiantes de tu institucion registrados</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentStudents as $student): ?>
                    <div class="flex items-center gap-3 sm:gap-4 bg-gray-900/50 p-3 sm:p-4 rounded-lg">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                            <?= strtoupper(substr($student['nombre'], 0, 2)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-200 font-medium truncate"><?= htmlspecialchars($student['nombre']) ?></p>
                            <p class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($student['correo']) ?>"><?= htmlspecialchars($student['correo']) ?></p>
                        </div>
                        <span class="text-xs text-gray-500 shrink-0 whitespace-nowrap"><?= date('d/m/Y', strtotime($student['creado_en'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

<script>
    const ctxTrips = document.getElementById('chartTrips').getContext('2d');
    new Chart(ctxTrips, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [{
                data: <?= json_encode($chartData['values']) ?>,
                backgroundColor: 'rgba(96, 165, 250, 0.3)',
                borderColor: 'rgba(96, 165, 250, 1)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#6b7280', stepSize: 1 }, grid: { color: 'rgba(55, 65, 81, 0.3)' } },
                x: { ticks: { color: '#6b7280' }, grid: { display: false } }
            }
        }
    });
</script>
