<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-8">
        <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-white"><?= t('dashboard.title') ?></h2>
            <p class="mt-2 text-gray-400 lg:text-lg"><?= t('dashboard.subtitle') ?></p>
        </div>  

        <div class="flex flex-col md:flex-row items-start gap-8">
            <div class="flex-1 w-full">
                <div class="bg-surface rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8 border border-gray-700/50 shadow-lg">
                <h3 class="text-sm lg:text-base font-semibold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-filter text-primary"></i> <?= t('dashboard.filters') ?>
                </h3>
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <!-- Tipo -->
                <div class="md:col-span-2">
                     <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1"><?= t('dashboard.type') ?></label>
                     <div class="relative group">
                        <select name="tipo" class="appearance-none block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-3 pr-8 text-white focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none cursor-pointer">
                            <option value=""><?= t('dashboard.all') ?></option>
                            <option value="Ofrezco" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Ofrezco') ? 'selected' : '' ?>><?= t('dashboard.driver') ?></option>
                            <option value="Busco" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Busco') ? 'selected' : '' ?>><?= t('dashboard.passenger') ?></option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                             <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                     </div>
                </div>

                <!-- Origen (con autocompletado) -->
                <div class="md:col-span-3 relative">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1"><?= t('dashboard.origin') ?></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-500 group-focus-within:text-primary transition-colors"></i>
                        </div>
                        <input type="text" name="origen" id="filter-origen" autocomplete="off"
                            value="<?= htmlspecialchars($_GET['origen'] ?? '') ?>"
                            class="block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none"
                            placeholder="<?= t('dashboard.origin_placeholder') ?>">
                    </div>
                    <div id="filter-origen-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
                </div>

                <!-- Destino (con autocompletado) -->
                <div class="md:col-span-3 relative">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1"><?= t('dashboard.destination') ?></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-flag-checkered text-gray-500 group-focus-within:text-primary transition-colors"></i>
                        </div>
                        <input type="text" name="destino" id="filter-destino" autocomplete="off"
                            value="<?= htmlspecialchars($_GET['destino'] ?? '') ?>"
                            class="block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none"
                            placeholder="<?= t('dashboard.destination_placeholder') ?>">
                    </div>
                    <div id="filter-destino-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
                </div>

                <!-- Fecha -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1"><?= t('dashboard.date') ?></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="far fa-calendar-alt text-gray-500 group-focus-within:text-primary transition-colors"></i>
                        </div>
                        <input type="date" name="fecha" value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>" 
                            class="block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none [color-scheme:dark]">
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary-dark text-secondary font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-primary/20 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> <?= t('dashboard.search') ?>
                    </button>
                    <a href="<?= url('/dashboard') ?>" class="flex items-center justify-center px-3 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl transition-all border border-gray-600 group" title="<?= t('dashboard.clear_filters') ?>">
                        <i class="fas fa-times group-hover:rotate-90 transition-transform duration-300"></i>
                    </a>
                </div>
            </form>
            
            <!-- Tags de filtros rápidos-->
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-xs text-gray-500 mr-2 self-center"><?= t('dashboard.quick_filters') ?></span>
                <a href="?fecha=<?= date('Y-m-d') ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-full text-xs text-gray-300 transition-colors"><?= t('dashboard.today') ?></a>
                <a href="?fecha=<?= date('Y-m-d', strtotime('+1 day')) ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-full text-xs text-gray-300 transition-colors"><?= t('dashboard.tomorrow') ?></a>
            </div>
        </div>

        <!-- Resultados -->
        <div class="space-y-4">
             <div class="flex justify-between items-center mb-4">
                <p class="text-sm lg:text-base text-gray-400"><?= t('dashboard.showing_results') ?> <strong><?= $totalItems ?></strong> <?= t('dashboard.results_available') ?></p>
             </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                 <?php if (empty($rides)): ?>
                    <div class="col-span-full py-16 text-center border-2 border-dashed border-gray-700 rounded-2xl bg-surface/30">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-surface rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search-location text-2xl sm:text-3xl text-gray-500"></i>
                        </div>
                        <h3 class="text-lg font-medium text-white"><?= t('dashboard.no_rides') ?></h3>
                        <p class="text-gray-400 mt-2 max-w-sm mx-auto"><?= t('dashboard.no_rides_desc') ?></p>
                        <a href="<?= url('/dashboard') ?>" class="inline-block mt-4 text-primary font-semibold hover:underline"><?= t('dashboard.clear_filters') ?></a>
                    </div>
                <?php else: ?>
                    <?php foreach ($rides as $ride): ?>
                        <div class="group relative bg-surface rounded-2xl p-5 border <?= !empty($ride['destacado']) ? 'border-yellow-500/40' : 'border-gray-700' ?> hover:border-primary/50 transition-all duration-300 shadow-md hover:shadow-xl hover:shadow-primary/5 overflow-hidden">
                            <?php if (!empty($ride['destacado'])): ?>
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-yellow-500 via-amber-400 to-yellow-500"></div>
                                <div class="absolute top-2 left-3 z-20">
                                    <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-500/30 flex items-center gap-1">
                                        <i class="fas fa-star text-[8px]"></i> Dest.
                                    </span>
                                </div>
                            <?php endif; ?>
                            <!-- Precio -->
                            <div class="absolute top-0 right-0 bg-gray-800 rounded-bl-2xl rounded-tr-xl px-4 py-2 border-b border-l border-gray-700 flex flex-col items-end">
                                <span class="text-xs font-bold uppercase tracking-wider mb-0.5 <?= $ride['tipo'] == 'ofrezco' ? 'text-primary' : 'text-purple-400' ?>">
                                    <?= $ride['tipo'] == 'ofrezco' ? t('dashboard.driver') : t('dashboard.passenger') ?>
                                </span>
                                <?php if($ride['tipo'] == 'ofrezco'): ?>
                                    <span class="text-lg font-bold text-white"><?= number_format($ride['precio'], 0) ?>€</span>
                                <?php endif; ?>
                            </div>

                            <!-- Usuario del anuncio -->
                            <div class="flex items-center gap-3 mb-6 pr-16">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center text-base font-bold text-white shadow-inner overflow-hidden">
                                    <?php if (!empty($ride['foto_perfil']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $ride['foto_perfil'])): ?>
                                        <?php $rpf = htmlspecialchars($ride['foto_perfil']); $rver = filemtime(__DIR__ . '/../../public/uploads/profiles/' . $ride['foto_perfil']); ?>
                                        <img src="public/uploads/profiles/<?= $rpf ?>?v=<?= $rver ?>" alt="avatar" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?= strtoupper(substr($ride['nombreUsuario'], 0, 2)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="text-sm lg:text-base font-bold text-white"><?= htmlspecialchars($ride['nombreUsuario']) ?></h4>
                                    <div class="flex items-center text-xs lg:text-sm gap-2">
                                        <span class="text-gray-400"><i class="fas fa-star text-yellow-500 mr-1"></i> <?= number_format((float)($ride['rating'] ?? 0), 1) ?></span>
                                        <span class="text-gray-600">•</span>
                                        <?php if ($ride['estado_verificacion'] == 2): ?>
                                            <span class="text-green-400"><?= t('dashboard.verified') ?></span>
                                        <?php elseif ($ride['estado_verificacion'] == 1): ?>
                                            <span class="text-yellow-400"><?= t('dashboard.pending') ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400"><?= t('dashboard.not_verified') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $ridePrefs = json_decode($ride['preferencias_viaje'] ?? '[]', true) ?: [];
                                    if (!empty($ridePrefs)):
                                        $prefIcons = ['silencio'=>'fa-volume-mute','charla'=>'fa-comments','mascotas'=>'fa-paw','no_fumar'=>'fa-smoking-ban','equipaje'=>'fa-suitcase','musica'=>'fa-music'];
                                        $prefColors = ['silencio'=>'text-blue-400','charla'=>'text-green-400','mascotas'=>'text-yellow-400','no_fumar'=>'text-red-400','equipaje'=>'text-purple-400','musica'=>'text-pink-400'];
                                    ?>
                                    <div class="flex items-center gap-1 mt-1">
                                        <?php foreach ($ridePrefs as $p): if (isset($prefIcons[$p])): ?>
                                        <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center" title="<?= t('pref.' . $p) ?>">
                                            <i class="fas <?= $prefIcons[$p] ?> text-[10px] <?= $prefColors[$p] ?>"></i>
                                        </span>
                                        <?php endif; endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Visual de la ruta -->
                            <div class="relative py-4">
                                <!-- Línea de la ruta -->
                                <div class="absolute left-[7px] top-6 bottom-6 w-0.5 bg-gray-700"></div>
                                
                                <div class="flex items-start mb-6 relative">
                                    <div class="w-4 h-4 rounded-full border-2 border-primary bg-gray-900 z-10 shrink-0 mt-1"></div>
                                    <div class="ml-4">
                                        <p class="text-sm lg:text-base font-semibold text-white"><?= htmlspecialchars($ride['nombreOrigen']) ?></p>
                                        <p class="text-xs lg:text-sm text-primary font-mono mt-0.5"><?= substr($ride['horaSalida'], 0, 5) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start relative">
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-500 bg-gray-900 z-10 shrink-0 mt-1"></div>
                                    <div class="ml-4">
                                        <p class="text-sm lg:text-base font-semibold text-white"><?= htmlspecialchars($ride['nombreDestino']) ?></p>
                                        <p class="text-xs lg:text-sm text-gray-500 mt-0.5">
                                            <?php if (!empty($ride['horaLlegada'])): ?>
                                                <?= t('dashboard.arrival_approx') ?> <?= substr($ride['horaLlegada'], 0, 5) ?>
                                            <?php else: ?>
                                                <?= t('dashboard.arrival_approx') ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de la fecha, plazos y boton de acción -->
                            <div class="mt-4 pt-4 border-t border-gray-700/50 flex justify-between items-center">
                                <div class="flex items-center gap-4 text-xs lg:text-sm text-gray-400">
                                     <span class="flex items-center" title="<?= date('d/m/Y', strtotime($ride['fechaSalida'])) ?>">
                                        <i class="far fa-calendar text-gray-500 mr-2"></i>
                                        <?= date('d M', strtotime($ride['fechaSalida'])) ?>
                                     </span>
                                     <span class="flex items-center" title="Plazas disponibles">
                                        <i class="fas fa-chair text-gray-500 mr-2"></i>
                                        <?= $ride['plazasDisponibles'] ?>
                                     </span>
                                </div>
                                <button type="button" class="view-ride-btn text-sm lg:text-base font-medium text-white hover:text-primary transition-colors relative z-20"
                                        data-ride='<?= htmlspecialchars(json_encode($ride), ENT_QUOTES, 'UTF-8') ?>'>
                                    <?= t('dashboard.view_detail') ?> <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </button>
                            </div>
                            
                            <button type="button" class="absolute inset-0 z-10 w-full h-full cursor-pointer view-ride-btn" 
                               data-ride='<?= htmlspecialchars(json_encode($ride), ENT_QUOTES, 'UTF-8') ?>'></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
             <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-center space-x-2 mt-12">
                <?php
                    $paginationParams = http_build_query(array_filter([
                        'origen'  => $_GET['origen'] ?? '',
                        'destino' => $_GET['destino'] ?? '',
                        'fecha'   => $_GET['fecha'] ?? '',
                        'tipo'    => $_GET['tipo'] ?? '',
                    ]));
                    $paginationBase = $paginationParams ? '&' . $paginationParams : '';
                ?>
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= $paginationBase ?>" class="p-2 w-10 h-10 flex items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $paginationBase ?>"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border <?= $i === $currentPage ? 'bg-primary border-primary text-secondary font-bold' : 'border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white' ?> transition-colors">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= $paginationBase ?>" class="p-2 w-10 h-10 flex items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Barra lateral -->
    <aside class="w-full md:w-72 lg:w-80 xl:w-96 hidden md:block shrink-0">
        <div class="sticky top-28 space-y-5">
             <!-- Alertas -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php 
                    switch($_GET['error']) {
                        case 'own_ride': echo t('dashboard.err_own_ride'); break;
                        case 'already_booked': echo t('dashboard.err_already_booked'); break;
                        case 'no_seats': echo t('dashboard.err_no_seats'); break;
                        case 'reservation_failed': echo t('dashboard.err_reserve_failed'); break;
                        case 'invalid_type': echo t('dashboard.err_type'); break;
                        default: echo t('dashboard.err_generic');
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-surface rounded-2xl p-6 border border-gray-700 shadow-xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="font-bold text-white"><?= t('dashboard.quick_actions') ?></h3>
                </div>
                
                <nav class="space-y-3">
                    <a href="<?= url('/publish') ?>" class="flex items-center justify-between p-3 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark transition-all group">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-plus-circle"></i> <?= t('dashboard.publish_ride') ?>
                        </span>
                        <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                    
                    <a href="<?= url('/my-rides') ?>?tab=bookings" class="flex items-center justify-between p-3 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-750 border border-gray-700 transition-all">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-network-wired text-gray-500"></i> <?= t('dashboard.my_bookings') ?>
                        </span>
                    </a>
                     <a href="<?= url('/profile') ?>" class="flex items-center justify-between p-3 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-750 border border-gray-700 transition-all">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-user-edit text-gray-500"></i> <?= t('dashboard.edit_profile') ?>
                        </span>
                    </a>
                </nav>
            </div>

            <!-- Widget CO2 -->
            <a href="<?= url('/ranking') ?>" class="block bg-gradient-to-br from-green-900/40 to-emerald-900/30 rounded-2xl p-5 border border-green-500/20 hover:border-green-500/40 transition-all group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white"><?= t('co2.saved') ?></h4>
                        <p class="text-xs text-gray-400"><?= t('co2.view_ranking') ?> <i class="fas fa-arrow-right text-[10px] ml-1 opacity-0 group-hover:opacity-100 transition-opacity"></i></p>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-2xl font-bold text-green-400"><?= number_format($myCO2, 1) ?> kg</p>
                        <p class="text-[10px] text-gray-500 mt-0.5"><?= t('co2.your_position') ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-400"><?= number_format($totalCO2Global, 0) ?> kg</p>
                        <p class="text-[10px] text-gray-500 mt-0.5"><?= t('co2.total_saved') ?></p>
                    </div>
                </div>
            </a>

            <div class="bg-gradient-to-br from-blue-900/50 to-purple-900/50 rounded-2xl p-6 border border-white/10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-2xl -mr-16 -mt-16"></div>
                
                <h4 class="font-bold text-white mb-2 relative z-10"><?= t('dashboard.travel_safe') ?></h4>
                <p class="text-xs text-gray-300 mb-4 relative z-10 leading-relaxed">
                    <?= t('dashboard.travel_safe_desc') ?>
                </p>
                <a href="<?= url('/safety') ?>" class="text-xs text-primary font-bold hover:underline relative z-10"><?= t('dashboard.safety_tips') ?></a>
            </div>
            
            <!-- Mini footer con links  -->
            <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-600 px-2">
                <a href="<?= url('/support') ?>" class="hover:text-gray-400"><?= t('dashboard.help') ?></a>
                <a href="<?= url('/terms') ?>" class="hover:text-gray-400"><?= t('dashboard.terms') ?></a>
                <a href="<?= url('/privacy') ?>" class="hover:text-gray-400"><?= t('dashboard.privacy') ?></a>
                <span>© 2026 Ride4Study</span>
            </div>
        </div>
    </aside>

</div>

<!-- Modal de detalles del viaje -->
<div id="ride-modal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="modal-backdrop"></div>

    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">

            <div class="relative transform overflow-hidden rounded-2xl bg-surface text-left shadow-2xl transition-all duration-300 w-full max-w-[60rem] border border-gray-700/50 opacity-0 translate-y-4 sm:scale-95" id="modal-panel">

                <!-- Header -->
                <div class="relative px-6 py-4 bg-gradient-to-r from-primary/5 via-transparent to-transparent border-b border-gray-700/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                            <i class="fas fa-route text-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white" id="modal-title"><?= t('dashboard.ride_details') ?></h3>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span id="modal-tipo-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold border"></span>
                                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt text-gray-500"></i>
                                    <span id="modal-fecha">—</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-all" onclick="closeRideModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="px-7 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-7">

                        <!-- Columna izquierda (3/5) -->
                        <div class="lg:col-span-3 space-y-6">

                            <!-- Ruta -->
                            <div class="bg-gray-800/30 rounded-xl p-5 border border-gray-700/40">
                                <div class="flex items-stretch gap-5">
                                    <!-- Linea vertical con puntos -->
                                    <div class="flex flex-col items-center pt-1 pb-1">
                                        <div class="w-4 h-4 rounded-full border-[3px] border-primary bg-surface shadow-md shadow-primary/20 shrink-0"></div>
                                        <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/60 to-gray-600 my-1"></div>
                                        <div class="w-4 h-4 rounded-full border-[3px] border-gray-500 bg-surface shrink-0"></div>
                                    </div>
                                    <!-- Ciudades y horas -->
                                    <div class="flex-1 flex flex-col justify-between gap-5">
                                        <!-- Origen -->
                                        <div>
                                            <p class="text-xl font-bold text-white" id="modal-origin"></p>
                                            <p class="text-sm text-primary font-semibold mt-1 flex items-center gap-1.5" id="modal-time-start">
                                                <i class="far fa-clock text-xs"></i>
                                            </p>
                                        </div>
                                        <!-- Destino -->
                                        <div>
                                            <p class="text-xl font-bold text-white" id="modal-dest"></p>
                                            <p class="text-sm text-primary font-semibold mt-1 flex items-center gap-1.5" id="modal-time-end">
                                                <i class="far fa-clock text-xs"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats en linea -->
                            <div class="grid grid-cols-3 gap-3" id="modal-specs">
                                <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 text-center" id="modal-price-container">
                                    <i class="fas fa-euro-sign text-primary text-lg mb-2"></i>
                                    <p class="text-2xl font-bold text-primary" id="modal-price"></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= t('dashboard.price_per_seat') ?></p>
                                </div>
                                <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 text-center">
                                    <i class="fas fa-chair text-blue-400 text-lg mb-2"></i>
                                    <p class="text-2xl font-bold text-white"><span id="modal-seats"></span></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= t('dashboard.seats_available') ?></p>
                                </div>
                                <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/40 text-center" id="modal-return-container" style="display:none;">
                                    <i class="fas fa-undo text-purple-400 text-lg mb-2"></i>
                                    <p class="text-2xl font-bold text-purple-400" id="modal-return-time"></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= t('dashboard.return') ?></p>
                                </div>
                            </div>

                            <!-- Descripcion -->
                            <div>
                                <h5 class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-comment-dots"></i> <?= t('dashboard.ride_comments') ?>
                                </h5>
                                <p class="text-sm text-gray-300 leading-relaxed bg-gray-800/20 p-5 rounded-xl border border-gray-700/30" id="modal-desc"></p>
                            </div>

                            <!-- Preferencias -->
                            <div id="modal-prefs-container" style="display:none;">
                                <h5 class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-sliders-h"></i> <?= t('pref.title') ?>
                                </h5>
                                <div class="flex flex-wrap gap-2" id="modal-prefs"></div>
                            </div>
                        </div>

                        <!-- Columna derecha: Usuario (2/5) -->
                        <div class="lg:col-span-2">
                            <div class="bg-gray-800/30 rounded-xl p-5 border border-gray-700/40 h-full flex flex-col">
                                <div class="text-center mb-4">
                                    <div class="w-20 h-20 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700 ring-2 ring-gray-700/50" id="modal-avatar"></div>
                                    <h4 class="text-lg font-bold text-white" id="modal-driver-name"></h4>
                                    <div class="flex items-center justify-center gap-2 mt-2">
                                        <span class="bg-yellow-500/10 text-yellow-500 px-2.5 py-1 rounded-lg border border-yellow-500/20 flex items-center gap-1 text-xs font-semibold">
                                            <i class="fas fa-star text-[10px]"></i>
                                            <span id="modal-rating"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2 pt-3 border-t border-gray-700/40 flex-1">
                                    <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-gray-800/40">
                                        <i class="fas fa-shield-alt w-4 text-center" id="modal-verified-icon"></i>
                                        <span id="modal-verified" class="text-sm font-medium"></span>
                                    </div>
                                    <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-gray-800/40" id="modal-member-info">
                                        <i class="far fa-calendar w-4 text-center text-gray-500"></i>
                                        <span id="modal-member-since" class="text-sm text-gray-400"></span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-700/40 space-y-2">
                                    <a href="#" id="modal-profile-link" class="w-full flex justify-center items-center gap-2 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-xl py-2.5 text-sm font-medium transition-all">
                                        <i class="fas fa-user text-xs"></i> <?= t('dashboard.view_profile') ?>
                                    </a>
                                    <a href="#" id="btn-contact"
                                       class="w-full flex justify-center items-center gap-2 bg-gray-700/40 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-600/40 rounded-xl py-2.5 text-sm font-medium transition-all">
                                        <i class="fas fa-comment-alt text-xs"></i> <?= t('dashboard.contact') ?>
                                    </a>
                                    <button type="button" id="btn-report"
                                            class="hidden w-full px-3 py-2.5 rounded-xl border border-red-500/20 bg-red-500/5 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-all flex items-center justify-center gap-2"
                                            onclick="reportCurrentRide()">
                                        <i class="fas fa-flag text-xs"></i> Reportar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-7 py-4 border-t border-gray-700/50 bg-gray-800/10 flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-3">
                    <button type="button"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-600 bg-transparent text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-all"
                            onclick="closeRideModal()">
                        <?= t('dashboard.close') ?>
                    </button>
                    <button type="button" id="btn-reserve"
                            class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-ticket-alt"></i> <?= t('dashboard.request_seat') ?>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const modal       = document.getElementById('ride-modal');
    const backdrop    = document.getElementById('modal-backdrop');
    const panel       = document.getElementById('modal-panel');
    const currentUserId = <?= $_SESSION['user_id'] ?>;
    let currentModalRide = null;

    function reportCurrentRide() {
        if (!currentModalRide) return;
        openReportModal('anuncio', {
            idAnuncio: currentModalRide.idAnuncio,
            idUsuario: currentModalRide.idUsuario
        });
    }

    // Clases reutilizables para el botón de acción
    const btnStyles = {
        active:    'w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2',
        disabled:  'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-700 bg-gray-800 text-sm font-bold text-gray-500 cursor-not-allowed flex items-center justify-center gap-2',
        pending:   'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-yellow-500/30 bg-yellow-500/10 text-sm font-bold text-yellow-400 cursor-not-allowed flex items-center justify-center gap-2',
        confirmed: 'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-green-500/30 bg-green-500/10 text-sm font-bold text-green-400 cursor-not-allowed flex items-center justify-center gap-2',
        rejected:  'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-red-500/30 bg-red-500/10 text-sm font-bold text-red-400 cursor-not-allowed flex items-center justify-center gap-2',
    };

    function openRideModal(ride) {
        currentModalRide = ride;
        const btnReserve = document.getElementById('btn-reserve');
        const btnReport  = document.getElementById('btn-report');

        // Mostrar botón de reporte solo para anuncios de otros usuarios
        if (btnReport) btnReport.classList.toggle('hidden', ride.idUsuario == currentUserId);

        // Tipo badge
        const badge = document.getElementById('modal-tipo-badge');
        if (ride.tipo.toLowerCase() === 'ofrezco') {
            badge.textContent = '<?= t('dashboard.driver') ?>';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-primary/10 text-primary border-primary/30';
        } else {
            badge.textContent = '<?= t('dashboard.passenger') ?>';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-purple-500/10 text-purple-400 border-purple-500/30';
        }

        // Fecha
        document.getElementById('modal-fecha').textContent = ride.fechaSalida
            ? new Date(ride.fechaSalida).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
            : '—';

        // Ruta
        document.getElementById('modal-origin').textContent = ride.nombreOrigen;
        document.getElementById('modal-dest').textContent = ride.nombreDestino;

        const timeStartEl = document.getElementById('modal-time-start');
        timeStartEl.innerHTML = '<i class="far fa-clock text-xs"></i> <?= t('dashboard.departure') ?>: ' + ride.horaSalida.substring(0, 5);

        const timeEndEl = document.getElementById('modal-time-end');
        const arrivalTime = ride.horaLlegada ? ride.horaLlegada.substring(0, 5) : '--:--';
        timeEndEl.innerHTML = '<i class="far fa-clock text-xs"></i> <?= t('dashboard.arrival_label') ?>: ' + arrivalTime;

        // Hora de regreso (card separada)
        const returnContainer = document.getElementById('modal-return-container');
        if (ride.horaRegreso) {
            document.getElementById('modal-return-time').textContent = ride.horaRegreso.substring(0, 5);
            returnContainer.style.display = '';
        } else {
            returnContainer.style.display = 'none';
        }
        // Precio y plazas
        const priceEl     = document.getElementById('modal-price');
        const priceContainer = document.getElementById('modal-price-container');
        if (ride.tipo.toLowerCase() === 'ofrezco' && ride.precio != null) {
            priceEl.textContent = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(ride.precio);
            priceContainer.style.display = '';
        } else {
            priceContainer.style.display = 'none';
        }
        document.getElementById('modal-seats').textContent = ride.plazasDisponibles ?? '—';

        // Descripción
        document.getElementById('modal-desc').textContent = ride.descripcion?.trim()
            ? ride.descripcion
            : '<?= t('dashboard.no_comments') ?>';

        // Avatar
        const avatarEl = document.getElementById('modal-avatar');
        if (ride.foto_perfil) {
            avatarEl.innerHTML = `<img src="public/uploads/profiles/${encodeURIComponent(ride.foto_perfil)}" alt="avatar" class="w-full h-full object-cover">`;
            avatarEl.className = 'w-20 h-20 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700 ring-2 ring-gray-700/50';
        } else {
            avatarEl.innerHTML = ride.nombreUsuario.substring(0, 2).toUpperCase();
            avatarEl.className = 'w-20 h-20 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg bg-gradient-to-br from-primary to-primary-dark ring-2 ring-primary/20';
        }

        // Preferencias de viaje
        const prefsContainer = document.getElementById('modal-prefs-container');
        const prefsEl = document.getElementById('modal-prefs');
        const prefIcons = {silencio:'fa-volume-mute',charla:'fa-comments',mascotas:'fa-paw',no_fumar:'fa-smoking-ban',equipaje:'fa-suitcase',musica:'fa-music'};
        const prefColors = {silencio:'blue',charla:'green',mascotas:'yellow',no_fumar:'red',equipaje:'purple',musica:'pink'};
        const prefLabels = <?= json_encode([
            'silencio' => t('pref.silencio'), 'charla' => t('pref.charla'), 'mascotas' => t('pref.mascotas'),
            'no_fumar' => t('pref.no_fumar'), 'equipaje' => t('pref.equipaje'), 'musica' => t('pref.musica')
        ]) ?>;
        let prefs = [];
        try { prefs = JSON.parse(ride.preferencias_viaje || '[]'); } catch(e) {}
        if (prefs.length > 0) {
            prefsEl.innerHTML = prefs.map(p => `<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-${prefColors[p]}-500/10 text-${prefColors[p]}-400 border border-${prefColors[p]}-500/20 text-xs font-medium"><i class="fas ${prefIcons[p]}"></i> ${prefLabels[p] || p}</span>`).join('');
            prefsContainer.style.display = '';
        } else {
            prefsContainer.style.display = 'none';
        }

        // Nombre y rating
        document.getElementById('modal-driver-name').textContent = ride.nombreUsuario;
        document.getElementById('modal-rating').textContent = parseFloat(ride.rating || 0).toFixed(1);

        // Verificación
        const verifiedEl   = document.getElementById('modal-verified');
        const verifiedIcon = document.getElementById('modal-verified-icon');
        if (ride.estado_verificacion == 2) {
            verifiedEl.textContent  = '<?= t('dashboard.verified') ?>';
            verifiedEl.className    = 'text-sm text-green-400';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-green-400';
        } else if (ride.estado_verificacion == 1) {
            verifiedEl.textContent  = '<?= t('dashboard.verification_pending') ?>';
            verifiedEl.className    = 'text-sm text-yellow-400';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-yellow-400';
        } else {
            verifiedEl.textContent  = '<?= t('dashboard.not_verified') ?>';
            verifiedEl.className    = 'text-sm text-gray-500';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-gray-500';
        }

        // Miembro desde (si existe el campo)
        const memberEl = document.getElementById('modal-member-since');
        const memberInfo = document.getElementById('modal-member-info');
        if (ride.fechaRegistro) {
            memberEl.textContent = '<?= t('dashboard.member_since') ?> ' + new Date(ride.fechaRegistro).toLocaleDateString('<?= currentLang() === 'es' ? 'es-ES' : 'en-GB' ?>', { month: 'short', year: 'numeric' });
            memberInfo.style.display = '';
        } else {
            memberInfo.style.display = 'none';
        }

        // Links
        document.getElementById('modal-profile-link').href = '<?= url("/profile") ?>?id=' + ride.idUsuario;
        document.getElementById('btn-contact').href        = '<?= url("/chat") ?>?anuncio_id=' + ride.idAnuncio + '&other_user_id=' + ride.idUsuario;

        // Botón de acción
        btnReserve.onclick   = null;
        btnReserve.disabled  = false;
        btnReserve.style.display = 'flex';

        if (ride.idUsuario == currentUserId) {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.disabled;
            btnReserve.innerHTML  = '<i class="fas fa-user text-xs"></i> <?= t('dashboard.your_ride') ?>';

        } else if (ride.booking_status === 'pendiente') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.pending;
            btnReserve.innerHTML  = '<i class="fas fa-clock text-xs"></i> <?= t('dashboard.pending_request') ?>';

        } else if (ride.booking_status === 'aceptado') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.confirmed;
            btnReserve.innerHTML  = '<i class="fas fa-check text-xs"></i> <?= t('dashboard.seat_confirmed') ?>';

        } else if (ride.booking_status === 'rechazado') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.rejected;
            btnReserve.innerHTML  = '<i class="fas fa-times text-xs"></i> <?= t('dashboard.request_rejected') ?>';

        } else if (ride.plazasDisponibles <= 0) {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.disabled;
            btnReserve.innerHTML  = '<i class="fas fa-ban text-xs"></i> <?= t('dashboard.ride_full') ?>';

        } else if (ride.tipo.toLowerCase() === 'ofrezco') {
            btnReserve.className  = btnStyles.active;
            btnReserve.innerHTML  = '<i class="fas fa-ticket-alt text-xs"></i> <?= t('dashboard.request_seat') ?>';
            btnReserve.onclick    = () => { window.location.href = '<?= url("/reserve") ?>?ride_id=' + ride.idAnuncio; };

        } else {
            // Tipo "busco" — solo contactar
            btnReserve.style.display = 'none';
        }

        // Mostrar modal con animación
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        });
    }

    function closeRideModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // Cerrar al hacer click en el fondo
    backdrop.addEventListener('click', closeRideModal);

    // Cerrar con Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRideModal(); });

    // Eventos en las tarjetas
    document.querySelectorAll('.view-ride-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            openRideModal(JSON.parse(btn.getAttribute('data-ride')));
        });
    });

    // Autocompletado básico usando Nominatim de OpenStreetMap
    class FilterAutocomplete {
        constructor(inputId) {
            this.input    = document.getElementById(inputId);
            this.dropdown = document.getElementById(inputId + '-dropdown');
            this.timer    = null;
            if (!this.input || !this.dropdown) return;
            this._bind();
        }

        _bind() {
            this.input.addEventListener('input', () => {
                clearTimeout(this.timer);
                const q = this.input.value.trim();
                if (q.length < 2) { this._hide(); return; }
                this.timer = setTimeout(() => this._search(q), 300);
            });
            this.input.addEventListener('blur', () => setTimeout(() => this._hide(), 200));
            this.input.addEventListener('keydown', (e) => {
                if (this.dropdown.classList.contains('hidden')) return;
                const items = this.dropdown.querySelectorAll('[data-name]');
                const active = this.dropdown.querySelector('.bg-gray-700');
                let idx = Array.from(items).indexOf(active);
                if (e.key === 'ArrowDown') { e.preventDefault(); if (active) active.classList.remove('bg-gray-700'); idx = (idx + 1) % items.length; items[idx].classList.add('bg-gray-700'); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (active) active.classList.remove('bg-gray-700'); idx = idx <= 0 ? items.length - 1 : idx - 1; items[idx].classList.add('bg-gray-700'); }
                else if (e.key === 'Enter' && active) { e.preventDefault(); active.click(); }
                else if (e.key === 'Escape') { this._hide(); }
            });
        }

        async _search(query) {
            const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
                format: 'json', q: query, countrycodes: 'es', limit: '5', addressdetails: '1', 'accept-language': 'es'
            });
            try {
                const res = await fetch(url, { headers: { 'User-Agent': 'Ride4Study/1.0' } });
                const data = await res.json();
                this._render(data);
            } catch (err) { this._hide(); }
        }

        _render(results) {
            this.dropdown.innerHTML = '';
            if (!results.length) {
                this.dropdown.innerHTML = '<div class="px-4 py-2 text-xs text-gray-500">Sin resultados</div>';
                this.dropdown.classList.remove('hidden');
                return;
            }
            results.forEach(place => {
                const addr = place.address || {};
                const name = addr.city || addr.town || addr.village || addr.municipality || addr.hamlet || place.name || place.display_name.split(',')[0];
                const sub = [addr.province, addr.state].filter((v, i, a) => v && a.indexOf(v) === i).join(', ');
                const item = document.createElement('div');
                item.dataset.name = name;
                item.className = 'px-4 py-2.5 cursor-pointer hover:bg-gray-700 transition-colors border-b border-gray-700/50 last:border-0';
                item.innerHTML = `<span class="text-sm text-white">${name}</span>${sub ? ` <span class="text-xs text-gray-500">- ${sub}</span>` : ''}`;
                item.addEventListener('mousedown', (e) => { e.preventDefault(); this.input.value = name; this._hide(); });
                this.dropdown.appendChild(item);
            });
            this.dropdown.classList.remove('hidden');
        }

        _hide() { this.dropdown.classList.add('hidden'); this.dropdown.innerHTML = ''; }
    }

    new FilterAutocomplete('filter-origen');
    new FilterAutocomplete('filter-destino');
</script>
</body>
</html>