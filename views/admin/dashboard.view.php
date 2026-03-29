<?php $pageTitle = 'Dashboard'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Tarjetas iniciales con estadisticas -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Usuarios -->
        <a href="<?= url('/admin/users') ?>" class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 hover:border-gray-600 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-emerald-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <span class="text-base text-gray-500 group-hover:text-primary transition"><?= $stats['verified_users'] ?? 0 ?> verificados</span>
            </div>
            <p class="text-4xl font-bold text-white"><?= $stats['users'] ?></p>
            <p class="text-base text-gray-400 mt-1">Usuarios totales</p>
        </a>

        <!-- Anuncios -->
        <a href="<?= url('/admin/ads') ?>" class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 hover:border-gray-600 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 rounded-lg bg-green-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <span class="text-base text-gray-500 group-hover:text-primary transition">Activos</span>
            </div>
            <p class="text-4xl font-bold text-white"><?= $stats['ads'] ?></p>
            <p class="text-base text-gray-400 mt-1">Anuncios publicados</p>
        </a>

        <!-- Reportes -->
        <a href="<?= url('/admin/reports') ?>?tab=usuario" class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 hover:border-gray-600 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                    </svg>
                </div>
                <?php if (($stats['pending_reports'] ?? 0) > 0): ?>
                <span class="text-base bg-red-500/20 text-red-400 px-3 py-1 rounded-full font-medium"><?= $stats['pending_reports'] ?> pendientes</span>
                <?php else: ?>
                <span class="text-base text-gray-500">Sin pendientes</span>
                <?php endif; ?>
            </div>
            <p class="text-4xl font-bold text-white"><?= $stats['reports'] ?></p>
            <p class="text-base text-gray-400 mt-1">Reportes totales</p>
        </a>

        <!-- Instituciones -->
        <a href="<?= url('/admin/instituciones') ?>" class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 hover:border-gray-600 transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-purple-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <span class="text-base text-gray-500 group-hover:text-primary transition">Registradas</span>
            </div>
            <p class="text-4xl font-bold text-white"><?= $stats['institutions'] ?></p>
            <p class="text-base text-gray-400 mt-1">Instituciones</p>
        </a>
    </section>

    <!-- Graficas de tendencias -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-lg font-semibold text-white mb-4">Registros por mes</h3>
            <div style="position: relative; height: 280px;">
                <canvas id="chartRegistros"></canvas>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-lg font-semibold text-white mb-4">Anuncios por mes</h3>
            <div style="position: relative; height: 280px;">
                <canvas id="chartAnuncios"></canvas>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7 lg:col-span-2">
            <h3 class="text-lg font-semibold text-white mb-4">Reportes por mes</h3>
            <div style="position: relative; height: 220px;">
                <canvas id="chartReportes"></canvas>
            </div>
        </div>
    </div>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <!-- Verificacion -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <h3 class="text-lg font-semibold text-white mb-6">Estado de Verificacion</h3>
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <span class="text-base text-gray-400">Verificados</span>
                    <span class="text-lg font-semibold text-green-400"><?= $stats['verified_users'] ?? 0 ?></span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= $stats['users'] > 0 ? min(100, round(($stats['verified_users'] / $stats['users']) * 100)) : 0 ?>%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-base text-gray-400">Pendientes</span>
                    <span class="text-lg font-semibold text-yellow-400"><?= $stats['pending_verification'] ?? 0 ?></span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" style="width: <?= $stats['users'] > 0 ? min(100, round(($stats['pending_verification'] / $stats['users']) * 100)) : 0 ?>%"></div>
                </div>
            </div>
            <a href="<?= url('/admin/users') ?>?tab=verificaciones" class="text-primary text-base hover:underline mt-6 inline-block">Gestionar verificaciones &rarr;</a>
        </div>

        <!-- Reportes pendientes -->
        <div class="lg:col-span-2 bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Reportes Pendientes</h3>
                <a href="<?= url('/admin/reports') ?>?tab=usuario" class="text-primary text-base hover:underline">Ver todos &rarr;</a>
            </div>
            <?php if (!empty($pendingReports)): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-5 py-3.5 text-left text-sm text-gray-500 font-semibold uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3.5 text-left text-sm text-gray-500 font-semibold uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3.5 text-left text-sm text-gray-500 font-semibold uppercase tracking-wider">Reportado</th>
                            <th class="px-5 py-3.5 text-left text-sm text-gray-500 font-semibold uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pendingReports, 0, 5) as $report): ?>
                        <tr class="border-b border-gray-700/30 hover:bg-gray-800/50">
                            <td class="px-5 py-4 text-base text-gray-400">#<?= $report['idReporte'] ?></td>
                            <td class="px-5 py-4">
                                <span class="px-3 py-1 text-sm rounded-full font-medium
                                    <?= $report['tipo'] === 'usuario' ? 'bg-emerald-500/10 text-emerald-400' : ($report['tipo'] === 'anuncio' ? 'bg-green-500/10 text-green-400' : 'bg-purple-500/10 text-purple-400') ?>">
                                    <?= htmlspecialchars($report['tipo']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-base text-gray-300"><?= htmlspecialchars($report['reportado_nombre'] ?? 'N/A') ?></td>
                            <td class="px-5 py-4 text-base text-gray-500"><?= isset($report['creado_en']) ? date('d/m/Y', strtotime($report['creado_en'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-10 text-gray-500 text-base">Sin reportes pendientes</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ultimos anuncios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Ultimos Anuncios</h3>
                <a href="<?= url('/admin/ads') ?>" class="text-primary text-base hover:underline">Ver todos &rarr;</a>
            </div>
            <?php if (!empty($recentAds)): ?>
            <div class="space-y-3">
                <?php foreach (array_slice($recentAds, 0, 5) as $ad): ?>
                <div class="flex items-center justify-between bg-gray-800/60 rounded-lg px-5 py-3.5 border border-gray-700/30">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-sm px-2.5 py-1 rounded font-medium shrink-0 <?= $ad['tipo'] === 'ofrezco' ? 'bg-green-500/10 text-green-400' : 'bg-emerald-500/10 text-emerald-400' ?>">
                            <?= $ad['tipo'] === 'ofrezco' ? 'Ofr' : 'Bus' ?>
                        </span>
                        <div class="min-w-0">
                            <p class="text-base text-gray-300 truncate"><?= htmlspecialchars($ad['usuario_nombre'] ?? 'N/A') ?></p>
                            <p class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($ad['fechaPublicacion'])) ?></p>
                        </div>
                    </div>
                    <?php if ($ad['precio']): ?>
                    <span class="text-lg font-semibold text-green-400 shrink-0"><?= $ad['precio'] ?>&euro;</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-10 text-gray-500 text-base">No hay anuncios recientes</div>
            <?php endif; ?>
        </div>

        <!-- Ultimos usuarios -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-7">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Usuarios Recientes</h3>
                <a href="<?= url('/admin/users') ?>" class="text-primary text-base hover:underline">Ver todos &rarr;</a>
            </div>
            <?php if (!empty($recentUsers)): ?>
            <div class="space-y-3">
                <?php foreach ($recentUsers as $user): ?>
                <div class="flex items-center gap-4 bg-gray-800/60 rounded-lg px-5 py-3.5 border border-gray-700/30">
                    <div class="w-11 h-11 rounded-full bg-gray-700 flex items-center justify-center shrink-0">
                        <span class="text-base font-bold text-gray-300"><?= mb_strtoupper(mb_substr($user['nombre'], 0, 1)) ?></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-base text-gray-200 font-medium truncate"><?= htmlspecialchars($user['nombre']) ?></p>
                        <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($user['correo']) ?></p>
                    </div>
                    <span class="text-sm px-2.5 py-1 rounded-full shrink-0
                        <?php if ($user['estado_verificacion'] == 2): ?>bg-green-500/10 text-green-400
                        <?php elseif ($user['estado_verificacion'] == 1): ?>bg-yellow-500/10 text-yellow-400
                        <?php else: ?>bg-gray-700 text-gray-400<?php endif; ?>">
                        <?= $user['estado_verificacion'] == 2 ? 'Verificado' : ($user['estado_verificacion'] == 1 ? 'Pendiente' : 'No verif.') ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-10 text-gray-500 text-base">No hay usuarios registrados</div>
            <?php endif; ?>
        </div>
    </section>

</div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', beginAtZero: true } }
            }
        };

        const regLabels = <?= json_encode(array_column($registrationsByMonth, 'mes')) ?>;
        const regData = <?= json_encode(array_map('intval', array_column($registrationsByMonth, 'total'))) ?>;
        new Chart(document.getElementById('chartRegistros'), {
            type: 'bar',
            data: {
                labels: regLabels,
                datasets: [{
                    data: regData,
                    backgroundColor: 'rgba(99, 102, 241, 0.5)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: chartDefaults
        });

        const rideLabels = <?= json_encode(array_column($ridesByMonth, 'mes')) ?>;
        const rideData = <?= json_encode(array_map('intval', array_column($ridesByMonth, 'total'))) ?>;
        new Chart(document.getElementById('chartAnuncios'), {
            type: 'line',
            data: {
                labels: rideLabels,
                datasets: [{
                    data: rideData,
                    borderColor: 'rgb(52, 211, 153)',
                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(52, 211, 153)'
                }]
            },
            options: chartDefaults
        });

        const repLabels = <?= json_encode(array_column($reportsByMonth, 'mes')) ?>;
        const repData = <?= json_encode(array_map('intval', array_column($reportsByMonth, 'total'))) ?>;
        new Chart(document.getElementById('chartReportes'), {
            type: 'bar',
            data: {
                labels: repLabels,
                datasets: [{
                    data: repData,
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: chartDefaults
        });
    });
</script>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
