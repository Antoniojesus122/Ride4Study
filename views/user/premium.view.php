<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if ($isPremium): ?>
    <!-- Estado: ya es Premium -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500/20 text-yellow-400 text-sm font-bold rounded-full border border-yellow-500/30 mb-4">
            <i class="fas fa-crown"></i> Suscripción activa
        </div>
        <h1 class="text-4xl font-bold text-white mb-3">¡Ya eres Premium!</h1>
        <p class="text-gray-400 text-lg">Tu suscripción está activa<?= $premiumHasta ? ' hasta el <strong class="text-white">' . date('d/m/Y', strtotime($premiumHasta)) . '</strong>' : '' ?>.</p>
    </div>

    <div class="bg-gradient-to-br from-yellow-500/10 to-amber-500/5 border border-yellow-500/20 rounded-2xl p-8 mb-8 text-center">
        <i class="fas fa-crown text-yellow-400 text-5xl mb-4"></i>
        <h2 class="text-2xl font-bold text-white mb-4">Ventajas que disfrutas</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-left">
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-infinity text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white">Anuncios ilimitados</p>
                    <p class="text-xs text-gray-400 mt-0.5">Sin límite de viajes activos</p>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-star text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white">Anuncio destacado</p>
                    <p class="text-xs text-gray-400 mt-0.5">Aparece primero en los resultados</p>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                <i class="fas fa-crown text-yellow-400 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-white">Insignia Premium</p>
                    <p class="text-xs text-gray-400 mt-0.5">Visible en tu perfil público</p>
                </div>
            </div>
        </div>
        <a href="<?= url('/my-rides') ?>" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all">
            <i class="fas fa-star"></i> Ir a Mis Viajes
        </a>
    </div>

    <?php else: ?>
    <!-- Estado: plan gratuito -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary text-sm font-medium rounded-full border border-primary/20 mb-4">
            <i class="fas fa-star"></i> Mejora tu experiencia
        </div>
        <h1 class="text-4xl font-bold text-white mb-3">Ride4Study Premium</h1>
        <p class="text-gray-400 text-lg max-w-xl mx-auto">Publica más viajes, destaca tu anuncio y consigue más visibilidad en la plataforma.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">¡Pago completado! Tu cuenta Premium está activa. Puede tardar unos segundos en actualizarse.</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cancelled'])): ?>
        <div class="mb-6 bg-yellow-500/10 border border-yellow-500/50 text-yellow-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-info-circle text-xl"></i>
            <span class="font-medium">Has cancelado el proceso de pago. Puedes intentarlo de nuevo cuando quieras.</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-medium">Ha ocurrido un error al procesar el pago. Por favor, inténtalo de nuevo.</span>
        </div>
    <?php endif; ?>

    <!-- Comparativa de planes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        <!-- Plan Gratuito -->
        <div class="bg-surface rounded-2xl border border-gray-700 p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white mb-1">Plan Gratuito</h2>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-bold text-white">0€</span>
                    <span class="text-gray-400 text-sm mb-1">/mes</span>
                </div>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    Hasta 4 anuncios activos
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    Chat con otros usuarios
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check text-green-400 w-4 text-center"></i>
                    Valoraciones y reseñas
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    Anuncios ilimitados
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    Destacar un anuncio
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-500">
                    <i class="fas fa-times text-gray-600 w-4 text-center"></i>
                    Insignia Premium en perfil
                </li>
            </ul>
            <div class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-gray-500 rounded-xl text-sm font-medium text-center">
                Plan actual
            </div>
        </div>

        <!-- Plan Premium -->
        <div class="bg-gradient-to-br from-yellow-500/10 to-amber-500/5 rounded-2xl border border-yellow-500/30 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
            <div class="absolute top-4 right-4">
                <span class="px-2.5 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-bold rounded-full border border-yellow-500/30">
                    <i class="fas fa-crown mr-1"></i>RECOMENDADO
                </span>
            </div>
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white mb-1">Premium</h2>
                <div class="flex items-end gap-1">
                    <span class="text-4xl font-bold text-yellow-400">4,99€</span>
                    <span class="text-gray-400 text-sm mb-1">/30 días</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pago único, sin renovación automática</p>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-check text-yellow-400 w-4 text-center"></i>
                    Todo lo del plan gratuito
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-infinity text-yellow-400 w-4 text-center"></i>
                    <strong>Anuncios ilimitados</strong>
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-star text-yellow-400 w-4 text-center"></i>
                    <strong>1 anuncio destacado</strong> (aparece primero)
                </li>
                <li class="flex items-center gap-3 text-sm text-gray-200">
                    <i class="fas fa-crown text-yellow-400 w-4 text-center"></i>
                    Insignia Premium visible en tu perfil
                </li>
            </ul>
            <a href="<?= url('/premium') ?>?action=checkout" class="block w-full px-4 py-3 bg-yellow-500 hover:bg-yellow-400 text-gray-900 rounded-xl text-sm font-bold text-center transition-all shadow-lg shadow-yellow-500/20 hover:shadow-yellow-500/40 transform hover:-translate-y-0.5">
                <i class="fas fa-crown mr-2"></i>Hazte Premium ahora
            </a>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-surface rounded-2xl border border-gray-700 p-6">
        <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <i class="fas fa-question-circle text-primary"></i>
            Preguntas frecuentes
        </h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm font-semibold text-white mb-1">¿Cómo funciona el anuncio destacado?</p>
                <p class="text-sm text-gray-400">Puedes marcar uno de tus anuncios activos como "Destacado" desde <strong class="text-gray-300">Mis Viajes</strong>. Ese anuncio aparecerá al principio de los resultados del dashboard. Solo puedes tener uno destacado a la vez.</p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1">¿Se renueva automáticamente?</p>
                <p class="text-sm text-gray-400">No. Es un pago único de 4,99€ que activa 30 días de Premium. Al finalizar, tu cuenta vuelve al plan gratuito sin cargos adicionales.</p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1">¿Es seguro el pago?</p>
                <p class="text-sm text-gray-400">Sí. El pago se procesa mediante <strong class="text-gray-300">Stripe</strong>, una plataforma de pagos de nivel bancario. Ride4Study nunca almacena los datos de tu tarjeta.</p>
            </div>
            <div class="border-t border-gray-700 pt-4">
                <p class="text-sm font-semibold text-white mb-1">¿Puedo obtener un reembolso?</p>
                <p class="text-sm text-gray-400">Si experimentas algún problema con el pago, contacta con nuestro equipo de soporte a través de la sección de <a href="<?= url('/support') ?>" class="text-primary hover:underline">Ayuda</a>.</p>
            </div>
        </div>
    </div>

    <?php endif; ?>

</div>
