<?php require_once __DIR__ . '/../layouts/header.php'; 
    $flashData = getFlash();
    $error = ($flashData && $flashData['type'] === 'error') ? $flashData['message'] : null;
    $success = ($flashData && $flashData['type'] === 'success') ? $flashData['message'] : null;
    $tab = ($flashData && isset($flashData['tab'])) ? $flashData['tab'] : 'profile';
    
    // Calcular estadísticas del usuario
    $userStats = [
        'total_viajes' => 0,
        'viajes_completados' => 0,
        'valoracion_promedio' => 0,
        'viajes_como_conductor' => 0,
        'viajes_como_pasajero' => 0,
    ];
?>

<div class="w-full mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-8">

    <!-- Encabezado del perfil -->
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-xl overflow-hidden mb-8">

        <div class="px-4 sm:px-8 pt-6 sm:pt-8 pb-6 sm:pb-8 flex flex-col md:flex-row items-start gap-4 sm:gap-6 relative">
             <!-- Avatar -->
             <div class="relative group">
                <div id="profile-avatar" class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl border-2 border-gray-700 bg-gray-800 flex items-center justify-center overflow-hidden shadow-xl">
                    <?php if (!empty($profileUser['foto_perfil']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $profileUser['foto_perfil'])): ?>
                        <?php $pf = htmlspecialchars($profileUser['foto_perfil']); $ver = filemtime(__DIR__ . '/../../public/uploads/profiles/' . $profileUser['foto_perfil']); ?>
                        <img src="public/uploads/profiles/<?= $pf ?>?v=<?= $ver ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-3xl sm:text-5xl font-bold text-white"><?= strtoupper(substr($profileUser['nombre'], 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Insignia de verificación -->
                <?php if($profileUser['estado_verificacion'] == 2): ?>
                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center border-4 border-surface shadow-lg">
                    <i class="fas fa-check text-white text-sm"></i>
                </div>
                <?php endif; ?>
                
                <?php if ($isOwnProfile): ?>
                <button onclick="document.getElementById('photo-input').click()" class="absolute bottom-0 right-0 bg-primary text-secondary p-2.5 rounded-full border-2 border-surface hover:bg-primary-dark transition-colors cursor-pointer shadow-lg opacity-0 group-hover:opacity-100" title="<?= t('profile.change_photo') ?>">
                    <i class="fas fa-camera text-sm"></i>
                </button>
                <?php endif; ?>
             </div>
             
             <!-- Información del usuario -->
             <div class="flex-1 pt-6">
                 <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                     <div>
                         <div class="flex items-center gap-3 mb-2">
                             <h1 class="text-3xl lg:text-4xl font-bold text-white"><?= htmlspecialchars($profileUser['nombre']) ?></h1>
                             <?php if (!empty($profileUser['premium']) && (!empty($profileUser['premium_hasta']) ? $profileUser['premium_hasta'] > date('Y-m-d H:i:s') : true)): ?>
                                 <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-bold rounded-full border border-yellow-500/30 flex items-center gap-1 flex-shrink-0">
                                     <i class="fas fa-crown"></i> <?= t('profile.premium') ?>
                                 </span>
                             <?php endif; ?>
                         </div>
                         <div class="flex flex-wrap items-center gap-4 text-sm lg:text-base text-gray-400">
                             <span class="flex items-center gap-2">
                                 <i class="fas fa-map-marker-alt text-primary"></i> 
                                 <?= htmlspecialchars($profileUser['ciudad'] ?? t('profile.no_location')) ?>
                             </span>
                             <?php if (!empty($profileUser['institucion'])): ?>
                             <span class="flex items-center gap-2">
                                 <i class="fas fa-university text-blue-400"></i> 
                                 <?= htmlspecialchars($profileUser['institucion']) ?>
                             </span>
                             <?php endif; ?>
                             <?php if (!empty($profileUser['vehiculo'])): ?>
                             <span class="flex items-center gap-2">
                                 <i class="fas fa-car text-purple-400"></i> 
                                 <?= htmlspecialchars($profileUser['vehiculo']) ?>
                             </span>
                             <?php endif; ?>
                         </div>
                     </div>
                     <?php if (!$isOwnProfile): ?>
                         <button onclick="openReportModal('usuario', {idUsuario: <?= (int)$profileUser['idUsuario'] ?>})" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-sm font-medium border border-red-500/20 transition-colors flex-shrink-0">
                             <i class="fas fa-flag text-xs"></i> <?= t('profile.report_user') ?>
                         </button>
                     <?php endif; ?>
                 </div>

                 <!-- Estadísticas en cards -->
                 <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 lg:gap-4">
                     <div class="bg-gray-800/50 rounded-xl p-3 lg:p-4 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-star text-yellow-500 text-sm"></i>
                             <span class="text-xs text-gray-400"><?= t('profile.rating') ?></span>
                         </div>
                         <p class="text-xl font-bold text-white">
                             <?= number_format($userStats['valoracion_promedio'] ?? 0, 1) ?>
                         </p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 lg:p-4 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-route text-blue-400 text-sm"></i>
                             <span class="text-xs text-gray-400"><?= t('profile.rides') ?></span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['total_viajes'] ?></p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 lg:p-4 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-steering-wheel text-primary text-sm"></i>
                             <span class="text-xs text-gray-400"><?= t('profile.driver') ?></span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['viajes_como_conductor'] ?></p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 lg:p-4 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-users text-purple-400 text-sm"></i>
                             <span class="text-xs text-gray-400"><?= t('profile.passenger') ?></span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['viajes_como_pasajero'] ?></p>
                     </div>
                     <div class="bg-gray-800/50 rounded-xl p-3 border border-green-500/20">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-leaf text-green-400 text-sm"></i>
                             <span class="text-xs text-gray-400"><?= t('co2.saved') ?></span>
                         </div>
                         <p class="text-xl font-bold text-green-400"><?= number_format($userStats['co2_ahorrado'] ?? 0, 1) ?> kg</p>
                     </div>
                 </div>
             </div>
        </div>
        
        <!-- Navegación de pestañas -->
        <?php if ($isOwnProfile): ?>
        <div class="px-4 sm:px-8 pb-6">
            <div class="flex gap-2 overflow-x-auto border-t border-gray-700 pt-4 scrollbar-hide">
                <button onclick="switchTab('profile')" id="tab-profile" class="px-4 py-2 rounded-lg bg-primary/10 text-primary font-medium border border-primary/20 whitespace-nowrap transition-colors">
                    <i class="fas fa-user mr-2"></i><?= t('profile.tab_profile') ?>
                </button>
                <button onclick="switchTab('security')" id="tab-security" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-lock mr-2"></i><?= t('profile.tab_security') ?>
                </button>
                <button onclick="switchTab('privacy')" id="tab-privacy" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-shield-alt mr-2"></i><?= t('profile.tab_privacy') ?>
                </button>
                <button onclick="switchTab('verification')" id="tab-verification" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-check-circle mr-2"></i><?= t('profile.tab_verification') ?>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Contenido principal -->
            <!-- Valoraciones -->
            <div class="mt-6">
                <div class="bg-surface rounded-2xl border border-gray-700 p-6 mb-6">
                    <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-star text-yellow-400"></i> <?= t('profile.ratings') ?>
                    </h3>
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <div class="text-3xl font-bold text-white"><?= number_format($userStats['valoracion_promedio'] ?? 0, 1) ?></div>
                            <div class="text-sm text-gray-400">(<?= t('profile.ratings_avg') ?> <?= count($ratings ?? []) ?> <?= t('profile.ratings_suffix') ?>)</div>
                        </div>
                    </div>


                    <div class="space-y-4">
                        <?php if (empty($ratings)): ?>
                            <p class="text-sm text-gray-400"><?= t('profile.no_ratings') ?></p>
                        <?php else: ?>
                            <?php foreach ($ratings as $rv): ?>
                                <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-800 flex-shrink-0">
                                            <?php if (!empty($rv['valoradorFoto']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $rv['valoradorFoto'])): ?>
                                                <?php $vpf = htmlspecialchars($rv['valoradorFoto']); $vver = filemtime(__DIR__ . '/../../public/uploads/profiles/' . $rv['valoradorFoto']); ?>
                                                <img src="public/uploads/profiles/<?= $vpf ?>?v=<?= $vver ?>" class="w-full h-full object-cover" alt="">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-xs text-white font-bold bg-gradient-to-tr from-gray-700 to-gray-600"><?= strtoupper(substr($rv['valoradorNombre'], 0, 2)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <span class="text-sm font-bold text-white"><?= htmlspecialchars($rv['valoradorNombre']) ?></span>
                                                <div class="flex items-center gap-0.5">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <i class="fas fa-star text-xs <?= $s <= (int)$rv['puntuacion'] ? 'text-yellow-400' : 'text-gray-600' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($rv['comentario'])): ?>
                                                <p class="text-sm text-gray-300 mt-1 italic">"<?= htmlspecialchars($rv['comentario']) ?>"</p>
                                            <?php endif; ?>
                                            <!-- Respuesta del valorado -->
                                            <?php if (!empty($rv['respuesta'])): ?>
                                                <div class="mt-2 pl-3 border-l-2 border-primary/40">
                                                    <p class="text-xs text-gray-400 mb-0.5"><i class="fas fa-reply text-primary/60 mr-1"></i><?= t('profile.reply') ?></p>
                                                    <p class="text-sm text-gray-300"><?= htmlspecialchars($rv['respuesta']) ?></p>
                                                </div>
                                            <?php elseif ($isOwnProfile): ?>
                                                <div class="mt-2">
                                                    <button onclick="toggleReplyForm(<?= (int)$rv['idValoracion'] ?>)" class="text-xs text-primary hover:underline">
                                                        <i class="fas fa-reply mr-1"></i>Responder
                                                    </button>
                                                    <div id="reply-form-<?= (int)$rv['idValoracion'] ?>" class="hidden mt-2">
                                                        <textarea id="reply-text-<?= (int)$rv['idValoracion'] ?>" rows="2" maxlength="300" placeholder="Escribe tu respuesta..." class="w-full bg-gray-900 border border-gray-600 text-gray-100 text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-primary resize-none placeholder-gray-500"></textarea>
                                                        <div class="flex gap-2 mt-1.5">
                                                            <button onclick="submitRatingReply(<?= (int)$rv['idValoracion'] ?>)" class="px-3 py-1.5 bg-primary text-secondary text-xs font-bold rounded-lg hover:bg-primary-dark transition-colors">Publicar</button>
                                                            <button onclick="toggleReplyForm(<?= (int)$rv['idValoracion'] ?>)" class="px-3 py-1.5 bg-gray-700 text-gray-300 text-xs rounded-lg hover:bg-gray-600 transition-colors">Cancelar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
     <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
         
        <!-- Columna izquierda -->
        <div class="space-y-6">            
            <!-- Información adicional -->
            <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                 <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Información</h3>
                 <div class="space-y-4">
                     <div>
                         <p class="text-xs text-gray-500">Estado de verificación</p>
                         <div class="mt-1.5">
                             <?php if($profileUser['estado_verificacion'] == 2): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                    <i class="fas fa-check-circle"></i> Verificado
                                </span>
                             <?php elseif($profileUser['estado_verificacion'] == 1): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                             <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400 border border-gray-500/20">
                                    No verificado
                                </span>
                             <?php endif; ?>
                         </div>
                     </div>
                     
                     <div>
                         <p class="text-xs text-gray-500">Miembro desde</p>
                         <p class="text-white mt-1">Octubre 2025</p>
                     </div>

                     <?php
                     // Teléfono: solo si la visibilidad lo permite
                     $visibTel = $profileUser['visibilidad_telefono'] ?? 'rides_only';
                     $mostrarTelefono = !$isOwnProfile && !empty($profileUser['telefono']) && $visibTel === 'public';
                     ?>
                     <?php if ($mostrarTelefono): ?>
                     <div>
                         <p class="text-xs text-gray-500">Contacto</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['telefono']) ?></p>
                     </div>
                     <?php endif; ?>

                     <?php
                     // Campos de detalle: solo visibilidad pública
                     $visibPerfil = $profileUser['visibilidad_perfil'] ?? 'public';
                     $esPublico   = $visibPerfil === 'public';
                     ?>
                     <?php if (!$isOwnProfile && $esPublico && !empty($profileUser['correo'])): ?>
                     <div>
                         <p class="text-xs text-gray-500">Correo</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['correo']) ?></p>
                     </div>
                     <?php endif; ?>
                     <?php if (!$isOwnProfile && $esPublico && !empty($profileUser['ciudad'])): ?>
                     <div>
                         <p class="text-xs text-gray-500">Ciudad</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['ciudad']) ?></p>
                     </div>
                     <?php endif; ?>
                     <?php if (!$isOwnProfile && $esPublico && !empty($profileUser['vehiculo'])): ?>
                     <div>
                         <p class="text-xs text-gray-500">Vehículo</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['vehiculo']) ?></p>
                     </div>
                     <?php endif; ?>
                     <?php if (!$isOwnProfile && $esPublico && !empty($profileUser['institucion'])): ?>
                     <div>
                         <p class="text-xs text-gray-500">Institución</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['institucion']) ?></p>
                     </div>
                     <?php endif; ?>
                 </div>
            </div>

            <!-- Sobre mí: visible para perfil público y registrado (no privado) -->
            <?php if ($isOwnProfile || $visibPerfil !== 'private'): ?>
             <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                 <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                     <i class="fas fa-quote-left text-primary"></i> Sobre mí
                 </h3>
                 <p class="text-gray-400 text-sm leading-relaxed italic">
                     <?= !empty($profileUser['biografia']) ? nl2br(htmlspecialchars($profileUser['biografia'])) : 'Este usuario no ha escrito nada sobre sí mismo aún.' ?>
                 </p>
             </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha -->
        <div class="lg:col-span-2">
            
            <?php if ($isOwnProfile): ?>
                
                <!-- Perfil -->
                <div id="content-profile" class="bg-surface rounded-2xl border border-gray-700 p-4 sm:p-6 lg:p-8">
                     <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                         <i class="fas fa-user-edit text-primary"></i> Editar Perfil
                     </h3>

                     <?php if ($error && (!$tab || $tab === 'profile')): ?>
                        <div class="mb-6 rounded-xl border border-red-500 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                            <?php
                                switch ($error) {
                                    case 'invalid_email':
                                        echo 'El correo electrónico no es válido.';
                                        break;
                                    case 'biografia_too_long':
                                        echo 'La biografía no puede superar los 300 caracteres.';
                                        break;
                                    case 'update_failed':
                                        echo 'Error al actualizar el perfil. Inténtalo de nuevo.';
                                        break;
                                    default:
                                        echo 'Ha ocurrido un error. Inténtalo de nuevo.';
                                }
                            ?>
                        </div>
                     <?php endif; ?>

                     <form action="<?= url('/profile') ?>?action=update" method="POST" enctype="multipart/form-data">
                         <input type="file" name="foto_perfil" id="photo-input" class="hidden" accept="image/*">
                         
                         <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Nombre Completo</label>
                                 <input type="text" name="nombre" value="<?= htmlspecialchars($profileUser['nombre']) ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Correo Electrónico</label>
                                 <input type="email" name="correo" value="<?= htmlspecialchars($profileUser['correo']) ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Teléfono</label>
                                 <input type="text" name="telefono" value="<?= htmlspecialchars($profileUser['telefono']) ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Ciudad / Localidad</label>
                                 <input type="text" name="ciudad" value="<?= htmlspecialchars($profileUser['ciudad'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Vehículo</label>
                                 <input type="text" name="vehiculo" value="<?= htmlspecialchars($profileUser['vehiculo'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm" placeholder="Ej: Seat Ibiza Rojo">
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Institución / Universidad</label>
                                 <input type="text" name="institucion" value="<?= htmlspecialchars($profileUser['institucion'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                             </div>
                             <div class="md:col-span-2">
                                 <label class="block text-sm font-medium text-gray-400 mb-2">Biografía</label>
                                 <textarea name="biografia" rows="4" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm resize-none" placeholder="Cuéntanos algo sobre ti..."><?= htmlspecialchars($profileUser['biografia'] ?? '') ?></textarea>
                             </div>

                             <!-- Preferencias de viaje -->
                             <div class="md:col-span-2">
                                 <label class="block text-sm font-medium text-gray-400 mb-3"><?= t('pref.title') ?></label>
                                 <p class="text-xs text-gray-500 mb-4"><?= t('pref.subtitle') ?></p>
                                 <?php
                                 $userPrefs = json_decode($profileUser['preferencias_viaje'] ?? '[]', true) ?: [];
                                 $allPrefs = [
                                     'silencio' => ['icon' => 'fa-volume-mute', 'color' => 'blue'],
                                     'charla'   => ['icon' => 'fa-comments',    'color' => 'green'],
                                     'mascotas' => ['icon' => 'fa-paw',         'color' => 'yellow'],
                                     'no_fumar' => ['icon' => 'fa-smoking-ban', 'color' => 'red'],
                                     'equipaje' => ['icon' => 'fa-suitcase',    'color' => 'purple'],
                                     'musica'   => ['icon' => 'fa-music',       'color' => 'pink'],
                                 ];
                                 ?>
                                 <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                                     <?php foreach ($allPrefs as $key => $pref):
                                         $isActive = in_array($key, $userPrefs);
                                     ?>
                                     <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all <?= $isActive ? 'bg-' . $pref['color'] . '-500/10 border-' . $pref['color'] . '-500/30' : 'bg-gray-800/50 border-gray-700 hover:border-gray-600' ?>">
                                         <input type="checkbox" name="preferencias_viaje[]" value="<?= $key ?>" <?= $isActive ? 'checked' : '' ?>
                                                class="hidden" onchange="this.closest('label').classList.toggle('bg-gray-800/50'); this.closest('label').classList.toggle('border-gray-700');">
                                         <i class="fas <?= $pref['icon'] ?> text-<?= $pref['color'] ?>-400 w-5 text-center"></i>
                                         <span class="text-sm text-gray-300"><?= t('pref.' . $key) ?></span>
                                     </label>
                                     <?php endforeach; ?>
                                 </div>
                             </div>
                         </div>
                         <div class="flex justify-end">
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                                 <i class="fas fa-save"></i> Guardar Cambios
                             </button>
                         </div>
                     </form>
                </div>

                <!-- Seguridad -->
                <div id="content-security" class="hidden bg-surface rounded-2xl border border-gray-700 p-4 sm:p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-lock text-primary"></i> Cambiar Contraseña
                    </h3>
                    <?php if ($error && $tab === 'security'): ?>
                        <div class="mb-6 rounded-xl border border-red-500 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                            <?php
                                switch ($error) {
                                    case 'wrong_password':
                                        echo 'La contraseña actual no es correcta.';
                                        break;
                                    case 'same_password':
                                        echo 'La nueva contraseña no puede ser igual a la actual.';
                                        break;
                                    case 'password_mismatch':
                                        echo 'Las nuevas contraseñas no coinciden.';
                                        break;
                                    case 'password_too_short':
                                        echo 'La nueva contraseña debe tener al menos 8 caracteres.';
                                        break;
                                    case 'password_weak':
                                        echo 'La contraseña debe incluir al menos una letra mayúscula y un número.';
                                        break;
                                    case 'empty_fields':
                                        echo 'Debes rellenar todos los campos.';
                                        break;
                                    default:
                                        echo 'Ha ocurrido un error. Inténtalo de nuevo.';
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success && $tab === 'security'): ?>
                        <div class="mb-6 rounded-xl border border-green-500 bg-green-500/10 px-4 py-3 text-sm text-green-400">
                            Contraseña actualizada correctamente.
                        </div>
                    <?php endif; ?>
                    <form action="<?= url('/profile') ?>?action=change_password" method="POST" class="max-w-md">
                        <div class="space-y-6 mb-8">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Contraseña Actual</label>
                                <input type="password" name="current_password" required class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Nueva Contraseña</label>
                                <input type="password" name="new_password" required class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                                <p class="mt-1.5 text-xs text-gray-500">Mínimo 8 caracteres, al menos una mayúscula y un número.</p>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Confirmar Nueva Contraseña</label>
                                <input type="password" name="confirm_password" required class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                            </div>
                        </div>
                        <div class="flex justify-end">
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                                 <i class="fas fa-key"></i> Actualizar Contraseña
                             </button>
                         </div>
                    </form>
                </div>

                <!-- Verificación -->
                <div id="content-verification" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
                     <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                         <i class="fas fa-shield-check text-primary"></i> Verificación de Estudiante
                     </h3>

                     <?php if ($error && $tab === 'verification'): ?>
                        <div class="mb-6 rounded-xl border border-red-500 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                            <?php
                                switch ($error) {
                                    case 'invalid_file_type':
                                        echo 'Tipo de archivo no permitido. Solo se aceptan PDF, JPG, PNG o WebP.';
                                        break;
                                    case 'file_too_large':
                                        echo 'El archivo es demasiado grande. El tamaño máximo es 5 MB.';
                                        break;
                                    case 'upload_failed':
                                        echo 'Error al subir el archivo. Inténtalo de nuevo.';
                                        break;
                                    case 'no_file':
                                        echo 'No se ha seleccionado ningún archivo.';
                                        break;
                                    default:
                                        echo 'Ha ocurrido un error. Inténtalo de nuevo.';
                                }
                            ?>
                        </div>
                     <?php endif; ?>

                     <?php if ($profileUser['estado_verificacion'] == 2): ?>
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-6 text-center">
                            <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                            <h4 class="text-lg font-bold text-green-500 mb-2">¡Tu cuenta está verificada!</h4>
                            <p class="text-gray-400 text-sm">Ya puedes disfrutar de todas las ventajas de ser un estudiante verificado.</p>
                        </div>
                     <?php elseif ($profileUser['estado_verificacion'] == 1): ?>
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-6 text-center">
                            <i class="fas fa-clock text-5xl text-yellow-500 mb-3"></i>
                            <h4 class="text-lg font-bold text-yellow-500 mb-2">Solicitud en revisión</h4>
                            <p class="text-gray-400 text-sm">Estamos revisando tus documentos. Te notificaremos pronto.</p>
                        </div>
                     <?php else: ?>
                        <div class="mb-8">
                            <p class="text-gray-400 text-sm mb-4">Sube una foto de tu carnet de estudiante o matrícula para verificar tu estatus.</p>
                            
                            <form action="<?= url('/profile') ?>?action=verify" method="POST" enctype="multipart/form-data" class="bg-gray-800/50 p-6 rounded-xl border border-dashed border-gray-600 text-center">
                                <div class="mb-4">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-500 mb-2"></i>
                                    <p class="text-sm text-gray-400">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                                </div>
                                <input type="file" name="document" required class="block w-full text-sm text-gray-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-xs file:font-semibold
                                  file:bg-primary file:text-secondary
                                  hover:file:bg-primary-dark
                                  cursor-pointer mb-6 mx-auto max-w-xs
                                ">
                                <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 flex items-center gap-2 mx-auto">
                                    <i class="fas fa-paper-plane"></i> Enviar Documentación
                                </button>
                            </form>
                        </div>
                     <?php endif; ?>
                </div>

                <!-- Privacidad -->
                <div id="content-privacy" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-user-shield text-primary"></i> Privacidad y Configuración
                    </h3>
                    <form action="<?= url('/profile') ?>?action=update_privacy" method="POST">
                        <div class="space-y-6 mb-8">
                             <div>
                                 <h4 class="text-white font-semibold mb-3">Visibilidad del Perfil</h4>
                                 <div class="space-y-2">
                                     <label class="flex items-center space-x-3 cursor-pointer">
                                         <input type="radio" name="visibilidad_perfil" value="public" <?= ($profileUser['visibilidad_perfil'] ?? 'public') == 'public' ? 'checked' : '' ?> class="form-radio text-primary bg-gray-800 border-gray-600 focus:ring-primary">
                                         <span class="text-gray-300">Público (Todos pueden ver tu perfil)</span>
                                     </label>
                                     <label class="flex items-center space-x-3 cursor-pointer">
                                         <input type="radio" name="visibilidad_perfil" value="registered" <?= ($profileUser['visibilidad_perfil'] ?? 'public') == 'registered' ? 'checked' : '' ?> class="form-radio text-primary bg-gray-800 border-gray-600 focus:ring-primary">
                                         <span class="text-gray-300">Solo usuarios registrados</span>
                                     </label>
                                     <label class="flex items-center space-x-3 cursor-pointer">
                                         <input type="radio" name="visibilidad_perfil" value="private" <?= ($profileUser['visibilidad_perfil'] ?? 'public') == 'private' ? 'checked' : '' ?> class="form-radio text-primary bg-gray-800 border-gray-600 focus:ring-primary">
                                         <span class="text-gray-300">Privado (Solo tú puedes verlo)</span>
                                     </label>
                                 </div>
                             </div>

                             <div class="pt-6 border-t border-gray-700">
                                 <h4 class="text-white font-semibold mb-3">Visibilidad del Teléfono</h4>
                                 <div class="space-y-2">
                                     <label class="flex items-center space-x-3 cursor-pointer">
                                         <input type="radio" name="visibilidad_telefono" value="public" <?= ($profileUser['visibilidad_telefono'] ?? 'rides_only') == 'public' ? 'checked' : '' ?> class="form-radio text-primary bg-gray-800 border-gray-600 focus:ring-primary">
                                         <span class="text-gray-300">Público (Visible en tu perfil)</span>
                                     </label>
                                     <label class="flex items-center space-x-3 cursor-pointer">
                                         <input type="radio" name="visibilidad_telefono" value="rides_only" <?= ($profileUser['visibilidad_telefono'] ?? 'rides_only') == 'rides_only' ? 'checked' : '' ?> class="form-radio text-primary bg-gray-800 border-gray-600 focus:ring-primary">
                                         <span class="text-gray-300">Solo en mis viajes (Usuarios aceptados)</span>
                                     </label>
                                 </div>
                             </div>

                             <div class="pt-6 border-t border-gray-700">
                                 <h4 class="text-white font-semibold mb-3">Notificaciones</h4>
                                 <label class="flex items-center space-x-3 cursor-pointer">
                                     <input type="checkbox" name="notificaciones_email" value="1" <?= ($profileUser['notificaciones_email'] ?? 1) ? 'checked' : '' ?> class="form-checkbox text-primary bg-gray-800 border-gray-600 focus:ring-primary rounded">
                                     <span class="text-gray-300">Recibir notificaciones por correo electrónico</span>
                                 </label>
                             </div>
                        </div>
                        <div class="flex justify-end">
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                                 <i class="fas fa-save"></i> Guardar Preferencias
                             </button>
                         </div>
                    </form>

                    <!-- Zona peligrosa: Eliminar cuenta -->
                    <div class="mt-10 pt-8 border-t border-red-500/20">
                        <h4 class="text-red-400 font-semibold mb-2 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i> <?= t('profile.danger_zone') ?>
                        </h4>
                        <p class="text-sm text-gray-400 mb-4"><?= t('profile.delete_warning') ?></p>
                        <button onclick="document.getElementById('delete-account-modal').classList.remove('hidden'); document.body.style.overflow='hidden';"
                                class="px-5 py-2.5 bg-red-500/10 text-red-400 border border-red-500/30 rounded-xl text-sm font-medium hover:bg-red-500/20 transition-all">
                            <i class="fas fa-trash-alt mr-2"></i><?= t('profile.delete_account') ?>
                        </button>
                    </div>
                </div>

            <?php else: ?>
                <?php
                // $visibPerfil ya está definida arriba en la columna izquierda
                // Si se carga el perfil propio antes de llegar aquí por algún motivo, aseguramos que exista
                if (!isset($visibPerfil)) {
                    $visibPerfil = $profileUser['visibilidad_perfil'] ?? 'public';
                }
                ?>

                <?php if ($visibPerfil === 'private'): ?>
                <!-- Perfil privado -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-8">
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-lock text-4xl mb-3"></i>
                        <p class="text-lg font-semibold text-white mb-1">Perfil privado</p>
                        <p class="text-sm">Este usuario ha decidido mantener su perfil privado.</p>
                    </div>
                </div>

                <?php else: ?>
                <!-- Anuncios activos del usuario -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-8">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-route text-primary"></i> Anuncios activos
                    </h3>
                    <?php if (empty($activeRides)): ?>
                        <div class="text-center py-10 text-gray-500">
                            <i class="fas fa-car-side text-4xl mb-3"></i>
                            <p>Este usuario no tiene anuncios activos ahora mismo.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($activeRides as $ar): ?>
                                <?php
                                    $arJson = array_merge($ar, [
                                        'nombreUsuario'       => $profileUser['nombre'],
                                        'foto_perfil'         => $profileUser['foto_perfil'] ?? null,
                                        'rating'              => $userStats['valoracion_promedio'] ?? 0,
                                        'estado_verificacion' => $profileUser['estado_verificacion'] ?? 0,
                                        'idUsuario'           => $profileUser['idUsuario'],
                                        'horaLlegada'         => $ar['horaLlegada'] ?? null,
                                        'horaRegreso'         => $ar['horaRegreso'] ?? null,
                                        'descripcion'         => $ar['descripcion'] ?? null,
                                        'preferencias_viaje'  => $profileUser['preferencias_viaje'] ?? '[]',
                                        'booking_status'      => null,
                                    ]);
                                ?>
                                <div class="relative group bg-gray-800/60 rounded-xl p-4 border border-gray-700/50 hover:border-primary/40 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer view-profile-ride-btn"
                                     data-ride='<?= htmlspecialchars(json_encode($arJson), ENT_QUOTES, 'UTF-8') ?>'>
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $ar['tipo'] === 'ofrezco' ? 'bg-primary/10 text-primary' : 'bg-purple-500/10 text-purple-400' ?>">
                                            <i class="fas <?= $ar['tipo'] === 'ofrezco' ? 'fa-steering-wheel' : 'fa-hand-paper' ?> text-sm"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                <?= htmlspecialchars($ar['nombreOrigen']) ?> &rarr; <?= htmlspecialchars($ar['nombreDestino']) ?>
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                <?= date('d/m/Y', strtotime($ar['fechaSalida'])) ?>
                                                a las <?= substr($ar['horaSalida'], 0, 5) ?>
                                                &bull;
                                                <?= $ar['tipo'] === 'ofrezco' ? 'Ofrezco plaza' : 'Busco plaza' ?>
                                                <?php if ($ar['tipo'] === 'ofrezco' && $ar['plazasDisponibles'] > 0): ?>
                                                    &bull; <?= (int)$ar['plazasDisponibles'] ?> plaza<?= $ar['plazasDisponibles'] != 1 ? 's' : '' ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <?php if (!empty($ar['precio'])): ?>
                                            <span class="text-sm font-bold text-primary"><?= number_format((float)$ar['precio'], 2) ?>€</span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">Gratis</span>
                                        <?php endif; ?>
                                        <span class="text-xs px-3 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-lg group-hover:bg-primary/20 transition-colors">
                                            Ver detalles <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
     </div>

</div>

<!-- Modal de eliminación de cuenta -->
<div id="delete-account-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4" onclick="if(event.target===this){this.classList.add('hidden');document.body.style.overflow='auto';}">
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white"><?= t('profile.delete_account') ?></h3>
                    <p class="text-sm text-gray-400"><?= t('profile.delete_irreversible') ?></p>
                </div>
            </div>
        </div>
        <form action="<?= url('/profile') ?>?action=delete_account" method="POST">
            <div class="p-6">
                <p class="text-gray-300 mb-4"><?= t('profile.delete_confirm_text') ?></p>
                <ul class="text-sm text-gray-400 space-y-1 mb-6">
                    <li><i class="fas fa-times text-red-400 mr-2 w-4 text-center"></i><?= t('profile.delete_data_rides') ?></li>
                    <li><i class="fas fa-times text-red-400 mr-2 w-4 text-center"></i><?= t('profile.delete_data_messages') ?></li>
                    <li><i class="fas fa-times text-red-400 mr-2 w-4 text-center"></i><?= t('profile.delete_data_ratings') ?></li>
                    <li><i class="fas fa-times text-red-400 mr-2 w-4 text-center"></i><?= t('profile.delete_data_profile') ?></li>
                </ul>
                <label class="block text-sm text-gray-300 mb-2"><?= t('profile.delete_password_label') ?></label>
                <input type="password" name="password" required placeholder="<?= t('profile.delete_password_placeholder') ?>"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
            </div>
            <div class="p-6 bg-gray-800/50 border-t border-gray-700 flex gap-3">
                <button type="button" onclick="document.getElementById('delete-account-modal').classList.add('hidden');document.body.style.overflow='auto';"
                        class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition-all">
                    <?= t('chat.cancel') ?>
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all">
                    <i class="fas fa-trash-alt mr-2"></i><?= t('profile.delete_account') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.getElementById('content-profile').classList.add('hidden');
        document.getElementById('content-security').classList.add('hidden');
        document.getElementById('content-privacy').classList.add('hidden');
        document.getElementById('content-verification').classList.add('hidden');

        const tabs = ['profile', 'security', 'privacy', 'verification'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            btn.classList.remove('bg-primary/10', 'text-primary', 'border-primary/20');
            btn.classList.add('bg-transparent', 'text-gray-400', 'border-transparent');
        });

        document.getElementById('content-' + tabName).classList.remove('hidden');
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.classList.remove('bg-transparent', 'text-gray-400', 'border-transparent');
        activeBtn.classList.add('bg-primary/10', 'text-primary', 'border-primary/20');
    }

    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }

    function toggleReplyForm(id) {
        const form = document.getElementById('reply-form-' + id);
        if (form) form.classList.toggle('hidden');
    }

    function submitRatingReply(idValoracion) {
        const textarea = document.getElementById('reply-text-' + idValoracion);
        const respuesta = textarea ? textarea.value.trim() : '';
        if (!respuesta) { if (typeof showToast === 'function') showToast('<?= t('profile.reply_empty') ?>', false); return; }
        const body = new FormData();
        body.append('idValoracion', idValoracion);
        body.append('respuesta', respuesta);
        fetch('<?= url("/rating") ?>?action=reply', { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (typeof showToast === 'function') showToast(data.message, data.success);
                if (data.success) setTimeout(() => window.location.reload(), 1200);
            })
            .catch(() => { if (typeof showToast === 'function') showToast('<?= t('profile.reply_error') ?>', false); });
    }
</script>

<?php if (!$isOwnProfile && isset($_SESSION['user_id'])): ?>

<script>
    document.getElementById('rating-form')?.addEventListener('submit', function(e){
        e.preventDefault();
        const form = e.currentTarget;
        const data = new FormData(form);

        fetch('<?= url("/rating") ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                const msg = document.getElementById('rating-msg');
                if (res.success) {
                    msg.textContent = 'Valoración enviada. Media actual: ' + (res.avg || res.avg === 0 ? res.avg : '-');
                    // actualizar número principal
                    const el = document.querySelector('.text-3xl.font-bold.text-white');
                    if (el) el.textContent = (res.avg ? parseFloat(res.avg).toFixed(1) : el.textContent);
                    form.querySelector('button').disabled = true;
                } else {
                    msg.textContent = res.message || 'Error al enviar valoración';
                }
            }).catch(()=>{
                document.getElementById('rating-msg').textContent = 'Error de red';
            });
    });
</script>

<?php endif; ?>

<script>
    const photoInput = document.getElementById('photo-input');
    const avatarContainer = document.getElementById('profile-avatar');
    if (photoInput && avatarContainer) {
        photoInput.addEventListener('change', function(e){
            const file = this.files && this.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) return;

            const url = URL.createObjectURL(file);
            avatarContainer.innerHTML = '';
            const img = document.createElement('img');
            img.src = url;
            img.alt = 'Profile preview';
            img.className = 'w-full h-full object-cover';
            avatarContainer.appendChild(img);

            img.onload = () => {
                URL.revokeObjectURL(url);
            };
        });

        if (<?= $isOwnProfile ? 'true' : 'false' ?>) {
            avatarContainer.style.cursor = 'pointer';
            avatarContainer.addEventListener('click', function(){
                photoInput.click();
            });
        }
    }
</script>


<?php if (!$isOwnProfile && !empty($activeRides)): ?>
<!-- Modal de detalles del anuncio -->
<div id="profile-ride-modal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-md transition-opacity duration-300 opacity-0" id="profile-modal-backdrop"></div>
    <div class="fixed inset-0 z-10 flex items-center justify-center p-3 sm:p-5">
        <div class="relative transform overflow-y-auto max-h-[92vh] rounded-3xl bg-gray-900 text-left shadow-2xl shadow-black/50 transition-all duration-300 w-full max-w-[76rem] border border-gray-700/40 opacity-0 translate-y-4 sm:scale-95" id="profile-modal-panel">

            <!-- Header -->
            <div class="sticky top-0 z-20 px-6 sm:px-8 py-4 bg-gray-900/95 backdrop-blur-xl border-b border-gray-800/80 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-lg shadow-primary/5">
                        <i class="fas fa-route text-primary text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-white"><?= t('dashboard.ride_details') ?></h3>
                        <div class="flex items-center gap-3 mt-0.5">
                            <span id="prm-tipo-badge" class="px-3 py-0.5 rounded-full text-xs font-bold border"></span>
                            <span class="text-sm text-gray-400 flex items-center gap-1.5">
                                <i class="far fa-calendar-alt"></i>
                                <span id="prm-fecha">—</span>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-400 hover:text-white hover:bg-white/10 transition-all" onclick="closeProfileRideModal()">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Barra de usuario horizontal -->
            <div class="px-6 sm:px-8 py-3.5 border-b border-gray-800/60 bg-gray-800/20">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700 ring-2 ring-gray-600/50 shrink-0" id="prm-avatar"></div>
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <h4 class="text-base font-bold text-white truncate shrink-0" id="prm-driver-name"></h4>
                        <span class="bg-yellow-500/10 text-yellow-400 px-2 py-0.5 rounded-lg border border-yellow-500/20 inline-flex items-center gap-1 text-xs font-bold shrink-0">
                            <i class="fas fa-star text-[9px]"></i>
                            <span id="prm-rating"></span>
                        </span>
                        <span class="text-gray-700 hidden sm:inline">|</span>
                        <div class="hidden sm:flex items-center gap-1.5 text-sm shrink-0">
                            <i class="fas fa-shield-alt text-xs" id="prm-verified-icon"></i>
                            <span id="prm-verified" class="font-medium"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <div id="prm-prefs-container" class="hidden sm:flex items-center gap-1.5" style="display:none;">
                            <div class="flex flex-wrap gap-1.5" id="prm-prefs"></div>
                        </div>
                        <a href="<?= url('/profile') ?>?id=<?= (int)$profileUser['idUsuario'] ?>" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-xl px-4 py-2 text-sm font-semibold transition-all">
                            <i class="fas fa-user text-xs"></i> <span class="hidden sm:inline"><?= t('dashboard.view_profile') ?></span><span class="sm:hidden"><?= t('dashboard.profile_short') ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2 columnas (Ruta | Mapa) -->
            <div class="px-6 sm:px-8 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Ruta + Stats + Descripcion -->
                    <div class="lg:col-span-5 space-y-5 order-1">
                        <div class="bg-gray-800/40 rounded-2xl p-6 border border-gray-700/30 shadow-lg shadow-black/10">
                            <div class="flex items-stretch gap-5">
                                <div class="flex flex-col items-center py-1">
                                    <div class="w-4 h-4 rounded-full border-[3px] border-primary bg-gray-900 shadow-lg shadow-primary/30 shrink-0"></div>
                                    <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/50 via-primary/20 to-gray-700 my-1.5"></div>
                                    <div class="w-4 h-4 rounded-full border-[3px] border-gray-500 bg-gray-900 shrink-0"></div>
                                </div>
                                <div class="flex-1 flex flex-col justify-between gap-6">
                                    <div>
                                        <p class="text-xl font-bold text-white tracking-tight" id="prm-origin"></p>
                                        <p class="text-sm text-primary font-semibold mt-1 flex items-center gap-2" id="prm-time-start"><i class="far fa-clock text-xs opacity-70"></i></p>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-white tracking-tight" id="prm-dest"></p>
                                        <p class="text-sm text-primary font-semibold mt-1 flex items-center gap-2" id="prm-time-end"><i class="far fa-clock text-xs opacity-70"></i></p>
                                    </div>
                                </div>
                            </div>
                            <!-- Estadísticas -->
                            <div class="flex flex-wrap items-center gap-2.5 mt-5 pt-5 border-t border-gray-700/30">
                                <span class="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-xl border border-primary/20" id="prm-price-container">
                                    <i class="fas fa-euro-sign text-primary"></i>
                                    <span class="text-lg font-extrabold text-primary" id="prm-price"></span>
                                    <span class="text-xs text-gray-500 font-medium">/<?= t('dashboard.seat') ?></span>
                                </span>
                                <span class="inline-flex items-center gap-2 bg-blue-500/10 px-4 py-2 rounded-xl border border-blue-500/20">
                                    <i class="fas fa-chair text-blue-400"></i>
                                    <span class="text-lg font-extrabold text-white" id="prm-seats"></span>
                                    <span class="text-xs text-gray-500 font-medium"><?= t('dashboard.seats_short') ?></span>
                                </span>
                                <span class="inline-flex items-center gap-2 bg-purple-500/10 px-4 py-2 rounded-xl border border-purple-500/20" id="prm-return-container" style="display:none;">
                                    <i class="fas fa-undo text-purple-400"></i>
                                    <span class="text-lg font-extrabold text-purple-400" id="prm-return-time"></span>
                                </span>
                            </div>
                        </div>
                        <!-- Comentarios -->
                        <div>
                            <h5 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2.5 flex items-center gap-2">
                                <i class="fas fa-comment-dots text-xs"></i> <?= t('dashboard.ride_comments') ?>
                            </h5>
                            <p class="text-sm text-gray-300 leading-relaxed bg-gray-800/30 p-5 rounded-2xl border border-gray-700/30" id="prm-desc"></p>
                        </div>
                    </div>

                    <!-- Mapa -->
                    <div class="lg:col-span-7 order-2" id="prm-map-container" style="display:none;">
                        <div class="relative rounded-2xl overflow-hidden border border-gray-700/30 shadow-xl shadow-black/20 h-full">
                            <div id="prm-map" class="w-full h-[300px] sm:h-[350px] lg:h-full lg:min-h-[380px]" style="z-index: 1;"></div>
                            <div class="absolute bottom-3 left-3 flex items-center gap-2 z-[2]">
                                <span id="prm-map-distance" class="inline-flex items-center gap-1.5 bg-gray-900/80 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-medium text-gray-200 border border-gray-700/50">
                                    <i class="fas fa-road text-primary text-[10px]"></i> <span></span>
                                </span>
                                <span id="prm-map-duration" class="inline-flex items-center gap-1.5 bg-gray-900/80 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-medium text-gray-200 border border-gray-700/50">
                                    <i class="fas fa-clock text-primary text-[10px]"></i> <span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 px-6 sm:px-8 py-4 border-t border-gray-800/80 bg-gray-900/95 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center gap-3">
                <button type="button"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl border border-gray-700 bg-gray-800/50 text-sm font-semibold text-gray-300 hover:bg-gray-800 hover:text-white transition-all order-3 sm:order-1"
                        onclick="closeProfileRideModal()">
                    <?= t('dashboard.close') ?>
                </button>
                <button type="button" id="prm-btn-report"
                        class="hidden w-full sm:w-auto px-5 py-3 rounded-xl border border-red-500/20 bg-red-500/5 text-sm font-medium text-red-400 hover:bg-red-500/15 hover:border-red-500/30 transition-all flex items-center justify-center gap-2 order-2 sm:order-2"
                        onclick="reportProfileRide()">
                    <i class="fas fa-flag text-xs"></i> Reportar
                </button>
                <div class="flex-1 hidden sm:block order-3"></div>
                <div class="flex gap-3 order-1 sm:order-4 w-full sm:w-auto">
                    <a href="#" id="prm-btn-contact"
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 bg-gray-700/50 hover:bg-gray-700 text-gray-200 hover:text-white border border-gray-600/30 rounded-xl px-6 py-3 text-sm font-semibold transition-all">
                        <i class="fas fa-comment-alt text-xs"></i> <?= t('dashboard.contact') ?>
                    </a>
                    <button type="button" id="prm-btn-reserve"
                            class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-primary text-secondary text-base font-bold hover:bg-primary-dark shadow-xl shadow-primary/25 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2.5">
                        <i class="fas fa-ticket-alt"></i> <?= t('dashboard.request_seat') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const prmModal    = document.getElementById('profile-ride-modal');
    const prmBackdrop = document.getElementById('profile-modal-backdrop');
    const prmPanel    = document.getElementById('profile-modal-panel');
    const prmCurrentUserId = <?= (int)$_SESSION['user_id'] ?>;
    let prmCurrentRide = null;

    function reportProfileRide() {
        if (!prmCurrentRide) return;
        openReportModal('anuncio', {
            idAnuncio: prmCurrentRide.idAnuncio,
            idUsuario: prmCurrentRide.idUsuario
        });
    }

    const prmBtnStyles = {
        active:   'w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2',
        disabled: 'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-700 bg-gray-800 text-sm font-bold text-gray-500 cursor-not-allowed flex items-center justify-center gap-2',
    };

    function submitReserveForm(rideId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url("/reserve") ?>';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ride_id';
        input.value = rideId;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    function openProfileRideModal(ride) {
        prmCurrentRide = ride;
        const btnReserve = document.getElementById('prm-btn-reserve');
        const btnReport  = document.getElementById('prm-btn-report');

        // Mostrar boton de reporte solo para anuncios de otros usuarios
        if (btnReport) btnReport.classList.toggle('hidden', ride.idUsuario == prmCurrentUserId);

        // Tipo badge
        const badge = document.getElementById('prm-tipo-badge');
        if (ride.tipo.toLowerCase() === 'ofrezco') {
            badge.textContent = 'Conductor';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-primary/10 text-primary border-primary/30';
        } else {
            badge.textContent = 'Pasajero';
            badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-purple-500/10 text-purple-400 border-purple-500/30';
        }

        // Fecha
        document.getElementById('prm-fecha').textContent = ride.fechaSalida
            ? new Date(ride.fechaSalida).toLocaleDateString('<?= currentLang() === 'es' ? 'es-ES' : 'en-GB' ?>', { day: '2-digit', month: 'short', year: 'numeric' })
            : '—';

        // Ruta
        document.getElementById('prm-origin').textContent = ride.nombreOrigen;
        document.getElementById('prm-dest').textContent   = ride.nombreDestino;

        const prmTimeStart = document.getElementById('prm-time-start');
        prmTimeStart.innerHTML = '<i class="far fa-clock text-xs"></i> <?= t('dashboard.departure') ?>: ' + ride.horaSalida.substring(0, 5);

        const prmTimeEnd = document.getElementById('prm-time-end');
        const prmArrival = ride.horaLlegada ? ride.horaLlegada.substring(0, 5) : '--:--';
        prmTimeEnd.innerHTML = '<i class="far fa-clock text-xs"></i> <?= t('dashboard.arrival_label') ?>: ' + prmArrival;

        // Hora de regreso
        const prmReturnContainer = document.getElementById('prm-return-container');
        if (ride.horaRegreso) {
            document.getElementById('prm-return-time').textContent = ride.horaRegreso.substring(0, 5);
            prmReturnContainer.style.display = '';
        } else {
            prmReturnContainer.style.display = 'none';
        }

        // Precio y plazas
        const priceEl        = document.getElementById('prm-price');
        const priceContainer = document.getElementById('prm-price-container');
        if (ride.tipo.toLowerCase() === 'ofrezco') {
            priceEl.textContent = new Intl.NumberFormat('<?= currentLang() === 'es' ? 'es-ES' : 'en-GB' ?>', { style: 'currency', currency: 'EUR' }).format(ride.precio || 0);
            priceContainer.style.display = '';
        } else {
            priceContainer.style.display = 'none';
        }
        document.getElementById('prm-seats').textContent = ride.plazasDisponibles ?? '—';

        // Avatar
        const avatarEl = document.getElementById('prm-avatar');
        if (ride.foto_perfil) {
            avatarEl.innerHTML = `<img src="public/uploads/profiles/${encodeURIComponent(ride.foto_perfil)}" alt="avatar" class="w-full h-full object-cover">`;
            avatarEl.className = 'w-20 h-20 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700 ring-2 ring-gray-700/50';
        } else {
            avatarEl.innerHTML = ride.nombreUsuario.substring(0, 2).toUpperCase();
            avatarEl.className = 'w-20 h-20 rounded-xl mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg bg-gradient-to-br from-primary to-primary-dark ring-2 ring-primary/20';
        }

        // Nombre y rating
        document.getElementById('prm-driver-name').textContent = ride.nombreUsuario;
        document.getElementById('prm-rating').textContent = parseFloat(ride.rating || 0).toFixed(1);

        // Verificación
        const verEl   = document.getElementById('prm-verified');
        const verIcon = document.getElementById('prm-verified-icon');
        if (ride.estado_verificacion == 2) {
            verEl.textContent  = 'Verificado';           verEl.className  = 'text-sm text-green-400';
            verIcon.className  = 'fas fa-shield-alt w-4 text-center text-green-400';
        } else if (ride.estado_verificacion == 1) {
            verEl.textContent  = 'Verificación pendiente'; verEl.className = 'text-sm text-yellow-400';
            verIcon.className  = 'fas fa-shield-alt w-4 text-center text-yellow-400';
        } else {
            verEl.textContent  = 'No verificado';        verEl.className  = 'text-sm text-gray-500';
            verIcon.className  = 'fas fa-shield-alt w-4 text-center text-gray-500';
        }

        // Descripcion
        document.getElementById('prm-desc').textContent = ride.descripcion?.trim()
            ? ride.descripcion
            : '<?= t('dashboard.no_comments') ?>';

        // Preferencias de viaje
        const prmPrefsContainer = document.getElementById('prm-prefs-container');
        const prmPrefsEl = document.getElementById('prm-prefs');
        const prmPrefIcons = {silencio:'fa-volume-mute',charla:'fa-comments',mascotas:'fa-paw',no_fumar:'fa-smoking-ban',equipaje:'fa-suitcase',musica:'fa-music'};
        const prmPrefColors = {silencio:'blue',charla:'green',mascotas:'yellow',no_fumar:'red',equipaje:'purple',musica:'pink'};
        const prmPrefLabels = <?= json_encode([
            'silencio' => t('pref.silencio'), 'charla' => t('pref.charla'), 'mascotas' => t('pref.mascotas'),
            'no_fumar' => t('pref.no_fumar'), 'equipaje' => t('pref.equipaje'), 'musica' => t('pref.musica')
        ]) ?>;
        let prmPrefs = [];
        try { prmPrefs = JSON.parse(ride.preferencias_viaje || '[]'); } catch(e) {}
        if (prmPrefs.length > 0) {
            prmPrefsEl.innerHTML = prmPrefs.map(p => `<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-${prmPrefColors[p]}-500/10 text-${prmPrefColors[p]}-400 border border-${prmPrefColors[p]}-500/20 text-xs font-medium"><i class="fas ${prmPrefIcons[p]}"></i> ${prmPrefLabels[p] || p}</span>`).join('');
            prmPrefsContainer.style.display = '';
        } else {
            prmPrefsContainer.style.display = 'none';
        }

        // Contactar
        document.getElementById('prm-btn-contact').href = '<?= url("/chat") ?>?anuncio_id=' + ride.idAnuncio + '&other_user_id=' + ride.idUsuario;

        // Botón de reserva
        btnReserve.onclick  = null;
        btnReserve.disabled = false;
        btnReserve.style.display = 'flex';

        if (ride.tipo.toLowerCase() === 'ofrezco' && ride.plazasDisponibles > 0) {
            btnReserve.className = prmBtnStyles.active;
            btnReserve.innerHTML = '<i class="fas fa-ticket-alt text-xs"></i> Solicitar Plaza';
            btnReserve.onclick   = () => { submitReserveForm(ride.idAnuncio); };
        } else if (ride.tipo.toLowerCase() === 'ofrezco' && ride.plazasDisponibles <= 0) {
            btnReserve.className = prmBtnStyles.disabled;
            btnReserve.innerHTML = '<i class="fas fa-ban text-xs"></i> Viaje completo';
            btnReserve.disabled  = true;
        } else {
            // Tipo "busco" — solo contactar
            btnReserve.style.display = 'none';
        }

        // Mapa de ruta
        const prmMapContainer = document.getElementById('prm-map-container');
        const prmMapEl = document.getElementById('prm-map');

        if (ride.ruta_polyline) {
            prmMapContainer.style.display = '';
            prmModal.classList.remove('hidden');
            requestAnimationFrame(() => {
                prmBackdrop.classList.remove('opacity-0');
                prmPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
                prmPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            });

            setTimeout(() => {
                if (window._prmMap) { window._prmMap.remove(); window._prmMap = null; }
                const map = L.map(prmMapEl, { zoomControl: true, attributionControl: false }).setView([39.5, -3.5], 6);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 18 }).addTo(map);
                window._prmMap = map;

                try {
                    const coords = JSON.parse(ride.ruta_polyline);
                    const latLngs = coords.map(c => [c[1], c[0]]);
                    const polyline = L.polyline(latLngs, { color: '#34d399', weight: 4, opacity: 0.8 }).addTo(map);

                    const greenIcon = L.divIcon({ html: '<div style="background:#34d399;width:12px;height:12px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>', iconSize: [12,12], iconAnchor: [6,6], className: '' });
                    const redIcon = L.divIcon({ html: '<div style="background:#f87171;width:12px;height:12px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>', iconSize: [12,12], iconAnchor: [6,6], className: '' });
                    L.marker(latLngs[0], { icon: greenIcon }).addTo(map);
                    L.marker(latLngs[latLngs.length - 1], { icon: redIcon }).addTo(map);
                    map.fitBounds(polyline.getBounds(), { padding: [25, 25] });
                } catch(e) {}

                if (ride.distancia_km) {
                    document.querySelector('#prm-map-distance span').textContent = ride.distancia_km + ' km';
                    document.querySelector('#prm-map-duration span').textContent = (ride.duracion_min || '--') + ' min';
                }
            }, 350);
            return;
        } else {
            prmMapContainer.style.display = 'none';
        }

        // Mostrar modal
        prmModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            prmBackdrop.classList.remove('opacity-0');
            prmPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
            prmPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        });
    }

    function closeProfileRideModal() {
        if (window._prmMap) { window._prmMap.remove(); window._prmMap = null; }
        prmBackdrop.classList.add('opacity-0');
        prmPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        prmPanel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');
        setTimeout(() => { prmModal.classList.add('hidden'); }, 300);
    }

    prmBackdrop.addEventListener('click', closeProfileRideModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProfileRideModal(); });

    document.querySelectorAll('.view-profile-ride-btn').forEach(card => {
        card.addEventListener('click', () => {
            openProfileRideModal(JSON.parse(card.getAttribute('data-ride')));
        });
    });
</script>
<?php endif; ?>

</body>
</html>
