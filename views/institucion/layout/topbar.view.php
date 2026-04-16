<header class="bg-gray-900 border-b border-gray-800 px-8 h-[72px] flex items-center justify-between shrink-0">
    <div>
        <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($_SESSION['institution_name'] ?? '') ?></p>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-3 bg-gray-800/50 px-4 py-2 rounded-xl border border-gray-700/50">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i class="fas fa-university text-blue-400 text-sm" aria-hidden="true"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-200"><?= htmlspecialchars($_SESSION['institution_name'] ?? '') ?></p>
                <p class="text-[11px] text-gray-500"><?= htmlspecialchars($_SESSION['institution_email'] ?? '') ?></p>
            </div>
        </div>
    </div>
</header>
