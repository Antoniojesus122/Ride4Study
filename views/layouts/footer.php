<!-- Footer -->
    <footer class="relative bg-gray-900 border-t border-gray-800 mt-auto" role="contentinfo">
        <div class="mx-auto max-w-7xl px-6 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 items-start">
                <!-- Logo y descripcion -->
                <div>
                    <div class="flex items-center gap-3">
                        <img src="public/img/logo.png" alt="" aria-hidden="true" class="w-8 h-8 object-contain">
                        <span class="font-semibold text-white text-lg tracking-tight">
                            Ride4Study
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-gray-400 max-w-xs leading-relaxed">
                        <?= t('footer.description') ?>
                    </p>
                </div>

                <!-- Links a paginas legales -->
                <nav class="flex flex-col gap-3 text-sm" aria-labelledby="footer-legal">
                    <h4 id="footer-legal" class="text-white font-medium mb-1"><?= t('footer.legal') ?></h4>
                    <a href="<?= url('/privacy') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200"><?= t('footer.privacy') ?></a>
                    <a href="<?= url('/terms') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200"><?= t('footer.terms') ?></a>
                    <a href="<?= url('/cookies') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200"><?= t('footer.cookies') ?></a>
                    <a href="<?= url('/support') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200"><?= t('footer.support') ?></a>
                </nav>

                <!-- Info de la web -->
                <div class="flex flex-col gap-3 text-sm">
                    <h4 class="text-white font-medium mb-1"><?= t('footer.platform') ?></h4>
                    <span class="text-gray-400"><?= t('footer.for_students') ?></span>
                    <span class="text-gray-400"><?= t('footer.spain') ?></span>
                    <span class="text-gray-400"><?= t('footer.version') ?></span>
                </div>

                <!-- Pagos seguros -->
                <div class="flex flex-col gap-3 text-sm">
                    <h4 class="text-white font-medium mb-1"><?= t('footer.payments') ?></h4>
                    <p class="text-gray-400 leading-relaxed"><?= t('footer.payments_desc') ?></p>
                    <div class="flex items-center gap-3 mt-1" role="list" aria-label="<?= t('a11y.payment_methods') ?? 'Metodos de pago aceptados' ?>">
                        <img src="<?= url('/public/img/stripe-logo.svg') ?>" alt="Stripe" class="h-7 rounded" role="listitem">
                        <img src="<?= url('/public/img/visa.svg') ?>" alt="Visa" class="h-7 rounded" role="listitem">
                        <img src="<?= url('/public/img/mastercard.svg') ?>" alt="Mastercard" class="h-7 rounded" role="listitem">
                    </div>
                </div>
            </div>

            <!-- Separador inferior con copyright -->
            <div class="mt-12 border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-500">
                    &copy; <span id="year"></span> Ride4Study. <?= t('footer.rights') ?>
                </p>
                <p class="text-xs text-gray-500">
                    <?= t('footer.made_by') ?> <a href="https://www.linkedin.com/in/antonio-jes%C3%BAs" class="text-primary hover:underline" rel="noopener" target="_blank" aria-label="<?= t('a11y.author_linkedin') ?? 'Perfil de LinkedIn del autor (abre en nueva ventana)' ?>">Antonio Jes&uacute;s Gonz&aacute;lez Domingo</a>
                </p>
            </div>
        </div>
    </footer>

    <?php require_once __DIR__ . '/cookie-banner.php'; ?>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
