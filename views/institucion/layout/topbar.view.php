<header class="bg-gray-900 border-b border-gray-800 px-4 sm:px-6 lg:px-8 h-[72px] flex items-center justify-between shrink-0 gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" onclick="toggleInstSidebar()" class="md:hidden p-2 -ml-2 text-gray-300 hover:text-white rounded-lg hover:bg-gray-800 transition-colors shrink-0" aria-label="Abrir menú">
            <i class="fas fa-bars text-lg" aria-hidden="true"></i>
        </button>
        <div class="min-w-0">
            <h1 class="text-lg sm:text-xl font-bold text-white truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <p class="text-xs text-gray-500 mt-0.5 truncate"><?= htmlspecialchars($_SESSION['institution_name'] ?? '') ?></p>
        </div>
    </div>
    <div class="flex items-center gap-4 shrink-0">
        <div class="hidden sm:flex items-center gap-3 bg-gray-800/50 px-4 py-2 rounded-xl border border-gray-700/50">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <i class="fas fa-university text-blue-400 text-sm" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-200 truncate max-w-[180px]"><?= htmlspecialchars($_SESSION['institution_name'] ?? '') ?></p>
                <p class="text-[11px] text-gray-500 truncate max-w-[180px]"><?= htmlspecialchars($_SESSION['institution_email'] ?? '') ?></p>
            </div>
        </div>
        <!-- Versión móvil compacta: solo avatar -->
        <div class="sm:hidden w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
            <i class="fas fa-university text-blue-400" aria-hidden="true"></i>
        </div>
    </div>
</header>
<script>
    window.toggleInstSidebar = function(force) {
        const sb = document.getElementById('inst-sidebar');
        const bd = document.getElementById('inst-backdrop');
        if (!sb) return;
        const willOpen = typeof force === 'boolean' ? force : !sb.classList.contains('is-open');
        sb.classList.toggle('is-open', willOpen);
        if (bd) bd.classList.toggle('is-open', willOpen);
        document.body.style.overflow = willOpen ? 'hidden' : '';
    };
    document.addEventListener('click', function(e) {
        if (window.innerWidth >= 768) return;
        const sb = document.getElementById('inst-sidebar');
        if (sb && sb.classList.contains('is-open') && e.target.closest('#inst-sidebar a')) {
            setTimeout(() => toggleInstSidebar(false), 50);
        }
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            document.getElementById('inst-sidebar')?.classList.remove('is-open');
            document.getElementById('inst-backdrop')?.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });
</script>
