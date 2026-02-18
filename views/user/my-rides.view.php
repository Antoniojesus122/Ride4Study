<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white">Mis Viajes</h2>
            <p class="text-gray-400 mt-2">Gestiona tus anuncios, revisa quién se ha unido y mantén tu historial organizado.</p>
        </div>
        <a href="publish.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-secondary bg-primary hover:bg-primary-dark transition-all transform hover:-translate-y-0.5 shadow-lg shadow-primary/20">
            <i class="fas fa-plus-circle mr-2"></i> Nuevo viaje
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <div class="font-medium">
                <?php if ($_GET['success'] == 'created'): ?>
                    ¡Viaje publicado correctamente!
                <?php elseif ($_GET['success'] == 'reserved'): ?>
                    ¡Solicitud de reserva enviada! Puedes ver el estado aquí.
                <?php elseif ($_GET['success'] == 'status_updated'): ?>
                    Estado de la reserva actualizado correctamente.
                <?php elseif ($_GET['success'] == 'updated'): ?>
                     ¡Viaje actualizado correctamente!
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    ¡Viaje eliminado correctamente!
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl flex items-center gap-3">
             <i class="fas fa-exclamation-circle text-xl"></i>
             <div class="font-medium">
                <?php if ($_GET['error'] == 'update_failed'): ?>
                    No se pudo actualizar el estado. Inténtalo de nuevo.
                <?php elseif ($_GET['error'] == 'unauthorized'): ?>
                    No tienes permiso para realizar esta acción.
                <?php else: ?>
                    Ha ocurrido un error inesperado.
                <?php endif; ?>
             </div>
        </div>
    <?php endif; ?>

    <!-- Pestañas de los anuncios -->
    <div class="mb-8 border-b border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button onclick="switchTab('active')" id="tab-active" class="border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-route"></i> Viajes Activos 
                <span class="bg-gray-800 text-gray-300 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($activeRides) ?></span>
            </button>
            <button onclick="switchTab('past')" id="tab-past" class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-history"></i> Historial
                <span class="bg-gray-800 text-gray-500 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($pastRides) ?></span>
            </button>
            <button onclick="switchTab('bookings')" id="tab-bookings" class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-ticket-alt"></i> Mis Reservas
                <span class="bg-gray-800 text-gray-500 py-0.5 px-2 rounded-full text-xs ml-1"><?= count($activeBookings) ?></span>
            </button>
        </nav>
    </div>

    <!-- Sección de viajes activos -->
    <div id="content-active" class="space-y-6">
        <?php if (empty($activeRides)): ?>
            <div class="text-center py-20 bg-surface/30 rounded-3xl border border-dashed border-gray-700">
                <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-car-side text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2">No tienes viajes activos</h3>
                <p class="text-gray-400 mb-6">¿Vas a realizar un trayecto pronto? ¡Compártelo!</p>
                <a href="publish.php" class="text-primary font-semibold hover:underline">Publicar un viaje ahora</a>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($activeRides as $ride): ?>
                    <?= renderRideCard($ride, true) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sección de viajes pasados -->
    <div id="content-past" class="hidden space-y-6">
        <?php if (empty($pastRides)): ?>
            <div class="text-center py-20 bg-surface/30 rounded-3xl border border-dashed border-gray-700">
                <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-history text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2">Historial vacío</h3>
                <p class="text-gray-400">Aún no tienes viajes finalizados.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                 <?php foreach ($pastRides as $ride): ?>
                    <?= renderRideCard($ride, false) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sección de reservas como pasajero -->
    <div id="content-bookings" class="hidden space-y-6">
        <?php if (empty($activeBookings)): ?>
            <div class="text-center py-20 bg-surface/30 rounded-3xl border border-dashed border-gray-700">
                <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-ticket-alt text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-medium text-white mb-2">No tienes reservas activas</h3>
                <p class="text-gray-400 mb-6">Explora los viajes disponibles y solicita una plaza.</p>
                <a href="dashboard.php" class="text-primary font-semibold hover:underline">Buscar viajes ahora</a>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($activeBookings as $booking): ?>
                    <?= renderBookingCard($booking) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Función auxiliar para las tarjetas de viaje -->
<?php
function renderRideCard($ride, $isActive) {
    ob_start();
    ?>
    <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden hover:border-gray-600 transition-colors shadow-lg">
        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Información principal -->
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                             <div class="px-3 py-1 bg-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/10 text-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-400 rounded-full text-xs font-bold border border-<?= $ride['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/20 uppercase tracking-wide">
                                <?= $ride['tipo'] === 'ofrezco' ? 'Conductor' : 'Pasajero' ?>
                            </div>
                            <span class="text-sm text-gray-400 flex items-center gap-2">
                                <i class="far fa-calendar"></i>
                                <?= date('d M, Y', strtotime($ride['fechaSalida'])) ?>
                            </span>
                        </div>
                        <div class="text-right">
                             <span class="text-2xl font-bold text-primary"><?= number_format($ride['precio'], 2) ?>€</span>
                             <p class="text-xs text-gray-500">por plaza</p>
                        </div>
                    </div>

                    <!-- Línea de ruta -->
                    <div class="relative pl-8 border-l-2 border-gray-700 py-2 space-y-6 mb-6">
                        <div class="relative">
                            <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-primary bg-surface"></div>
                            <h4 class="text-lg font-bold text-white leading-none"><?= htmlspecialchars($ride['nombreOrigen']) ?></h4>
                            <p class="text-sm text-primary font-mono mt-1"><?= substr($ride['horaSalida'], 0, 5) ?></p>
                        </div>
                        <div class="relative">
                            <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-gray-500 bg-surface"></div>
                             <h4 class="text-lg font-bold text-white leading-none"><?= htmlspecialchars($ride['nombreDestino']) ?></h4>
                             <?php if ($ride['horaRegreso']): ?>
                                <p class="text-sm text-gray-400 font-mono mt-1">Regreso: <?= substr($ride['horaRegreso'], 0, 5) ?></p>
                             <?php endif; ?>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <?php if (!empty($ride['descripcion'])): ?>
                        <div class="bg-gray-800/50 p-4 rounded-xl border border-gray-700/50 mb-6">
                            <p class="text-sm text-gray-300 italic">"<?= htmlspecialchars($ride['descripcion']) ?>"</p>
                        </div>
                    <?php endif; ?>

                    <!-- Estadísticas -->
                     <div class="flex items-center gap-6 text-sm">
                        <div class="flex items-center gap-2 text-gray-300" title="Plazas totales">
                            <i class="fas fa-chair text-gray-500"></i>
                            <span><?= $ride['plazasDisponibles'] + count($ride['passengers']) ?> plazas total</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-300" title="Pasajeros unidos">
                            <i class="fas fa-users text-gray-500"></i>
                            <span><?= count($ride['passengers']) ?> unidos</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones y pasajeros -->
                <div class="w-full md:w-72 border-t md:border-t-0 md:border-l border-gray-700 pt-6 md:pt-0 md:pl-6 flex flex-col">
                    
                    <?php if ($ride['tipo'] === 'ofrezco'): ?>
                    <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Pasajeros (<?= count($ride['passengers']) ?>)</h5>
                    
                    <div class="flex-grow">
                        <?php if (empty($ride['passengers'])): ?>
                            <p class="text-sm text-gray-500 italic mb-4">Aún no hay pasajeros.</p>
                        <?php else: ?>
                            <div class="space-y-3 mb-6">
                                <?php foreach ($ride['passengers'] as $passenger): ?>
                                    <div class="flex flex-col gap-2 bg-gray-800/50 p-2 rounded-lg border border-gray-700/50">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white">
                                                <?= strtoupper(substr($passenger['nombre'], 0, 2)) ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($passenger['nombre']) ?></p>
                                            </div>
                                            <a href="profile.php?id=<?= $passenger['idUsuario'] ?>" class="text-gray-400 hover:text-white" title="Ver perfil">
                                                <i class="fas fa-external-link-alt text-xs"></i>
                                            </a>
                                        </div>

                                        <!-- Acciones/Estado -->
                                        <?php if ($passenger['estado'] === 'pendiente'): ?>
                                            <div class="flex gap-2 mt-1">
                                                <form action="manage-reservation.php" method="POST" class="flex-1">
                                                    <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                                                    <input type="hidden" name="passenger_id" value="<?= $passenger['idUsuario'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="w-full py-1 bg-green-500/10 hover:bg-green-500/20 text-green-500 rounded text-xs font-bold border border-green-500/20 transition-colors">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="manage-reservation.php" method="POST" class="flex-1">
                                                     <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                                                    <input type="hidden" name="passenger_id" value="<?= $passenger['idUsuario'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="w-full py-1 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded text-xs font-bold border border-red-500/20 transition-colors">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($passenger['estado'] === 'aceptado'): ?>
                                            <div class="text-center py-0.5 bg-green-500/10 text-green-500 rounded text-[10px] font-bold border border-green-500/20">
                                                Confirmado
                                            </div>
                                        <?php elseif ($passenger['estado'] === 'rechazado'): ?>
                                            <div class="text-center py-0.5 bg-red-500/10 text-red-500 rounded text-[10px] font-bold border border-red-500/20">
                                                Rechazado
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($isActive): ?>
                        <div class="grid grid-cols-2 gap-3 mt-auto">
                            <a href="edit-ride.php?id=<?= $ride['idAnuncio'] ?>" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors border border-gray-600">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button onclick="confirmDelete(<?= $ride['idAnuncio'] ?>)" class="flex items-center justify-center gap-2 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-400 rounded-lg text-sm font-medium transition-colors border border-red-500/20">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="mt-auto">
                           <button disabled class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-800/50 text-gray-500 rounded-lg text-sm font-medium border border-gray-700 cursor-not-allowed">
                                <i class="fas fa-archive"></i> Archivado
                            </button>
                        </div>
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
        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Información principal -->
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                             <div class="px-3 py-1 bg-<?= $booking['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/10 text-<?= $booking['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-400 rounded-full text-xs font-bold border border-<?= $booking['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/20 uppercase tracking-wide">
                                <?= $booking['tipo'] === 'ofrezco' ? 'Conductor' : 'Pasajero' ?>
                            </div>
                            <span class="text-sm text-gray-400 flex items-center gap-2">
                                <i class="far fa-calendar"></i>
                                <?= date('d M, Y', strtotime($booking['fechaSalida'])) ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if ($booking['estadoReserva'] === 'pendiente'): ?>
                                <span class="px-3 py-1 bg-yellow-500/10 text-yellow-500 rounded-full text-xs font-bold border border-yellow-500/20">
                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                </span>
                            <?php elseif ($booking['estadoReserva'] === 'aceptado'): ?>
                                <span class="px-3 py-1 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20">
                                    <i class="fas fa-check mr-1"></i> Confirmado
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-xs font-bold border border-red-500/20">
                                    <i class="fas fa-times mr-1"></i> Rechazado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Línea de ruta -->
                    <div class="relative pl-8 border-l-2 border-gray-700 py-2 space-y-6 mb-6">
                        <div class="relative">
                            <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-primary bg-surface"></div>
                            <h4 class="text-lg font-bold text-white leading-none"><?= htmlspecialchars($booking['nombreOrigen']) ?></h4>
                            <p class="text-sm text-primary font-mono mt-1"><?= substr($booking['horaSalida'], 0, 5) ?></p>
                        </div>
                        <div class="relative">
                            <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-gray-500 bg-surface"></div>
                             <h4 class="text-lg font-bold text-white leading-none"><?= htmlspecialchars($booking['nombreDestino']) ?></h4>
                             <?php if ($booking['horaRegreso']): ?>
                                <p class="text-sm text-gray-400 font-mono mt-1">Regreso: <?= substr($booking['horaRegreso'], 0, 5) ?></p>
                             <?php endif; ?>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <?php if (!empty($booking['descripcion'])): ?>
                        <div class="bg-gray-800/50 p-4 rounded-xl border border-gray-700/50 mb-6">
                            <p class="text-sm text-gray-300 italic">"<?= htmlspecialchars($booking['descripcion']) ?>"</p>
                        </div>
                    <?php endif; ?>

                    <!-- Info del conductor -->
                    <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-sm font-bold text-secondary">
                            <?= strtoupper(substr($booking['nombreUsuario'], 0, 2)) ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($booking['nombreUsuario']) ?></p>
                            <p class="text-xs text-gray-400">Conductor</p>
                        </div>
                        <a href="profile.php?id=<?= $booking['idUsuario'] ?>" class="text-primary hover:text-primary-dark transition-colors">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="w-full md:w-56 border-t md:border-t-0 md:border-l border-gray-700 pt-6 md:pt-0 md:pl-6 flex flex-col">
                    <div class="text-center mb-4">
                         <span class="text-2xl font-bold text-primary"><?= number_format($booking['precio'], 2) ?>€</span>
                         <p class="text-xs text-gray-500">por plaza</p>
                    </div>
                    
                    <div class="space-y-3 mt-auto">
                        <a href="chat.php?anuncio_id=<?= $booking['idAnuncio'] ?>&other_user_id=<?= $booking['idUsuario'] ?>" class="flex items-center justify-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-secondary rounded-lg text-sm font-bold transition-all">
                            <i class="fas fa-comment"></i> Contactar
                        </a>
                        <a href="profile.php?id=<?= $booking['idUsuario'] ?>" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors border border-gray-600">
                            <i class="fas fa-user"></i> Ver perfil
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
    const activeTabBtn = document.getElementById('tab-active');
    const pastTabBtn = document.getElementById('tab-past');
    const bookingsTabBtn = document.getElementById('tab-bookings');
    const activeContent = document.getElementById('content-active');
    const pastContent = document.getElementById('content-past');
    const bookingsContent = document.getElementById('content-bookings');

    // Restablecer todas las pestañas
    [activeTabBtn, pastTabBtn, bookingsTabBtn].forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-400');
    });
    
    // Esconder el contenido
    [activeContent, pastContent, bookingsContent].forEach(content => {
        content.classList.add('hidden');
    });

    // Mostrar la pestaña seleccionada
    if (tab === 'active') {
        activeTabBtn.classList.replace('border-transparent', 'border-primary');
        activeTabBtn.classList.replace('text-gray-400', 'text-primary');
        activeContent.classList.remove('hidden');
    } else if (tab === 'past') {
        pastTabBtn.classList.replace('border-transparent', 'border-primary');
        pastTabBtn.classList.replace('text-gray-400', 'text-primary');
        pastContent.classList.remove('hidden');
    } else if (tab === 'bookings') {
        bookingsTabBtn.classList.replace('border-transparent', 'border-primary');
        bookingsTabBtn.classList.replace('text-gray-400', 'text-primary');
        bookingsContent.classList.remove('hidden');
    }
}

function confirmDelete(rideId) {
    if(confirm('¿Estás seguro de que quieres eliminar este viaje? Esta acción no se puede deshacer.')) {
        window.location.href = `delete-ride.php?id=${rideId}`;
    }
}
</script>

</body>
</html>