<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if ($isPremium): ?>

    <?php if (isset($_GET['activated'])): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium"><?= t('premium.success') ?></span>
        </div>
    <?php endif; ?>

    <!-- Estado: ya es Premium -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500/20 text-yellow-400 text-sm font-bold rounded-full border border-yellow-500/30 mb-4">
            <i class="fas fa-crown"></i> <?= t('premium.active_title') ?>
        </div>
        <h1 class="text-4xl font-bold text-white mb-3"><?= t('premium.already_premium') ?></h1>
        <p class="text-gray-400 text-lg"><?= t('premium.active_desc') ?><?= $premiumHasta ? ' hasta el <strong class="text-white">' . date('d/m/Y', strtotime($premiumHasta)) . '</strong>' : '' ?>.</p>
    </div>

    <div class="bg-gradient-to-br from-yellow-500/10 to-amber-500/5 border border-yellow-500/20 rounded-2xl p-8 mb-8 text-center">
        <i class="fas fa-crown text-yellow-400 text-5xl mb-4"></i>
        <h2 class="text-2xl font-bold text-white mb-4"><?= t('premium.perks_title') ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-left">
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-infinity text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white"><?= t('premium.perk_unlimited') ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><?= t('premium.perk_unlimited_desc') ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-star text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white"><?= t('premium.perk_featured') ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><?= t('premium.perk_featured_desc') ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-crown text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white"><?= t('premium.perk_badge') ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><?= t('premium.perk_badge_desc') ?></p>
                </div>
            </div>
        </div>
        <a href="<?= url('/my-rides') ?>" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all">
            <i class="fas fa-star"></i> <?= t('premium.go_rides') ?>
        </a>
    </div>

    <?php else: ?>
    <!-- Estado: plan gratuito -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary text-sm font-medium rounded-full border border-primary/20 mb-4">
            <i class="fas fa-star"></i> <?= t('premium.improve_title') ?>
        </div>
        <h1 class="text-4xl font-bold text-white mb-3"><?= t('premium.title') ?></h1>
        <p class="text-gray-400 text-lg max-w-xl mx-auto"><?= t('premium.subtitle') ?></p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium"><?= t('premium.success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cancelled'])): ?>
        <div class="mb-6 bg-yellow-500/10 border border-yellow-500/50 text-yellow-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-info-circle text-xl"></i>
            <span class="font-medium"><?= t('premium.cancelled') ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-medium"><?= t('premium.error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Comparativa de planes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        <!-- Plan Gratuito -->
        <div class="bg-surface rounded-2xl border border-gray-700 p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white mb-1"><?= t('premium.free_plan') ?></h2>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-bold text-white">0€</span>
                    <span class="text-gray-400 text-sm mb-1"><?= t('premium.per_month') ?></span>
                </div>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    <?= t('premium.free_4_rides') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    <?= t('premium.free_chat') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    <?= t('premium.free_ratings') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    <?= t('premium.free_no_unlimited') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    <?= t('premium.free_no_featured') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    <?= t('premium.free_no_badge') ?>
                </li>
            </ul>
            <div class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-gray-500 rounded-xl text-sm font-medium text-center">
                <?= t('premium.current_plan') ?>
            </div>
        </div>

        <!-- Plan Premium -->
        <div class="bg-gradient-to-br from-yellow-500/10 to-amber-500/5 rounded-2xl border border-yellow-500/30 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
            <div class="absolute top-4 right-4">
                <span class="px-2.5 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-bold rounded-full border border-yellow-500/30">
                    <i class="fas fa-crown mr-1"></i><?= t('premium.recommended') ?>
                </span>
            </div>
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white mb-1"><?= t('premium.plan_name') ?></h2>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-bold text-yellow-400"><?= t('premium.price') ?></span>
                    <span class="text-gray-400 text-sm mb-1"><?= t('premium.per_30days') ?></span>
                </div>
                <p class="text-xs text-gray-400 mt-1"><?= t('premium.no_auto_renew') ?></p>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-check text-yellow-400 w-4 text-center"></i>
                    <?= t('premium.includes_free') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-infinity text-yellow-400 w-4 text-center"></i>
                    <strong><?= t('premium.unlimited_rides') ?></strong>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-star text-yellow-400 w-4 text-center"></i>
                    <strong><?= t('premium.one_featured') ?></strong> <?= t('premium.one_featured_desc') ?>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-crown text-yellow-400 w-4 text-center"></i>
                    <?= t('premium.badge_visible') ?>
                </li>
            </ul>
            <form action="<?= url('/premium') ?>?action=checkout" method="POST">
                <button type="submit" class="block w-full px-4 py-3 bg-yellow-500 hover:bg-yellow-400 text-gray-900 rounded-xl text-sm font-bold text-center transition-all shadow-lg shadow-yellow-500/20 hover:shadow-yellow-500/40 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fas fa-crown mr-2"></i><?= t('premium.buy_now') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-surface rounded-2xl border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <i class="fas fa-question-circle text-primary"></i>
            <?= t('premium.faq_title') ?>
        </h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm font-semibold text-white mb-1"><?= t('premium.faq1_q') ?></p>
                <p class="text-sm text-gray-400"><?= t('premium.faq1_a') ?></p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1"><?= t('premium.faq2_q') ?></p>
                <p class="text-sm text-gray-400"><?= t('premium.faq2_a') ?></p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1"><?= t('premium.faq3_q') ?></p>
                <p class="text-sm text-gray-400"><?= t('premium.faq3_a') ?></p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1"><?= t('premium.faq4_q') ?></p>
                <p class="text-sm text-gray-400"><?= t('premium.faq4_a') ?></p>
            </div>
        </div>
    </div>

    <?php endif; ?>

</div>
