<!-- Banner de Cookies -->
<div id="cookieBanner" class="fixed bottom-0 left-0 right-0 z-50 transform translate-y-full transition-transform duration-500 ease-out" style="display:none;">
    <div class="bg-gray-900/95 backdrop-blur-xl border-t border-gray-700/50 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="flex items-start gap-3 flex-1">
                    <i class="fas fa-cookie-bite text-yellow-400 text-xl mt-0.5 hidden sm:block"></i>
                    <div>
                        <p class="text-sm text-gray-300 leading-relaxed">
                            <?= t('cookies.banner_text') ?>
                            <a href="<?= url('/cookies') ?>" class="text-primary hover:underline font-medium"><?= t('cookies.banner_link') ?></a>.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button onclick="rejectCookies()" class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium text-gray-400 hover:text-white border border-gray-600 hover:border-gray-500 rounded-lg transition-colors">
                        <?= t('cookies.reject') ?>
                    </button>
                    <button onclick="acceptCookies()" class="flex-1 sm:flex-none px-5 py-2 text-sm font-bold bg-primary text-secondary rounded-lg hover:bg-primary-dark transition-colors">
                        <?= t('cookies.accept') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var consent = localStorage.getItem('cookie_consent');
    if (!consent) {
        var banner = document.getElementById('cookieBanner');
        banner.style.display = 'block';
        setTimeout(function() { banner.classList.remove('translate-y-full'); }, 100);
    }
})();

function acceptCookies() {
    localStorage.setItem('cookie_consent', 'accepted');
    closeCookieBanner();
}

function rejectCookies() {
    localStorage.setItem('cookie_consent', 'rejected');
    closeCookieBanner();
}

function closeCookieBanner() {
    var banner = document.getElementById('cookieBanner');
    banner.classList.add('translate-y-full');
    setTimeout(function() { banner.style.display = 'none'; }, 500);
}
</script>
