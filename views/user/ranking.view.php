<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Encabezado -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500/10 text-green-400 text-sm font-medium rounded-full border border-green-500/20 mb-4">
            <i class="fas fa-leaf"></i> <?= t('co2.ranking_title') ?>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3"><?= t('co2.ranking_title') ?></h1>
        <p class="text-gray-400 text-lg max-w-xl mx-auto"><?= t('co2.ranking_subtitle') ?></p>
    </div>

    <!-- Stats globales -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-surface rounded-2xl border border-green-500/20 p-6 text-center">
            <i class="fas fa-globe-americas text-green-400 text-2xl mb-2"></i>
            <p class="text-2xl font-bold text-green-400"><?= number_format($totalCO2, 1) ?> kg</p>
            <p class="text-xs text-gray-400 mt-1"><?= t('co2.total_saved') ?></p>
        </div>
        <div class="bg-surface rounded-2xl border border-green-500/20 p-6 text-center">
            <i class="fas fa-tree text-green-400 text-2xl mb-2"></i>
            <p class="text-2xl font-bold text-green-400"><?= number_format($totalCO2 / 21, 0) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= t('co2.equivalent') ?> <?= t('co2.trees') ?></p>
        </div>
        <div class="bg-surface rounded-2xl border border-primary/20 p-6 text-center">
            <i class="fas fa-user text-primary text-2xl mb-2"></i>
            <p class="text-2xl font-bold text-primary"><?= number_format($userCO2, 1) ?> kg</p>
            <p class="text-xs text-gray-400 mt-1"><?= t('co2.your_position') ?>: <?= $userPosition > 0 ? '#' . $userPosition : '-' ?></p>
        </div>
    </div>

    <!-- Ranking -->
    <?php if (empty($ranking)): ?>
        <div class="text-center py-16 text-gray-500">
            <i class="fas fa-leaf text-4xl mb-3 opacity-50"></i>
            <p class="text-sm"><?= t('co2.no_data') ?></p>
        </div>
    <?php else: ?>
        <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden">
            <?php foreach ($ranking as $i => $user):
                $position = $i + 1;
                $isCurrentUser = ((int)$user['idUsuario'] === (int)$_SESSION['user_id']);
                $medalColors = [1 => 'text-yellow-400', 2 => 'text-gray-300', 3 => 'text-amber-600'];
            ?>
            <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-700/50 <?= $isCurrentUser ? 'bg-primary/5 border-l-4 border-l-primary' : '' ?> <?= $position <= 3 ? 'bg-gray-800/30' : '' ?>">
                <!-- Posición -->
                <div class="w-10 text-center shrink-0">
                    <?php if ($position <= 3): ?>
                        <i class="fas fa-trophy text-xl <?= $medalColors[$position] ?>"></i>
                    <?php else: ?>
                        <span class="text-lg font-bold text-gray-500"><?= $position ?></span>
                    <?php endif; ?>
                </div>

                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center text-sm font-bold text-white overflow-hidden shrink-0">
                    <?php if (!empty($user['foto_perfil']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $user['foto_perfil'])): ?>
                        <img src="public/uploads/profiles/<?= htmlspecialchars($user['foto_perfil']) ?>" alt="avatar" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= strtoupper(substr($user['nombre'], 0, 2)) ?>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate <?= $isCurrentUser ? 'text-primary' : '' ?>">
                        <?= htmlspecialchars($user['nombre']) ?>
                        <?php if ($isCurrentUser): ?>
                            <span class="text-xs text-primary ml-1">(<?= t('co2.you') ?>)</span>
                        <?php endif; ?>
                        <?php if ((int)($user['estado_verificacion'] ?? 0) === 2): ?>
                            <i class="fas fa-check-circle text-green-400 text-xs ml-1"></i>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- CO2 -->
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-green-400"><?= number_format((float)$user['co2_ahorrado'], 1) ?> kg</p>
                    <p class="text-[10px] text-gray-500">CO2</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
