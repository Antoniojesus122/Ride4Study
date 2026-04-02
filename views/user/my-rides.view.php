<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="w-full mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-6 sm:py-8">
    
    <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl lg:text-4xl font-bold text-white"><?= t('myrides.title') ?></h2>
            <p class="text-gray-400 mt-2 lg:text-lg"><?= t('myrides.subtitle') ?></p>
        </div>
        <a href="<?= url('/publish') ?>" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-secondary bg-primary hover:bg-primary-dark transition-all transform hover:-translate-y-0.5 shadow-lg shadow-primary/20">
            <i class="fas fa-plus-circle mr-2"></i> <?= t('myrides.new_ride') ?>
        </a>
    </div>

    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <div class="font-medium">
                <?php if ($flashData['message'] == 'created'): ?>
                    <?= t('myrides.success_created') ?>
                <?php elseif ($flashData['message'] == 'reserved'): ?>
                    <?= t('myrides.success_reserved') ?>
                <?php elseif ($flashData['message'] == 'status_updated'): ?>
                    <?= t('myrides.success_status') ?>
                <?php elseif ($flashData['message'] == 'updated'): ?>
                    <?= t('myrides.success_updated') ?>
                <?php elseif ($flashData['message'] == 'deleted'): ?>
                    <?= t('myrides.success_deleted') ?>
                <?php elseif ($flashData['message'] == 'reservation_cancelled'): ?>
                    <?= t('myrides.success_cancelled') ?>
                <?php elseif ($flashData['message'] == 'trip_completed'): ?>
                    <?= t('myrides.success_completed') ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl flex items-center gap-3">
             <i class="fas fa-exclamation-circle text-xl"></i>
             <div class="font-medium">
                <?php if ($flashData['message'] == 'update_failed'): ?>
                    <?= t('myrides.err_update_failed') ?>
                <?php elseif ($flashData['message'] == 'unauthorized'): ?>
                    <?= t('myrides.err_unauthorized') ?>
                <?php elseif ($flashData['message'] == 'too_late_to_cancel'): ?>
                    <?= t('myrides.err_too_late') ?>
                <?php elseif ($flashData['message'] == 'no_booking'): ?>
                    <?= t('myrides.err_no_booking') ?>
                <?php elseif ($flashData['message'] == 'cancel_failed'): ?>
                    <?= t('myrides.err_cancel_failed') ?>
                <?php elseif ($flashData['message'] == 'trip_not_past'): ?>
                    <?= t('myrides.err_trip_not_past') ?>
                <?php else: ?>
                    <?= t('myrides.err_generic') ?>
                <?php endif; ?>
             </div>
        </div>
    <?php endif; ?>

    <!-- Pestañas de los anuncios -->
    <div class="mb-8 border-b border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button onclick="switchTab('active')" id="tab-active" class="border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm lg:text-base transition-colors flex items-center gap-2">
                <i class="fas fa-route"></i> <?= t('myrides.tab_active') ?>
                <span class="bg-gray-800 text-gray-300 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($activeRides) ?></span>
            </button>
            <button onclick="switchTab('past')" id="tab-past" class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm lg:text-base transition-colors flex items-center gap-2">
                <i class="fas fa-history"></i> <?= t('myrides.tab_history') ?>
                <span class="bg-gray-800 text-gray-500 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($pastRides) ?></span>
            </button>
            <button onclick="switchTab('bookings')" id="tab-bookings" class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm lg:text-base transition-colors flex items-center gap-2">
                <i class="fas fa-ticket-alt"></i> <?= t('myrides.tab_bookings') ?>
                <span class="bg-gray-800 text-gray-500 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($activeBookings) ?></span>
            </button>
            <button onclick="switchTab('past-bookings')" id="tab-past-bookings" class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm lg:text-base transition-colors flex items-center gap-2">
                <i class="fas fa-clipboard-check"></i> <?= t('myrides.tab_past_bookings') ?>
                <span class="bg-gray-800 text-gray-500 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($pastBookings) ?></span>
            </button>
        </nav>
    </div>

    <!-- Sección de viajes activos -->
    <div id="content-active" class="space-y-6">
        <?php if (empty($activeRides)): ?>
            <div class="text-center py-12 sm:py-20 bg-surface/30 rounded-2xl sm:rounded-3xl border border-dashed border-gray-700">
                <div class="w-16 h-16 sm:w-24 sm:h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-car-side text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2"><?= t('myrides.no_active') ?></h3>
                <p class="text-gray-400 mb-6"><?= t('myrides.no_active_desc') ?></p>
                <a href="<?= url('/publish') ?>" class="text-primary font-semibold hover:underline"><?= t('myrides.publish_now') ?></a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6" id="grid-active">
                <?php foreach ($activeRides as $ride): ?>
                    <div class="paginated-item-active"><?= renderRideCard($ride, true, $isPremium) ?></div>
                <?php endforeach; ?>
            </div>
            <div id="pagination-active" class="mt-6"></div>
        <?php endif; ?>
    </div>

    <!-- Sección de viajes pasados -->
    <div id="content-past" class="hidden space-y-6">
        <?php if (empty($pastRides)): ?>
            <div class="text-center py-12 sm:py-20 bg-surface/30 rounded-2xl sm:rounded-3xl border border-dashed border-gray-700">
                <div class="w-16 h-16 sm:w-24 sm:h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-history text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2"><?= t('myrides.no_history') ?></h3>
                <p class="text-gray-400"><?= t('myrides.no_history_desc') ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6" id="grid-past">
                <?php foreach ($pastRides as $ride): ?>
                    <div class="paginated-item-past"><?= renderRideCard($ride, false, false) ?></div>
                <?php endforeach; ?>
            </div>
            <div id="pagination-past" class="mt-6"></div>
        <?php endif; ?>
    </div>

    <!-- Sección de reservas como pasajero -->
    <div id="content-bookings" class="hidden space-y-6">
        <?php if (empty($activeBookings)): ?>
            <div class="text-center py-12 sm:py-20 bg-surface/30 rounded-2xl sm:rounded-3xl border border-dashed border-gray-700">
                <div class="w-16 h-16 sm:w-24 sm:h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-ticket-alt text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2"><?= t('myrides.no_bookings') ?></h3>
                <p class="text-gray-400 mb-6"><?= t('myrides.no_bookings_desc') ?></p>
                <a href="<?= url('/dashboard') ?>" class="text-primary font-semibold hover:underline"><?= t('myrides.search_now') ?></a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6" id="grid-bookings">
                <?php foreach ($activeBookings as $booking): ?>
                    <div class="paginated-item-bookings"><?= renderBookingCard($booking) ?></div>
                <?php endforeach; ?>
            </div>
            <div id="pagination-bookings" class="mt-6"></div>
        <?php endif; ?>
    </div>

    <!-- Sección de historial de reservas (viajes pasados como pasajero/conductor respondedor) -->
    <div id="content-past-bookings" class="hidden space-y-6">
        <?php if (empty($pastBookings)): ?>
            <div class="text-center py-12 sm:py-20 bg-surface/30 rounded-2xl sm:rounded-3xl border border-dashed border-gray-700">
                <div class="w-16 h-16 sm:w-24 sm:h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-clipboard-check text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2"><?= t('myrides.no_past_bookings') ?></h3>
                <p class="text-gray-400"><?= t('myrides.no_past_bookings_desc') ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6" id="grid-past-bookings">
                <?php foreach ($pastBookings as $booking): ?>
                    <div class="paginated-item-past-bookings"><?= renderPastBookingCard($booking) ?></div>
                <?php endforeach; ?>
            </div>
            <div id="pagination-past-bookings" class="mt-6"></div>
        <?php endif; ?>
    </div>

</div>

<!-- Función auxiliar para las tarjetas de viaje -->
<?php
function renderRideCard($ride, $isActive, $isPremium = false) {
    ob_start();
    $isFeatured = !empty($ride['destacado']);
    $passengerCount = count($ride['passengers']);
    $totalSeats = $ride['plazasDisponibles'] + $passengerCount;
    ?>
    <?php $tripEnded = !empty($ride['trip_ended']); ?>
    <div class="bg-surface rounded-2xl border <?= $tripEnded ? 'border-emerald-500/50 shadow-emerald-500/10' : ($isFeatured ? 'border-yellow-500/40' : 'border-gray-700') ?> overflow-hidden hover:border-gray-600 transition-colors shadow-lg relative">
        <?php if ($tripEnded): ?>
            <div class="relative overflow-hidden bg-gradient-to-r from-emerald-600/30 via-emerald-500/20 to-teal-600/30 border-b border-emerald-500/40 px-6 py-4 flex items-center justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_50%,rgba(16,185,129,0.15),transparent_70%)]"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/25 border border-emerald-500/30 flex items-center justify-center">
                        <i class="fas fa-flag-checkered text-emerald-400 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-base font-bold text-emerald-300"><?= t('myrides.trip_ended_title') ?></p>
                        <p class="text-sm text-emerald-400/80"><?= t('myrides.trip_ended_desc') ?></p>
                    </div>
                </div>
                <form action="<?= url('/complete-trip') ?>" method="POST" onsubmit="return confirm('<?= t('myrides.complete_confirm') ?>')" class="relative z-10">
                    <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-gray-900 rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-400/40 flex items-center gap-2 hover:-translate-y-0.5">
                        <i class="fas fa-check-circle"></i> <?= t('myrides.complete_trip') ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($isFeatured): ?>
            <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-yellow-500 via-amber-400 to-yellow-500"></div>
        <?php endif; ?>

        <!-- Header con tipo, fecha, precio y badges -->
        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="px-3 py-1 bg-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/10 text-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-400 rounded-full text-xs font-bold border border-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/20 uppercase tracking-wide">
                    <?= $ride['tipo'] === 'ofrezco' ? t('common.driver') : t('common.passenger') ?>
                </div>
                <?php if ($isFeatured): ?>
                    <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-500/30 flex items-center gap-1">
                        <i class="fas fa-star text-[8px]"></i> <?= t('myrides.featured') ?>
                    </span>
                <?php endif; ?>
                <span class="text-sm text-gray-400 flex items-center gap-2">
                    <i class="far fa-calendar"></i>
                    <?= date('d M, Y', strtotime($ride['fechaSalida'])) ?>
                </span>
            </div>
            <div class="text-right">
                <span class="text-2xl font-bold text-primary"><?= number_format($ride['precio'], 2) ?>€</span>
                <p class="text-xs text-gray-500"><?= t('myrides.per_seat') ?></p>
            </div>
        </div>

        <!-- Contenido principal: 3 columnas -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Col 1: Ruta + descripcion + stats (5/12) -->
                <div class="lg:col-span-5">
                    <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 mb-4">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center pt-1 pb-1">
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-primary bg-surface shadow-md shadow-primary/20 shrink-0"></div>
                                <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/60 to-gray-600 my-1"></div>
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-gray-500 bg-surface shrink-0"></div>
                            </div>
                            <div class="flex-1 flex flex-col justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($ride['nombreOrigen']) ?></h4>
                                    <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1"></i><?= t('dashboard.departure') ?>: <?= substr($ride['horaSalida'], 0, 5) ?></p>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($ride['nombreDestino']) ?></h4>
                                    <?php if (!empty($ride['horaLlegada'])): ?>
                                        <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1"></i><?= t('dashboard.arrival_label') ?>: <?= substr($ride['horaLlegada'], 0, 5) ?></p>
                                    <?php endif; ?>
                                    <?php if ($ride['horaRegreso']): ?>
                                        <p class="text-xs text-purple-400 font-medium mt-1"><i class="fas fa-undo text-[10px] mr-1"></i><?= t('myrides.return_time') ?> <?= substr($ride['horaRegreso'], 0, 5) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats inline -->
                    <div class="flex items-center gap-4 text-sm mb-4">
                        <div class="flex items-center gap-2 bg-gray-800/30 rounded-lg px-3 py-2 border border-gray-700/40">
                            <i class="fas fa-chair text-blue-400"></i>
                            <span class="text-gray-300"><?= $totalSeats ?> <?= t('myrides.total_seats') ?></span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-800/30 rounded-lg px-3 py-2 border border-gray-700/40">
                            <i class="fas fa-users text-green-400"></i>
                            <span class="text-gray-300"><?= $passengerCount ?> <?= t('myrides.joined') ?></span>
                        </div>
                    </div>

                    <?php if (!empty($ride['descripcion'])): ?>
                        <div class="bg-gray-800/20 p-3 rounded-xl border border-gray-700/30">
                            <p class="text-sm text-gray-400 italic leading-relaxed">"<?= htmlspecialchars($ride['descripcion']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Col 2: Pasajeros/Ofertas (4/12) -->
                <div class="lg:col-span-4 lg:border-l lg:border-gray-700/40 lg:pl-6">
                    <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        <?= $ride['tipo'] === 'ofrezco' ? t('myrides.passengers') : t('myrides.driver_offers') ?> (<?= $passengerCount ?>)
                    </h5>

                    <?php if (empty($ride['passengers'])): ?>
                        <p class="text-sm text-gray-500 italic"><?= $ride['tipo'] === 'ofrezco' ? t('myrides.no_passengers') : t('myrides.no_offers') ?></p>
                    <?php else: ?>
                        <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                            <?php foreach ($ride['passengers'] as $passenger): ?>
                                <div class="flex flex-col gap-2 bg-gray-800/40 p-2.5 rounded-lg border border-gray-700/40">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                            <?= strtoupper(substr($passenger['nombre'], 0, 2)) ?>
                                        </div>
                                        <p class="text-sm font-medium text-white truncate flex-1"><?= htmlspecialchars($passenger['nombre']) ?></p>
                                        <a href="<?= url('/profile') ?>?id=<?= $passenger['idUsuario'] ?>" class="text-gray-400 hover:text-white shrink-0"><i class="fas fa-external-link-alt text-xs"></i></a>
                                    </div>
                                    <?php if ($passenger['estado'] === 'pendiente'): ?>
                                        <div class="flex gap-2">
                                            <form action="<?= url('/manage-reservation') ?>" method="POST" class="flex-1">
                                                <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                                                <input type="hidden" name="passenger_id" value="<?= $passenger['idUsuario'] ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="w-full py-1.5 bg-green-500/10 hover:bg-green-500/20 text-green-500 rounded-lg text-xs font-bold border border-green-500/20 transition-colors"><i class="fas fa-check mr-1"></i><?= $ride['tipo'] === 'ofrezco' ? t('myrides.accept') : t('myrides.accept_driver') ?></button>
                                            </form>
                                            <form action="<?= url('/manage-reservation') ?>" method="POST" class="flex-1">
                                                <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                                                <input type="hidden" name="passenger_id" value="<?= $passenger['idUsuario'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="w-full py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg text-xs font-bold border border-red-500/20 transition-colors"><i class="fas fa-times mr-1"></i><?= $ride['tipo'] === 'ofrezco' ? t('myrides.reject') : t('myrides.reject_driver') ?></button>
                                            </form>
                                        </div>
                                    <?php elseif ($passenger['estado'] === 'aceptado'): ?>
                                        <div class="text-center py-1 bg-green-500/10 text-green-500 rounded-lg text-xs font-bold border border-green-500/20"><i class="fas fa-check mr-1"></i><?= t('myrides.confirmed') ?></div>
                                    <?php elseif ($passenger['estado'] === 'rechazado'): ?>
                                        <div class="text-center py-1 bg-red-500/10 text-red-500 rounded-lg text-xs font-bold border border-red-500/20"><i class="fas fa-times mr-1"></i><?= t('myrides.rejected') ?></div>
                                    <?php elseif ($passenger['estado'] === 'completado'): ?>
                                        <div class="text-center py-1 bg-primary/10 text-primary rounded-lg text-xs font-bold border border-primary/20"><i class="fas fa-check-double mr-1"></i><?= t('myrides.completed') ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Col 3: Acciones (3/12) -->
                <div class="lg:col-span-3 lg:border-l lg:border-gray-700/40 lg:pl-6 flex flex-col justify-between">
                    <?php if ($isActive && !$tripEnded): ?>
                        <div class="space-y-2.5">
                            <?php if ($isPremium): ?>
                                <button onclick="toggleFeatured(<?= $ride['idAnuncio'] ?>, this)" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 <?= $isFeatured ? 'bg-yellow-500/20 text-yellow-400 border-yellow-500/40' : 'bg-gray-800/50 text-gray-400 border-gray-700' ?> rounded-xl text-sm font-medium transition-colors border" data-featured="<?= $isFeatured ? '1' : '0' ?>">
                                    <i class="fas fa-star text-xs"></i> <?= $isFeatured ? t('myrides.remove_featured') : t('myrides.set_featured') ?>
                                </button>
                            <?php else: ?>
                                <a href="<?= url('/premium') ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800/50 text-gray-500 rounded-xl text-xs font-medium border border-gray-700 hover:text-gray-300 transition-colors">
                                    <i class="fas fa-lock text-xs"></i> <?= t('myrides.set_featured_premium') ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= url('/edit-ride') ?>?id=<?= $ride['idAnuncio'] ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-medium transition-colors border border-gray-600">
                                <i class="fas fa-edit"></i> <?= t('myrides.edit') ?>
                            </a>
                            <button onclick="confirmDelete(<?= $ride['idAnuncio'] ?>)" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-400 rounded-xl text-sm font-medium transition-colors border border-red-500/20">
                                <i class="fas fa-trash-alt"></i> <?= t('myrides.delete') ?>
                            </button>
                        </div>
                    <?php elseif ($isActive && $tripEnded): ?>
                        <div class="space-y-2.5">
                            <div class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl text-sm font-medium border border-emerald-500/20">
                                <i class="fas fa-hourglass-half"></i> <?= t('myrides.awaiting_completion') ?>
                            </div>
                            <a href="<?= url('/chat') ?>?anuncio_id=<?= $ride['idAnuncio'] ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-medium transition-colors border border-gray-600">
                                <i class="fas fa-comment"></i> <?= t('myrides.contact') ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <?php
                            // Comprobar si hay pasajeros aceptados para poder completar el viaje
                            $hasAccepted = false;
                            $allCompleted = true;
                            foreach ($ride['passengers'] as $p) {
                                if ($p['estado'] === 'aceptado') { $hasAccepted = true; }
                                if ($p['estado'] !== 'completado') { $allCompleted = false; }
                            }
                        ?>
                        <?php if ($allCompleted && !empty($ride['passengers'])): ?>
                            <div class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-green-500/10 text-green-500 rounded-xl text-sm font-medium border border-green-500/20">
                                <i class="fas fa-check-double"></i> <?= t('myrides.completed') ?>
                            </div>
                        <?php elseif ($hasAccepted): ?>
                            <form action="<?= url('/complete-trip') ?>" method="POST" onsubmit="return confirm('<?= t('myrides.complete_confirm') ?>')">
                                <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl text-sm font-medium border border-primary/30 transition-colors">
                                    <i class="fas fa-check-circle"></i> <?= t('myrides.complete_trip') ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800/50 text-gray-500 rounded-xl text-sm font-medium border border-gray-700 cursor-not-allowed">
                                <i class="fas fa-archive"></i> <?= t('myrides.archived') ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderBookingCard($booking) {
    ob_start();
    ?>
    <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden hover:border-gray-600 transition-colors shadow-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if ($booking['tipo'] === 'ofrezco'): ?>
                    <div class="px-3 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-bold border border-blue-500/20 uppercase tracking-wide">
                        <i class="fas fa-user-friends mr-1"></i> <?= t('common.passenger') ?>
                    </div>
                <?php else: ?>
                    <div class="px-3 py-1 bg-purple-500/10 text-purple-400 rounded-full text-xs font-bold border border-purple-500/20 uppercase tracking-wide">
                        <i class="fas fa-car mr-1"></i> <?= t('common.driver') ?>
                    </div>
                <?php endif; ?>
                <span class="text-sm text-gray-400 flex items-center gap-2">
                    <i class="far fa-calendar"></i> <?= date('d M, Y', strtotime($booking['fechaSalida'])) ?>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($booking['estadoReserva'] === 'pendiente'): ?>
                    <span class="px-3 py-1.5 bg-yellow-500/10 text-yellow-500 rounded-full text-xs font-bold border border-yellow-500/20"><i class="fas fa-clock mr-1"></i> <?= t('myrides.status_pending') ?></span>
                <?php elseif ($booking['estadoReserva'] === 'aceptado'): ?>
                    <span class="px-3 py-1.5 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20"><i class="fas fa-check mr-1"></i> <?= t('myrides.status_confirmed') ?></span>
                <?php else: ?>
                    <span class="px-3 py-1.5 bg-red-500/10 text-red-500 rounded-full text-xs font-bold border border-red-500/20"><i class="fas fa-times mr-1"></i> <?= t('myrides.status_rejected') ?></span>
                <?php endif; ?>
                <?php if ($booking['tipo'] === 'ofrezco' && $booking['precio']): ?>
                    <span class="text-xl font-bold text-primary"><?= number_format($booking['precio'], 2) ?>€</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenido: 3 columnas -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Col 1: Ruta (5/12) -->
                <div class="lg:col-span-5">
                    <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 mb-4">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center pt-1 pb-1">
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-primary bg-surface shadow-md shadow-primary/20 shrink-0"></div>
                                <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/60 to-gray-600 my-1"></div>
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-gray-500 bg-surface shrink-0"></div>
                            </div>
                            <div class="flex-1 flex flex-col justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($booking['nombreOrigen']) ?></h4>
                                    <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1"></i><?= t('dashboard.departure') ?>: <?= substr($booking['horaSalida'], 0, 5) ?></p>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($booking['nombreDestino']) ?></h4>
                                    <?php if ($booking['horaRegreso']): ?>
                                        <p class="text-xs text-purple-400 font-medium mt-1"><i class="fas fa-undo text-[10px] mr-1"></i><?= t('myrides.return_time') ?> <?= substr($booking['horaRegreso'], 0, 5) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($booking['descripcion'])): ?>
                        <div class="bg-gray-800/20 p-3 rounded-xl border border-gray-700/30">
                            <p class="text-sm text-gray-400 italic">"<?= htmlspecialchars($booking['descripcion']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Col 2: Info del conductor/pasajero (4/12) -->
                <div class="lg:col-span-4 lg:border-l lg:border-gray-700/40 lg:pl-6">
                    <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        <?= $booking['tipo'] === 'ofrezco' ? t('common.driver') : t('common.passenger') ?>
                    </h5>
                    <div class="flex items-center gap-4 bg-gray-800/30 p-4 rounded-xl border border-gray-700/40">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-base font-bold text-secondary shrink-0">
                            <?= strtoupper(substr($booking['nombreUsuario'], 0, 2)) ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-white"><?= htmlspecialchars($booking['nombreUsuario']) ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?= $booking['tipo'] === 'ofrezco' ? t('common.driver') : t('common.passenger') ?></p>
                        </div>
                        <a href="<?= url('/profile') ?>?id=<?= $booking['idUsuario'] ?>" class="text-primary hover:text-primary-dark transition-colors">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 3: Acciones (3/12) -->
                <div class="lg:col-span-3 lg:border-l lg:border-gray-700/40 lg:pl-6 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <a href="<?= url('/chat') ?>?anuncio_id=<?= $booking['idAnuncio'] ?>&other_user_id=<?= $booking['idUsuario'] ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-secondary rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                            <i class="fas fa-comment"></i> <?= t('myrides.contact') ?>
                        </a>
                        <a href="<?= url('/profile') ?>?id=<?= $booking['idUsuario'] ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-medium transition-colors border border-gray-600">
                            <i class="fas fa-user"></i> <?= t('myrides.view_profile') ?>
                        </a>
                        <?php if ($booking['estadoReserva'] !== 'rechazado'): ?>
                            <button onclick="confirmCancelReservation(<?= $booking['idAnuncio'] ?>, '<?= $booking['estadoReserva'] ?>')" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-400 rounded-xl text-sm font-medium transition-colors border border-red-500/20">
                                <i class="fas fa-times-circle"></i> <?= t('myrides.cancel_booking') ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderPastBookingCard($booking) {
    ob_start();
    $isCompleted = $booking['estadoReserva'] === 'completado';
    $isAccepted = $booking['estadoReserva'] === 'aceptado';
    ?>
    <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden hover:border-gray-600 transition-colors shadow-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if ($booking['tipo'] === 'ofrezco'): ?>
                    <div class="px-3 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-bold border border-blue-500/20 uppercase tracking-wide">
                        <i class="fas fa-user-friends mr-1"></i> <?= t('common.passenger') ?>
                    </div>
                <?php else: ?>
                    <div class="px-3 py-1 bg-purple-500/10 text-purple-400 rounded-full text-xs font-bold border border-purple-500/20 uppercase tracking-wide">
                        <i class="fas fa-car mr-1"></i> <?= t('common.driver') ?>
                    </div>
                <?php endif; ?>
                <span class="text-sm text-gray-400 flex items-center gap-2">
                    <i class="far fa-calendar"></i> <?= date('d M, Y', strtotime($booking['fechaSalida'])) ?>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($isCompleted): ?>
                    <span class="px-3 py-1.5 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20"><i class="fas fa-check-double mr-1"></i> <?= t('myrides.completed') ?></span>
                <?php elseif ($isAccepted): ?>
                    <span class="px-3 py-1.5 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20"><i class="fas fa-check mr-1"></i> <?= t('myrides.status_confirmed') ?></span>
                <?php else: ?>
                    <span class="px-3 py-1.5 bg-gray-500/10 text-gray-400 rounded-full text-xs font-bold border border-gray-500/20"><i class="fas fa-archive mr-1"></i> <?= t('myrides.archived') ?></span>
                <?php endif; ?>
                <?php if ($booking['tipo'] === 'ofrezco' && $booking['precio']): ?>
                    <span class="text-xl font-bold text-primary"><?= number_format($booking['precio'], 2) ?>€</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Columna 1: Ruta) -->
                <div class="lg:col-span-5">
                    <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 mb-4">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center pt-1 pb-1">
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-primary bg-surface shadow-md shadow-primary/20 shrink-0"></div>
                                <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/60 to-gray-600 my-1"></div>
                                <div class="w-3.5 h-3.5 rounded-full border-[3px] border-gray-500 bg-surface shrink-0"></div>
                            </div>
                            <div class="flex-1 flex flex-col justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($booking['nombreOrigen']) ?></h4>
                                    <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1"></i><?= t('dashboard.departure') ?>: <?= substr($booking['horaSalida'], 0, 5) ?></p>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($booking['nombreDestino']) ?></h4>
                                    <?php if ($booking['horaRegreso']): ?>
                                        <p class="text-xs text-purple-400 font-medium mt-1"><i class="fas fa-undo text-[10px] mr-1"></i><?= t('myrides.return_time') ?> <?= substr($booking['horaRegreso'], 0, 5) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna 2: Info del conductor/pasajero -->
                <div class="lg:col-span-4 lg:border-l lg:border-gray-700/40 lg:pl-6">
                    <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        <?= $booking['tipo'] === 'ofrezco' ? t('common.driver') : t('common.passenger') ?>
                    </h5>
                    <div class="flex items-center gap-4 bg-gray-800/30 p-4 rounded-xl border border-gray-700/40">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-base font-bold text-secondary shrink-0">
                            <?= strtoupper(substr($booking['nombreUsuario'], 0, 2)) ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-white"><?= htmlspecialchars($booking['nombreUsuario']) ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?= $booking['tipo'] === 'ofrezco' ? t('common.driver') : t('common.passenger') ?></p>
                        </div>
                        <a href="<?= url('/profile') ?>?id=<?= $booking['idUsuario'] ?>" class="text-primary hover:text-primary-dark transition-colors">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>

                <!-- Columna 3: Acciones -->
                <div class="lg:col-span-3 lg:border-l lg:border-gray-700/40 lg:pl-6 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <?php if ($isCompleted || $isAccepted): ?>
                            <?php
                            // Obtener idViaje para el enlace de valoración
                            $viajeId = $booking['idViaje'] ?? null;
                            ?>
                            <?php if ($viajeId): ?>
                                <a href="<?= url('/rating') ?>?viaje=<?= $viajeId ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-secondary rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                                    <i class="fas fa-star"></i> <?= t('myrides.rate_trip') ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <a href="<?= url('/profile') ?>?id=<?= $booking['idUsuario'] ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-medium transition-colors border border-gray-600">
                            <i class="fas fa-user"></i> <?= t('myrides.view_profile') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
    function switchTab(tab) {
        const tabs = ['active', 'past', 'bookings', 'past-bookings'];

        // Restablecer todas las pestañas
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            const content = document.getElementById('content-' + t);
            if (btn) {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-400');
            }
            if (content) content.classList.add('hidden');
        });

        // Mostrar la pestaña seleccionada
        const selectedBtn = document.getElementById('tab-' + tab);
        const selectedContent = document.getElementById('content-' + tab);
        if (selectedBtn) {
            selectedBtn.classList.replace('border-transparent', 'border-primary');
            selectedBtn.classList.replace('text-gray-400', 'text-primary');
        }
        if (selectedContent) selectedContent.classList.remove('hidden');
    }

    let rideToCancelReservation = null;

    function confirmCancelReservation(rideId, estado) {
        rideToCancelReservation = rideId;
        const warningEl = document.getElementById('cancelReservationWarning');
        if (estado === 'aceptado') {
            warningEl.textContent = '<?= t('myrides.cancel_accepted_warning') ?>';
        } else {
            warningEl.textContent = '<?= t('myrides.cancel_pending_info') ?>';
        }
        document.getElementById('cancelReservationModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCancelReservationModal() {
        document.getElementById('cancelReservationModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        rideToCancelReservation = null;
    }

    function executeCancelReservation() {
        if (rideToCancelReservation) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("/cancel-reservation") ?>';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ride_id';
            input.value = rideToCancelReservation;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    let rideToDelete = null;

    function confirmDelete(rideId) {
        rideToDelete = rideId;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        rideToDelete = null;
    }

    function executeDelete() {
        if (rideToDelete) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("/delete-ride") ?>';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = rideToDelete;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleFeatured(rideId, btn) {
        const body = new FormData();
        body.append('ride_id', rideId);
        fetch('<?= url("/toggle-featured") ?>', { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para reflejar el cambio
                    window.location.reload();
                } else if (typeof showToast === 'function') {
                    showToast(data.message || '<?= t('myrides.err_featured') ?>', false);
                }
            })
            .catch(() => {
                if (typeof showToast === 'function') showToast('<?= t('myrides.err_connection') ?>', false);
            });
    }

    // Detectar parámetro 'tab' en la URL al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (['bookings', 'past', 'past-bookings'].includes(tabParam)) {
            switchTab(tabParam);
        } else {
            switchTab('active');
        }
        
        // Cerrar modales al hacer clic fuera
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
        document.getElementById('cancelReservationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCancelReservationModal();
            }
        });

        // Cerrar modales con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeCancelReservationModal();
            }
        });
    });
</script>

<!-- Modal de confirmación para eliminar viaje -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-2xl max-w-md w-full transform transition-all">
        <!-- Header del modal -->
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white"><?= t('myrides.delete_title') ?></h3>
                    <p class="text-sm text-gray-400"><?= t('myrides.delete_warning') ?></p>
                </div>
            </div>
        </div>
        
        <!-- Contenido del modal -->
        <div class="p-6">
            <p class="text-gray-300 leading-relaxed">
                <?= t('myrides.delete_confirm') ?>
            </p>
            
            <div class="mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                <p class="text-sm text-red-400 flex items-start gap-2">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    <span><?= t('myrides.delete_permanent') ?></span>
                </p>
            </div>
        </div>
        
        <!-- Footer con botones -->
        <div class="p-6 bg-gray-800/50 border-t border-gray-700 flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition-all">
                <i class="fas fa-times mr-2"></i><?= t('myrides.cancel') ?>
            </button>
            <button onclick="executeDelete()" class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-red-500/20 hover:shadow-red-500/40">
                <i class="fas fa-trash-alt mr-2"></i><?= t('myrides.delete') ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cancelar reserva -->
<div id="cancelReservationModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-2xl max-w-md w-full transform transition-all">
        <!-- Header del modal -->
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white"><?= t('myrides.cancel_title') ?></h3>
                    <p class="text-sm text-gray-400"><?= t('myrides.delete_warning') ?></p>
                </div>
            </div>
        </div>

        <!-- Contenido del modal -->
        <div class="p-6">
            <p class="text-gray-300 leading-relaxed">
                <?= t('myrides.cancel_confirm') ?>
            </p>

            <div class="mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                <p class="text-sm text-red-400 flex items-start gap-2">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    <span id="cancelReservationWarning"><?= t('myrides.cancel_pending_info') ?></span>
                </p>
            </div>
        </div>

        <!-- Footer con botones -->
        <div class="p-6 bg-gray-800/50 border-t border-gray-700 flex gap-3">
            <button onclick="closeCancelReservationModal()" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition-all">
                <i class="fas fa-arrow-left mr-2"></i><?= t('myrides.cancel_back') ?>
            </button>
            <button onclick="executeCancelReservation()" class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-red-500/20 hover:shadow-red-500/40">
                <i class="fas fa-times-circle mr-2"></i><?= t('myrides.cancel_booking') ?>
            </button>
        </div>
    </div>
</div>

<script>
// Paginación client-side para las 3 pestañas
const ITEMS_PER_PAGE = 4;
const paginationState = {};

function goToPage(tabKey, page) {
    const state = paginationState[tabKey];
    if (!state) return;

    const { items, totalPages, paginationId } = state;
    if (page < 1 || page > totalPages) return;

    state.currentPage = page;

    // Mostrar/ocultar items
    items.forEach((item, i) => {
        const start = (page - 1) * ITEMS_PER_PAGE;
        item.style.display = (i >= start && i < start + ITEMS_PER_PAGE) ? '' : 'none';
    });

    // Renderizar controles de paginación
    const container = document.getElementById(paginationId);
    if (!container) return;

    let html = '<div class="flex items-center justify-between bg-surface rounded-xl border border-gray-700 px-5 py-3">';
    html += '<p class="text-sm text-gray-400"><?= t('myrides.page') ?> <span class="text-white font-semibold">' + page + '</span> <?= t('myrides.of') ?> ' + totalPages + ' <span class="text-gray-500 ml-1">(' + items.length + ' <?= t('myrides.total_items') ?>)</span></p>';
    html += '<div class="flex items-center gap-1">';

    // Anterior
    html += `<button onclick="goToPage('${tabKey}', ${page - 1})" ${page === 1 ? 'disabled' : ''} class="px-3 py-2 rounded-lg text-sm font-medium transition-colors ${page === 1 ? 'text-gray-600 cursor-not-allowed' : 'text-gray-300 hover:bg-gray-800 hover:text-white'}"><i class="fas fa-chevron-left"></i></button>`;

    // Números
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    if (startPage > 1) {
        html += `<button onclick="goToPage('${tabKey}', 1)" class="w-10 h-10 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">1</button>`;
        if (startPage > 2) html += '<span class="px-1 text-gray-600">...</span>';
    }

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === page;
        html += `<button onclick="goToPage('${tabKey}', ${i})" class="w-10 h-10 rounded-lg text-sm font-medium transition-colors ${isActive ? 'bg-primary text-secondary font-bold shadow-lg shadow-primary/20' : 'text-gray-400 hover:bg-gray-800 hover:text-white'}">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="px-1 text-gray-600">...</span>';
        html += `<button onclick="goToPage('${tabKey}', ${totalPages})" class="w-10 h-10 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">${totalPages}</button>`;
    }

    // Siguiente
    html += `<button onclick="goToPage('${tabKey}', ${page + 1})" ${page === totalPages ? 'disabled' : ''} class="px-3 py-2 rounded-lg text-sm font-medium transition-colors ${page === totalPages ? 'text-gray-600 cursor-not-allowed' : 'text-gray-300 hover:bg-gray-800 hover:text-white'}"><i class="fas fa-chevron-right"></i></button>`;

    html += '</div></div>';
    container.innerHTML = html;

    // Scroll arriba suavemente
    window.scrollTo({ top: 200, behavior: 'smooth' });
}

function initPagination(tabKey, itemClass, paginationId) {
    const items = Array.from(document.querySelectorAll('.' + itemClass));
    if (!items.length) return;

    const totalPages = Math.ceil(items.length / ITEMS_PER_PAGE);
    paginationState[tabKey] = { items, totalPages, paginationId, currentPage: 1 };

    if (totalPages <= 1) return;
    goToPage(tabKey, 1);
}

document.addEventListener('DOMContentLoaded', function() {
    initPagination('active', 'paginated-item-active', 'pagination-active');
    initPagination('past', 'paginated-item-past', 'pagination-past');
    initPagination('bookings', 'paginated-item-bookings', 'pagination-bookings');
    initPagination('past-bookings', 'paginated-item-past-bookings', 'pagination-past-bookings');
});
</script>

</body>
</html>