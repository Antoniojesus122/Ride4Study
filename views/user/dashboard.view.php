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
                <a href="#" class="text-xs text-primary font-bold hover:underline relative z-10">Ver consejos de seguridad &rarr;</a>
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
    <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Panel del modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-[#1F2937] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-700 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel">
                
                <!-- Header del modal -->
                <div class="bg-gray-800/50 px-4 py-3 sm:px-6 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">Detalles del Viaje</h3>
                    <button type="button" class="text-gray-400 hover:text-white transition-colors" onclick="closeRideModal()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Informacion del viaje -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Linea de ruta -->
                            <div class="relative pl-8 border-l-2 border-gray-700 space-y-8">
                                <div class="relative">
                                    <div class="absolute -left-[39px] top-1 h-5 w-5 rounded-full border-4 border-[#1F2937] bg-primary"></div>
                                    <h4 class="text-lg font-bold text-white" id="modal-origin">Madrigal de la Vera</h4>
                                    <p class="text-sm text-primary font-mono" id="modal-time-start">17:00</p>
                                </div>
                                <div class="relative">
                                    <div class="absolute -left-[39px] top-1 h-5 w-5 rounded-full border-4 border-[#1F2937] bg-white"></div>
                                    <h4 class="text-lg font-bold text-white" id="modal-dest">Móstoles</h4>
                                    <p class="text-sm text-gray-400 font-mono" id="modal-time-end">19:10</p>
                                </div>
                            </div>

                            <!-- Especificaciones -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50" id="modal-price-container">
                                    <p class="text-xs text-gray-400 mb-1">Precio por plaza</p>
                                    <p class="text-xl font-bold text-primary" id="modal-price">14,50 €</p>
                                </div>
                                <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50" id="modal-seats-container">
                                    <p class="text-xs text-gray-400 mb-1">Plazas disponibles</p>
                                    <p class="text-xl font-bold text-white flex items-center gap-2">
                                        <span id="modal-seats">2</span> <i class="fas fa-chair text-sm text-gray-500"></i>
                                    </p>
                                </div>
                            </div>

                            <!-- Descripcion -->
                            <div>
                                <h5 class="text-sm font-semibold text-gray-300 mb-2">Comentarios del viaje</h5>
                                <p class="text-sm text-gray-400 leading-relaxed bg-gray-800/30 p-4 rounded-xl border border-gray-700/30 italic" id="modal-desc">
                                    Sin descripción.
                                </p>
                            </div>
                        </div>

                        <!-- Informacion del conductor o solicitante -->
                        <div class="md:col-span-1">
                            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 h-full">
                                <div class="text-center mb-4">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-blue-600 mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg shadow-primary/20" id="modal-avatar">
                                        JD
                                    </div>
                                    <h4 class="font-bold text-white truncate" id="modal-driver-name">Juan David</h4>
                                    <div class="flex items-center justify-center gap-2 mt-1 text-sm">
                                        <span class="bg-yellow-500/10 text-yellow-500 px-2 py-0.5 rounded-full border border-yellow-500/20 flex items-center gap-1">
                                            <i class="fas fa-star text-xs"></i> <span id="modal-rating">0.0</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-3 pt-4 border-t border-gray-700">
                                    <div class="flex items-center gap-3 text-sm text-gray-300">
                                        <i class="fas fa-shield-alt w-5 text-center text-green-400"></i>
                                        <span id="modal-verified">Verificado</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-gray-300">
                                        <i class="fas fa-user-clock w-5 text-center text-blue-400"></i>
                                        <span>Miembro veterano</span>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <a href="#" id="modal-profile-link" class="w-full flex justify-center bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-lg py-2 text-sm font-medium transition-all">
                                        Ver perfil completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer del modal -->
                <div class="bg-gray-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-700 gap-2">
                    <button type="button" id="btn-reserve" class="w-full inline-flex justify-center rounded-xl border border-transparent bg-primary px-4 py-2 text-base font-bold text-secondary shadow-sm hover:bg-primary-dark sm:ml-3 sm:w-auto sm:text-sm transition-all shadow-primary/20 hover:shadow-primary/40">
                        Solicitar plaza
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-600 bg-transparent px-4 py-2 text-base font-medium text-gray-300 shadow-sm hover:bg-gray-800 hover:text-white sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all" onclick="closeRideModal()">
                        Cerrar
                    </button>
                    <a href="#" id="btn-contact" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-600 bg-transparent px-4 py-2 text-base font-medium text-gray-300 shadow-sm hover:bg-gray-800 hover:text-white sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all text-center items-center">
                        Contactar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('ride-modal');
    const backdrop = document.getElementById('modal-backdrop');
    const panel = document.getElementById('modal-panel');
    const currentUserId = <?= $_SESSION['user_id'] ?>;

    function openRideModal(ride) {
        // Datos del viaje
        document.getElementById('modal-origin').textContent = ride.nombreOrigen;
        document.getElementById('modal-dest').textContent = ride.nombreDestino;
        document.getElementById('modal-time-start').textContent = ride.horaSalida.substring(0, 5);
        document.getElementById('modal-time-end').textContent = ride.horaRegreso ? 'Regreso: ' + ride.horaRegreso.substring(0, 5) : '—'; 
        document.getElementById('modal-price').textContent = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(ride.precio);
        document.getElementById('modal-seats').textContent = ride.plazasDisponibles;
        document.getElementById('modal-desc').textContent = ride.descripcion || 'Sin descripción adicional.';
        
        // Datos del usuario
        document.getElementById('modal-driver-name').textContent = ride.nombreUsuario;
        if (ride.foto_perfil) {
            document.getElementById('modal-avatar').innerHTML = '<img src="public/uploads/profiles/' + encodeURIComponent(ride.foto_perfil) + '?v=' + Date.now() + '" alt="avatar" class="w-full h-full object-cover rounded-full">';
        } else {
            document.getElementById('modal-avatar').textContent = ride.nombreUsuario.substring(0, 2).toUpperCase();
        }
        document.getElementById('modal-profile-link').href = 'profile.php?id=' + ride.idUsuario;
        document.getElementById('btn-contact').href = 'chat.php?anuncio_id=' + ride.idAnuncio + '&other_user_id=' + ride.idUsuario;
        
        // Valoración
        const rating = parseFloat(ride.rating || 0).toFixed(1);
        document.getElementById('modal-rating').textContent = rating;
        
        // Verificación
           const verifiedEl = document.getElementById('modal-verified');
           // 2 = verificado, 1 = pendiente, 0 = no verificado
           if (ride.estado_verificacion == 2) {
               verifiedEl.textContent = 'Verificado';
               verifiedEl.className = 'text-green-400';
               verifiedEl.previousElementSibling.className = 'fas fa-shield-alt w-5 text-center text-green-400';
           } else if (ride.estado_verificacion == 1) {
               verifiedEl.textContent = 'Pendiente';
               verifiedEl.className = 'text-yellow-400';
               verifiedEl.previousElementSibling.className = 'fas fa-shield-alt w-5 text-center text-yellow-400';
           } else {
               verifiedEl.textContent = 'No verificado';
               verifiedEl.className = 'text-gray-500';
               verifiedEl.previousElementSibling.className = 'fas fa-shield-alt w-5 text-center text-gray-500';
           }

        // Lógica para reserva
        const btnReserve = document.getElementById('btn-reserve');
        btnReserve.onclick = null;
        btnReserve.style.display = 'inline-flex';
        
        // Resetear estilos y estados
        btnReserve.disabled = false;
        btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-transparent bg-primary px-4 py-2 text-base font-bold text-secondary shadow-sm hover:bg-primary-dark sm:ml-3 sm:w-auto sm:text-sm transition-all shadow-primary/20 hover:shadow-primary/40";
        btnReserve.textContent = 'Solicitar plaza';

        // Esconder viajes propios del usuario
        if (ride.idUsuario == currentUserId) {
            btnReserve.disabled = true;
            btnReserve.textContent = 'Tu viaje';
            btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-gray-600 bg-gray-800 px-4 py-2 text-base font-bold text-gray-500 cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm";
        }
        // Verificar si el usuario ya ha reservado el viaje que está viendo
        else if (ride.booking_status) {
            btnReserve.disabled = true;
            if (ride.booking_status === 'pendiente') {
                btnReserve.textContent = 'Solicitud Pendiente';
                btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-yellow-500/20 bg-yellow-500/10 px-4 py-2 text-base font-bold text-yellow-500 cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm";
            } else if (ride.booking_status === 'aceptado') {
                btnReserve.textContent = 'Plaza Confirmada';
                btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-2 text-base font-bold text-green-500 cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm";
            } else {
                btnReserve.textContent = 'Rechazado';
                btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-2 text-base font-bold text-red-500 cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm";
            }
        }
        // Verificar si no hay plazas disponibles en ese viaje
        else if (ride.plazasDisponibles <= 0) {
            btnReserve.disabled = true;
            btnReserve.textContent = 'Completo';
            btnReserve.className = "w-full inline-flex justify-center rounded-xl border border-gray-600 bg-gray-800 px-4 py-2 text-base font-bold text-gray-500 cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm";
        }
        // Configurar botón según tipo de anuncio
        else if (ride.tipo.toLowerCase() === 'ofrezco') {
            // TIPO OFREZCO: Usuario puede reservar una plaza directamente
            btnReserve.innerHTML = '<i class="fas fa-ticket-alt mr-2"></i>Reservar Plaza';
            btnReserve.className = 'bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition flex items-center justify-center shadow-lg';
            btnReserve.onclick = function() {
                window.location.href = "reserve.php?ride_id=" + ride.idAnuncio;
            };
        }
        else if (ride.tipo.toLowerCase() === 'busco') {
            // TIPO BUSCO: Solo se puede contactar por chat, no reservar directamente
            btnReserve.style.display = 'none';
        }
        else {
            // Tipo desconocido
            btnReserve.style.display = 'none';
        }

        // Mostrar modal
        modal.classList.remove('hidden');
        // Animaciones de entrada
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    }

    function closeRideModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Eventos para los botones
    document.querySelectorAll('.view-ride-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const rideData = JSON.parse(btn.getAttribute('data-ride'));
            openRideModal(rideData);
        });
    });
</script>
</body>
</html>