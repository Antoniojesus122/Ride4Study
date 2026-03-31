<?php $pageTitle = 'Dashboard'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Tarjetas de estadisticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-users text-blue-400"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['totalStudents'] ?></p>
            <p class="text-xs text-gray-500 mt-1">Estudiantes registrados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-route text-primary"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['totalTrips'] ?></p>
            <p class="text-xs text-gray-500 mt-1">Viajes publicados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-check-double text-green-400"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['completedTrips'] ?></p>
            <p class="text-xs text-gray-500 mt-1">Viajes completados</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-star text-yellow-400"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['avgRating'] ?: '-' ?> <span class="text-lg text-gray-500">/ 5</span></p>
            <p class="text-xs text-gray-500 mt-1">Valoracion media</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="w-10 h-10 bg-emerald-500/10 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-leaf text-emerald-400"></i>
            </div>
            <p class="text-3xl font-bold text-white"><?= $stats['co2Saved'] ?> <span class="text-lg text-gray-500">kg</span></p>
            <p class="text-xs text-gray-500 mt-1">CO2 ahorrado</p>
        </div>
    </div>

    <!-- Graficas y tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <!-- Grafica: Viajes por mes -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-white">Viajes por mes</h3>
                <span class="text-xs text-gray-500">Ultimos 6 meses</span>
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
                    <i class="fas fa-map-signs text-3xl text-gray-600 mb-3"></i>
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
                                    <?= htmlspecialchars($route['origen']) ?> <i class="fas fa-arrow-right text-xs text-gray-500 mx-1"></i> <?= htmlspecialchars($route['destino']) ?>
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
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-white">Ultimos estudiantes registrados</h3>
            <a href="<?= url('/institution/students') ?>" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                Ver todos <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>
        <?php if (empty($recentStudents)): ?>
            <div class="text-center py-12">
                <i class="fas fa-user-graduate text-3xl text-gray-600 mb-3"></i>
                <p class="text-gray-500 text-sm">Aun no hay estudiantes de tu institucion registrados</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentStudents as $student): ?>
                    <div class="flex items-center gap-4 bg-gray-900/50 p-4 rounded-lg">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                            <?= strtoupper(substr($student['nombre'], 0, 2)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-200 font-medium truncate"><?= htmlspecialchars($student['nombre']) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($student['correo']) ?></p>
                        </div>
                        <span class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($student['creado_en'])) ?></span>
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
