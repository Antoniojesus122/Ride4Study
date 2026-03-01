<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold leading-tight text-white">Explorar Viajes</h2>
            <p class="mt-2 text-gray-400">Encuentra compañeros para tu próximo trayecto.</p>
        </div>  

        <div class="flex flex-col md:flex-row items-start gap-8">
            <div class="flex-1 w-full">
                <div class="bg-surface rounded-2xl p-6 mb-8 border border-gray-700 shadow-lg">
                <h3 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-filter text-primary"></i> Filtros de búsqueda
                </h3>
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <!-- Tipo -->
                <div class="md:col-span-2">
                     <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1">Tipo</label>
                     <div class="relative group">
                        <select name="tipo" class="appearance-none block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-3 pr-8 text-white focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none cursor-pointer">
                            <option value="">Todos</option>
                            <option value="Ofrezco" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Ofrezco') ? 'selected' : '' ?>>Conductor</option>
                            <option value="Busco" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Busco') ? 'selected' : '' ?>>Pasajero</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                             <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                     </div>
                </div>

                <!-- Origen -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1">Origen</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-500 group-focus-within:text-primary transition-colors"></i>
                        </div>
                        <input type="text" name="origen" value="<?= htmlspecialchars($_GET['origen'] ?? '') ?>" 
                            class="block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none" 
                            placeholder="Ciudad de salida">
                    </div>
                </div>

                <!-- Destino -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1">Destino</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-flag-checkered text-gray-500 group-focus-within:text-primary transition-colors"></i>
                        </div>
                        <input type="text" name="destino" value="<?= htmlspecialchars($_GET['destino'] ?? '') ?>" 
                            class="block w-full rounded-xl border border-gray-600 bg-gray-800 py-3 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm outline-none" 
                            placeholder="Ciudad de destino">
                    </div>
                </div>

                <!-- Fecha -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 ml-1">Fecha</label>
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
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="dashboard.php" class="flex items-center justify-center px-3 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl transition-all border border-gray-600 group" title="Limpiar filtros">
                        <i class="fas fa-times group-hover:rotate-90 transition-transform duration-300"></i>
                    </a>
                </div>
            </form>
            
            <!-- Tags de filtros rápidos-->
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-xs text-gray-500 mr-2 self-center">Filtros rápidos:</span>
                <a href="?fecha=<?= date('Y-m-d') ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-full text-xs text-gray-300 transition-colors">Hoy</a>
                <a href="?fecha=<?= date('Y-m-d', strtotime('+1 day')) ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-full text-xs text-gray-300 transition-colors">Mañana</a>
            </div>
        </div>

        <!-- Resultados -->
        <div class="space-y-4">
             <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-400">Mostrando <strong><?= count($rides) ?></strong> resultados disponibles</p>
             </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 <?php if (empty($rides)): ?>
                    <div class="col-span-full py-16 text-center border-2 border-dashed border-gray-700 rounded-2xl bg-surface/30">
                        <div class="w-20 h-20 bg-surface rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                            <i class="fas fa-search-location text-3xl text-gray-500"></i>
                        </div>
                        <h3 class="text-lg font-medium text-white">No hemos encontrado viajes</h3>
                        <p class="text-gray-400 mt-2 max-w-sm mx-auto">Parece que no hay viajes que coincidan con tus filtros. ¡Prueba a cambiar la fecha!</p>
                        <a href="dashboard.php" class="inline-block mt-4 text-primary font-semibold hover:underline">Limpiar filtros</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($rides as $ride): ?>
                        <div class="group relative bg-surface rounded-2xl p-5 border border-gray-700 hover:border-primary/50 transition-all duration-300 shadow-md hover:shadow-xl hover:shadow-primary/5">
                            <!-- Precio -->
                            <div class="absolute top-0 right-0 bg-gray-800 rounded-bl-2xl rounded-tr-xl px-4 py-2 border-b border-l border-gray-700 flex flex-col items-end">
                                <span class="text-xs font-bold uppercase tracking-wider mb-0.5 <?= $ride['tipo'] == 'ofrezco' ? 'text-primary' : 'text-purple-400' ?>">
                                    <?= $ride['tipo'] == 'ofrezco' ? 'Conductor' : 'Pasajero' ?>
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
                                    <h4 class="text-sm font-bold text-white"><?= htmlspecialchars($ride['nombreUsuario']) ?></h4>
                                    <div class="flex items-center text-xs gap-2">
                                        <span class="text-gray-400"><i class="fas fa-star text-yellow-500 mr-1"></i> <?= number_format((float)($ride['rating'] ?? 0), 1) ?></span>
                                        <span class="text-gray-600">•</span>
                                        <?php if ($ride['estado_verificacion'] == 2): ?>
                                            <span class="text-green-400">Verificado</span>
                                        <?php elseif ($ride['estado_verificacion'] == 1): ?>
                                            <span class="text-yellow-400">Pendiente</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">No verificado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Visual de la ruta -->
                            <div class="relative py-4">
                                <!-- Línea de la ruta -->
                                <div class="absolute left-[7px] top-6 bottom-6 w-0.5 bg-gray-700"></div>
                                
                                <div class="flex items-start mb-6 relative">
                                    <div class="w-4 h-4 rounded-full border-2 border-primary bg-gray-900 z-10 shrink-0 mt-1"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-semibold text-white"><?= htmlspecialchars($ride['nombreOrigen']) ?></p>
                                        <p class="text-xs text-primary font-mono mt-0.5"><?= substr($ride['horaSalida'], 0, 5) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start relative">
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-500 bg-gray-900 z-10 shrink-0 mt-1"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-semibold text-white"><?= htmlspecialchars($ride['nombreDestino']) ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5">Llegada aprox.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de la fecha, plazos y boton de acción -->
                            <div class="mt-4 pt-4 border-t border-gray-700/50 flex justify-between items-center">
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                     <span class="flex items-center" title="<?= date('d/m/Y', strtotime($ride['fechaSalida'])) ?>">
                                        <i class="far fa-calendar text-gray-500 mr-2"></i>
                                        <?= date('d M', strtotime($ride['fechaSalida'])) ?>
                                     </span>
                                     <span class="flex items-center" title="Plazas disponibles">
                                        <i class="fas fa-chair text-gray-500 mr-2"></i>
                                        <?= $ride['plazasDisponibles'] ?>
                                     </span>
                                </div>
                                <button type="button" class="view-ride-btn text-sm font-medium text-white hover:text-primary transition-colors relative z-20"
                                        data-ride='<?= htmlspecialchars(json_encode($ride), ENT_QUOTES, 'UTF-8') ?>'>
                                    Ver detalle <i class="fas fa-arrow-right ml-1 text-xs"></i>
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
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>&origen=<?= urlencode($_GET['origen'] ?? '') ?>&destino=<?= urlencode($_GET['destino'] ?? '') ?>&fecha=<?= urlencode($_GET['fecha'] ?? '') ?>" class="p-2 w-10 h-10 flex items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&origen=<?= urlencode($_GET['origen'] ?? '') ?>&destino=<?= urlencode($_GET['destino'] ?? '') ?>&fecha=<?= urlencode($_GET['fecha'] ?? '') ?>" 
                    class="w-10 h-10 flex items-center justify-center rounded-lg border <?= $i === $currentPage ? 'bg-primary border-primary text-secondary font-bold' : 'border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white' ?> transition-colors">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>&origen=<?= urlencode($_GET['origen'] ?? '') ?>&destino=<?= urlencode($_GET['destino'] ?? '') ?>&fecha=<?= urlencode($_GET['fecha'] ?? '') ?>" class="p-2 w-10 h-10 flex items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Barra lateral -->
    <aside class="w-full md:w-80 px-6 hidden md:block shrink-0">
        <div class="sticky top-6 space-y-6">
             <!-- Alertas -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php 
                    switch($_GET['error']) {
                        case 'own_ride': echo 'No puedes reservar tu propio viaje.'; break;
                        case 'already_booked': echo 'Ya has solicitado plaza en este viaje.'; break;
                        case 'no_seats': echo 'No quedan plazas disponibles.'; break;
                        case 'reservation_failed': echo 'Error al realizar la reserva.'; break;
                        case 'invalid_type': echo 'Este tipo de anuncio no admite reservas directas. Contacta con el usuario.'; break;
                        default: echo 'Ha ocurrido un error.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-surface rounded-2xl p-6 border border-gray-700 shadow-xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="font-bold text-white">Acciones Rápidas</h3>
                </div>
                
                <nav class="space-y-3">
                    <a href="publish.php" class="flex items-center justify-between p-3 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark transition-all group">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-plus-circle"></i> Publicar viaje
                        </span>
                        <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                    
                    <a href="my-rides.php?tab=bookings" class="flex items-center justify-between p-3 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-750 border border-gray-700 transition-all">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-network-wired text-gray-500"></i> Mis reservas
                        </span>
                    </a>
                     <a href="profile.php" class="flex items-center justify-between p-3 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-750 border border-gray-700 transition-all">
                        <span class="flex items-center gap-3">
                            <i class="fas fa-user-edit text-gray-500"></i> Editar perfil
                        </span>
                    </a>
                </nav>
            </div>

            <div class="bg-gradient-to-br from-blue-900/50 to-purple-900/50 rounded-2xl p-6 border border-white/10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-2xl -mr-16 -mt-16"></div>
                
                <h4 class="font-bold text-white mb-2 relative z-10">Viaja seguro</h4>
                <p class="text-xs text-gray-300 mb-4 relative z-10 leading-relaxed">
                    Recuerda verificar siempre las valoraciones de tus compañeros antes de reservar.
                </p>
                <a href="safety.php" class="text-xs text-primary font-bold hover:underline relative z-10">Ver consejos de seguridad &rarr;</a>
            </div>
            
            <!-- Mini footer con links  -->
            <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-600 px-2">
                <a href="support.php" class="hover:text-gray-400">Ayuda</a>
                <a href="terms.php" class="hover:text-gray-400">Términos</a>
                <a href="privacy.php" class="hover:text-gray-400">Privacidad</a>
                <span>© 2026 Ride4Study</span>
            </div>
        </div>
    </aside>

</div>

<!-- Modal de detalles del viaje -->
<div id="ride-modal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Fondo oscuro -->
    <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="modal-backdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div class="relative transform overflow-hidden rounded-2xl bg-surface text-left shadow-2xl transition-all duration-300 sm:my-8 sm:w-full sm:max-w-2xl border border-gray-700 opacity-0 translate-y-4 sm:scale-95" id="modal-panel">

                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <i class="fas fa-route text-primary text-sm"></i>
                        </div>
                        <h3 class="text-base font-semibold text-white" id="modal-title">Detalles del Viaje</h3>
                    </div>
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-all" onclick="closeRideModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="px-5 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Info del viaje -->
                        <div class="md:col-span-2 space-y-5">

                            <!-- Tipo badge + fecha -->
                            <div class="flex items-center justify-between">
                                <span id="modal-tipo-badge" class="px-3 py-1 rounded-full text-xs font-semibold border"></span>
                                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt text-gray-500"></i>
                                    <span id="modal-fecha">—</span>
                                </span>
                            </div>

                            <!-- Ruta -->
                            <div class="relative pl-7 space-y-5">
                                <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-gray-700"></div>

                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-primary bg-surface z-10"></div>
                                    <div>
                                        <p class="text-base font-bold text-white" id="modal-origin"></p>
                                        <p class="text-xs text-primary font-mono mt-0.5" id="modal-time-start"></p>
                                    </div>
                                </div>

                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-gray-500 bg-surface z-10"></div>
                                    <div>
                                        <p class="text-base font-bold text-white" id="modal-dest"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" id="modal-time-end"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Precio y plazas -->
                            <div class="grid grid-cols-2 gap-3" id="modal-specs">
                                <div class="bg-gray-800 rounded-xl p-3.5 border border-gray-700" id="modal-price-container">
                                    <p class="text-xs text-gray-400 mb-1">Precio por plaza</p>
                                    <p class="text-xl font-bold text-primary" id="modal-price"></p>
                                </div>
                                <div class="bg-gray-800 rounded-xl p-3.5 border border-gray-700">
                                    <p class="text-xs text-gray-400 mb-1">Plazas disponibles</p>
                                    <p class="text-xl font-bold text-white flex items-center gap-2">
                                        <span id="modal-seats"></span>
                                        <i class="fas fa-chair text-sm text-gray-500"></i>
                                    </p>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Comentarios del viaje</h5>
                                <p class="text-sm text-gray-400 leading-relaxed bg-gray-800/50 p-4 rounded-xl border border-gray-700/50 italic" id="modal-desc"></p>
                            </div>
                        </div>

                        <!-- Info del usuario -->
                        <div class="md:col-span-1">
                            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 h-full flex flex-col">
                                <div class="text-center mb-4">
                                    <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700" id="modal-avatar"></div>
                                    <h4 class="font-bold text-white truncate" id="modal-driver-name"></h4>
                                    <div class="flex items-center justify-center mt-2">
                                        <span class="bg-yellow-500/10 text-yellow-500 px-2.5 py-1 rounded-full border border-yellow-500/20 flex items-center gap-1.5 text-xs font-semibold">
                                            <i class="fas fa-star text-xs"></i>
                                            <span id="modal-rating"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2.5 pt-4 border-t border-gray-700 flex-1">
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <i class="fas fa-shield-alt w-4 text-center" id="modal-verified-icon"></i>
                                        <span id="modal-verified" class="text-sm"></span>
                                    </div>
                                    <div class="flex items-center gap-2.5 text-sm text-gray-400" id="modal-member-info">
                                        <i class="far fa-calendar w-4 text-center text-gray-500"></i>
                                        <span id="modal-member-since"></span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-700">
                                    <a href="#" id="modal-profile-link" class="w-full flex justify-center items-center gap-2 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-lg py-2 text-sm font-medium transition-all">
                                        <i class="fas fa-user text-xs"></i> Ver perfil
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="px-5 py-4 sm:px-6 border-t border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-600 bg-transparent text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-all"
                            onclick="closeRideModal()">
                        Cerrar
                    </button>
                    <a href="#" id="btn-contact"
                       class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-600 bg-gray-800 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-comment-alt text-xs"></i> Contactar
                    </a>
                    <button type="button" id="btn-reserve"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-ticket-alt text-xs"></i> Solicitar Plaza
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

    // Clases reutilizables para el botón de acción
    const btnStyles = {
        active:    'w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2',
        disabled:  'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-700 bg-gray-800 text-sm font-bold text-gray-500 cursor-not-allowed flex items-center justify-center gap-2',
        pending:   'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-yellow-500/30 bg-yellow-500/10 text-sm font-bold text-yellow-400 cursor-not-allowed flex items-center justify-center gap-2',
        confirmed: 'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-green-500/30 bg-green-500/10 text-sm font-bold text-green-400 cursor-not-allowed flex items-center justify-center gap-2',
        rejected:  'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-red-500/30 bg-red-500/10 text-sm font-bold text-red-400 cursor-not-allowed flex items-center justify-center gap-2',
    };

    function openRideModal(ride) {
        const btnReserve = document.getElementById('btn-reserve');

        // — Tipo badge —
        const badge = document.getElementById('modal-tipo-badge');
        if (ride.tipo.toLowerCase() === 'ofrezco') {
            badge.textContent = 'Conductor';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-primary/10 text-primary border-primary/30';
        } else {
            badge.textContent = 'Pasajero';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-purple-500/10 text-purple-400 border-purple-500/30';
        }

        // — Fecha —
        document.getElementById('modal-fecha').textContent = ride.fechaSalida
            ? new Date(ride.fechaSalida).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
            : '—';

        // — Ruta —
        document.getElementById('modal-origin').textContent    = ride.nombreOrigen;
        document.getElementById('modal-dest').textContent      = ride.nombreDestino;
        document.getElementById('modal-time-start').textContent = ride.horaSalida.substring(0, 5);
        document.getElementById('modal-time-end').textContent  = ride.horaRegreso
            ? 'Regreso: ' + ride.horaRegreso.substring(0, 5)
            : 'Llegada aprox.';

        // — Precio y plazas —
        const priceEl     = document.getElementById('modal-price');
        const priceContainer = document.getElementById('modal-price-container');
        if (ride.tipo.toLowerCase() === 'ofrezco' && ride.precio != null) {
            priceEl.textContent = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(ride.precio);
            priceContainer.style.display = '';
        } else {
            priceContainer.style.display = 'none';
        }
        document.getElementById('modal-seats').textContent = ride.plazasDisponibles ?? '—';

        // — Descripción —
        document.getElementById('modal-desc').textContent = ride.descripcion?.trim()
            ? ride.descripcion
            : 'Este usuario no ha añadido comentarios sobre el viaje.';

        // — Avatar —
        const avatarEl = document.getElementById('modal-avatar');
        if (ride.foto_perfil) {
            avatarEl.innerHTML = `<img src="public/uploads/profiles/${encodeURIComponent(ride.foto_perfil)}" alt="avatar" class="w-full h-full object-cover">`;
        } else {
            avatarEl.innerHTML = ride.nombreUsuario.substring(0, 2).toUpperCase();
            avatarEl.className = 'w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg bg-gradient-to-br from-primary to-primary-dark';
        }

        // — Nombre y rating —
        document.getElementById('modal-driver-name').textContent = ride.nombreUsuario;
        document.getElementById('modal-rating').textContent = parseFloat(ride.rating || 0).toFixed(1);

        // — Verificación —
        const verifiedEl   = document.getElementById('modal-verified');
        const verifiedIcon = document.getElementById('modal-verified-icon');
        if (ride.estado_verificacion == 2) {
            verifiedEl.textContent  = 'Verificado';
            verifiedEl.className    = 'text-sm text-green-400';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-green-400';
        } else if (ride.estado_verificacion == 1) {
            verifiedEl.textContent  = 'Verificación pendiente';
            verifiedEl.className    = 'text-sm text-yellow-400';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-yellow-400';
        } else {
            verifiedEl.textContent  = 'No verificado';
            verifiedEl.className    = 'text-sm text-gray-500';
            verifiedIcon.className  = 'fas fa-shield-alt w-4 text-center text-gray-500';
        }

        // — Miembro desde (si existe el campo) —
        const memberEl = document.getElementById('modal-member-since');
        const memberInfo = document.getElementById('modal-member-info');
        if (ride.fechaRegistro) {
            memberEl.textContent = 'Miembro desde ' + new Date(ride.fechaRegistro).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
            memberInfo.style.display = '';
        } else {
            memberInfo.style.display = 'none';
        }

        // — Links —
        document.getElementById('modal-profile-link').href = 'profile.php?id=' + ride.idUsuario;
        document.getElementById('btn-contact').href        = 'messages.php?anuncio_id=' + ride.idAnuncio + '&other_user_id=' + ride.idUsuario;

        // — Botón de acción —
        btnReserve.onclick   = null;
        btnReserve.disabled  = false;
        btnReserve.style.display = 'flex';

        if (ride.idUsuario == currentUserId) {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.disabled;
            btnReserve.innerHTML  = '<i class="fas fa-user text-xs"></i> Tu anuncio';

        } else if (ride.booking_status === 'pendiente') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.pending;
            btnReserve.innerHTML  = '<i class="fas fa-clock text-xs"></i> Solicitud pendiente';

        } else if (ride.booking_status === 'aceptado') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.confirmed;
            btnReserve.innerHTML  = '<i class="fas fa-check text-xs"></i> Plaza confirmada';

        } else if (ride.booking_status === 'rechazado') {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.rejected;
            btnReserve.innerHTML  = '<i class="fas fa-times text-xs"></i> Solicitud rechazada';

        } else if (ride.plazasDisponibles <= 0) {
            btnReserve.disabled   = true;
            btnReserve.className  = btnStyles.disabled;
            btnReserve.innerHTML  = '<i class="fas fa-ban text-xs"></i> Viaje completo';

        } else if (ride.tipo.toLowerCase() === 'ofrezco') {
            btnReserve.className  = btnStyles.active;
            btnReserve.innerHTML  = '<i class="fas fa-ticket-alt text-xs"></i> Solicitar Plaza';
            btnReserve.onclick    = () => { window.location.href = 'reserve.php?ride_id=' + ride.idAnuncio; };

        } else {
            // Tipo "busco" — solo contactar
            btnReserve.style.display = 'none';
        }

        // — Mostrar modal con animación —
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
</script>
</body>
</html>