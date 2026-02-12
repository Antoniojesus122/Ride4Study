<?php require_once __DIR__ . '/../layouts/header.php'; 
    $error = $_GET['error'] ?? null;
    $success = $_GET['success'] ?? null;
    $tab = $_GET['tab'] ?? 'profile';
    
    // Calcular estadísticas del usuario
    $userStats = [
        'total_viajes' => 0,
        'viajes_completados' => 0,
        'valoracion_promedio' => 0,
        'viajes_como_conductor' => 0,
        'viajes_como_pasajero' => 0,
    ];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Encabezado del perfil -->
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-xl overflow-hidden mb-8">
        <!-- Banner superior con gradiente -->
        <div class="h-40 bg-gradient-to-r from-primary via-blue-500 to-purple-600 relative">
            <div class="absolute inset-0 bg-black/20"></div>
            <!-- Patrón decorativo -->
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        

        <div class="px-8 pb-8 flex flex-col md:flex-row items-start gap-6 -mt-16 relative">
             <!-- Avatar -->
             <div class="relative group">
                <div id="profile-avatar" class="w-32 h-32 rounded-2xl border-4 border-surface bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xl shadow-black/50 ring-4 ring-primary/20">
                    <?php if (!empty($profileUser['foto_perfil']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $profileUser['foto_perfil'])): ?>
                        <?php $pf = htmlspecialchars($profileUser['foto_perfil']); $ver = filemtime(__DIR__ . '/../../public/uploads/profiles/' . $profileUser['foto_perfil']); ?>
                        <img src="public/uploads/profiles/<?= $pf ?>?v=<?= $ver ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-5xl font-bold text-white"><?= strtoupper(substr($profileUser['nombre'], 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Insignia de verificación -->
                <?php if($profileUser['estado_verificacion'] == 2): ?>
                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center border-4 border-surface shadow-lg">
                    <i class="fas fa-check text-white text-sm"></i>
                </div>
                <?php endif; ?>
                
                <?php if ($isOwnProfile): ?>
                <button onclick="document.getElementById('photo-input').click()" class="absolute bottom-0 right-0 bg-primary text-secondary p-2.5 rounded-full border-2 border-surface hover:bg-primary-dark transition-colors cursor-pointer shadow-lg opacity-0 group-hover:opacity-100" title="Cambiar foto">
                    <i class="fas fa-camera text-sm"></i>
                </button>
                <?php endif; ?>
             </div>
             
             <!-- Información del usuario -->
             <div class="flex-1 pt-6">
                 <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                     <div>
                         <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($profileUser['nombre']) ?></h1>
                         <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400">
                             <span class="flex items-center gap-2">
                                 <i class="fas fa-map-marker-alt text-primary"></i> 
                                 <?= htmlspecialchars($profileUser['ciudad'] ?? 'Sin localidad') ?>
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
                     
                     <!-- Acciones según tipo de perfil -->
                     <?php if (!$isOwnProfile): ?>
                     <div class="flex gap-2">
                         <a href="chat.php?user_id=<?= $profileUser['idUsuario'] ?>" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-colors shadow-lg shadow-primary/20 flex items-center gap-2">
                             <i class="fas fa-comment-alt"></i> Contactar
                         </a>
                     </div>
                     <?php endif; ?>
                 </div>
                 
                 <!-- Estadísticas en cards -->
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                     <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-star text-yellow-500 text-sm"></i>
                             <span class="text-xs text-gray-400">Valoración</span>
                         </div>
                         <p class="text-xl font-bold text-white">
                             <?= number_format($userStats['valoracion_promedio'] ?? 0, 1) ?>
                         </p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-route text-blue-400 text-sm"></i>
                             <span class="text-xs text-gray-400">Viajes</span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['total_viajes'] ?></p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-steering-wheel text-primary text-sm"></i>
                             <span class="text-xs text-gray-400">Conductor</span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['viajes_como_conductor'] ?></p>
                     </div>
                     
                     <div class="bg-gray-800/50 rounded-xl p-3 border border-gray-700/50">
                         <div class="flex items-center gap-2 mb-1">
                             <i class="fas fa-users text-purple-400 text-sm"></i>
                             <span class="text-xs text-gray-400">Pasajero</span>
                         </div>
                         <p class="text-xl font-bold text-white"><?= $userStats['viajes_como_pasajero'] ?></p>
                     </div>
                 </div>
             </div>
        </div>
        
        <!-- Navegación de pestañas -->
        <?php if ($isOwnProfile): ?>
        <div class="px-8 pb-6">
            <div class="flex gap-2 overflow-x-auto border-t border-gray-700 pt-4">
                <button onclick="switchTab('profile')" id="tab-profile" class="px-4 py-2 rounded-lg bg-primary/10 text-primary font-medium border border-primary/20 whitespace-nowrap transition-colors">
                    <i class="fas fa-user mr-2"></i>Perfil
                </button>
                <button onclick="switchTab('security')" id="tab-security" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-lock mr-2"></i>Seguridad
                </button>
                <button onclick="switchTab('privacy')" id="tab-privacy" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-shield-alt mr-2"></i>Privacidad
                </button>
                <button onclick="switchTab('verification')" id="tab-verification" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border border-transparent">
                    <i class="fas fa-check-circle mr-2"></i>Verificación
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
                        <i class="fas fa-star text-yellow-400"></i> Valoraciones
                    </h3>
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <div class="text-3xl font-bold text-white"><?= number_format($userStats['valoracion_promedio'] ?? 0, 1) ?></div>
                            <div class="text-sm text-gray-400">(Media basada en <?= count($ratings ?? []) ?> valoraciones)</div>
                        </div>
                    </div>

                    <?php if (!$isOwnProfile && isset($_SESSION['user_id'])): ?>
                        <form id="rating-form" class="mb-4">
                            <input type="hidden" name="idValorado" value="<?= $profileUser['idUsuario'] ?>">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-400">Tu valoración:</label>
                                <select name="puntuacion" id="rating-select" class="bg-gray-800 border border-gray-600 rounded-xl px-3 py-2 text-white text-sm">
                                    <option value="5">5 — Excelente</option>
                                    <option value="4">4 — Muy buena</option>
                                    <option value="3">3 — Correcta</option>
                                    <option value="2">2 — Mejorable</option>
                                    <option value="1">1 — Mala</option>
                                </select>
                                <button type="submit" class="ml-3 px-4 py-2 bg-primary text-secondary rounded-xl font-bold">Valorar</button>
                            </div>
                            <div id="rating-msg" class="text-sm text-yellow-300 mt-2"></div>
                        </form>
                    <?php endif; ?>

                    <div class="space-y-3">
                        <?php if (empty($ratings)): ?>
                            <p class="text-sm text-gray-400">Aún no hay valoraciones para este usuario.</p>
                        <?php else: ?>
                            <?php foreach ($ratings as $rv): ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-800 flex-shrink-0">
                                        <?php if (!empty($rv['valoradorFoto']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $rv['valoradorFoto'])): ?>
                                            <?php $vpf = htmlspecialchars($rv['valoradorFoto']); $vver = filemtime(__DIR__ . '/../../public/uploads/profiles/' . $rv['valoradorFoto']); ?>
                                            <img src="public/uploads/profiles/<?= $vpf ?>?v=<?= $vver ?>" class="w-full h-full object-cover" alt="">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-xs text-white font-bold bg-gradient-to-tr from-gray-700 to-gray-600"><?= strtoupper(substr($rv['valoradorNombre'], 0, 2)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white"><?= htmlspecialchars($rv['valoradorNombre']) ?></div>
                                        <div class="text-xs text-gray-400">Puntuación: <span class="text-yellow-400 font-semibold"><?= (int)$rv['puntuacion'] ?></span></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
     <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
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
                     
                     <?php if (!$isOwnProfile && !empty($profileUser['telefono'])): ?>
                     <div>
                         <p class="text-xs text-gray-500">Contacto</p>
                         <p class="text-white mt-1"><?= htmlspecialchars($profileUser['telefono']) ?></p>
                     </div>
                     <?php endif; ?>
                 </div>
            </div>

            <!-- Sobre mí -->
             <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                 <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                     <i class="fas fa-quote-left text-primary"></i> Sobre mí
                 </h3>
                 <p class="text-gray-400 text-sm leading-relaxed italic">
                     <?= !empty($profileUser['biografia']) ? nl2br(htmlspecialchars($profileUser['biografia'])) : 'Este usuario no ha escrito nada sobre sí mismo aún.' ?>
                 </p>
             </div>
        </div>

        <!-- Columna derecha -->
        <div class="lg:col-span-2">
            
            <?php if ($isOwnProfile): ?>
                
                <!-- Perfil -->
                <div id="content-profile" class="bg-surface rounded-2xl border border-gray-700 p-8">
                     <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                         <i class="fas fa-user-edit text-primary"></i> Editar Perfil
                     </h3>
                     <form action="profile.php?action=update" method="POST" enctype="multipart/form-data">
                         <input type="file" name="foto_perfil" id="photo-input" class="hidden" accept="image/*">
                         
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                         </div>
                         <div class="flex justify-end">
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                                 <i class="fas fa-save"></i> Guardar Cambios
                             </button>
                         </div>
                     </form>
                </div>

                <!-- Seguridad -->
                <div id="content-security" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
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
                                    case 'password_mismatch':
                                        echo 'Las nuevas contraseñas no coinciden.';
                                        break;
                                    case 'password_too_short':
                                        echo 'La nueva contraseña debe tener al menos 8 caracteres.';
                                        break;
                                    case 'empty_fields':
                                        echo 'Debes rellenar todos los campos.';
                                        break;
                                    default:
                                        echo 'Ha ocurrido un error.';
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success && $tab === 'security'): ?>
                        <div class="mb-6 rounded-xl border border-green-500 bg-green-500/10 px-4 py-3 text-sm text-green-400">
                            Contraseña actualizada correctamente.
                        </div>
                    <?php endif; ?>
                    <form action="profile.php?action=change_password" method="POST" class="max-w-md">
                        <div class="space-y-6 mb-8">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Contraseña Actual</label>
                                <input type="password" name="current_password" required class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Nueva Contraseña</label>
                                <input type="password" name="new_password" required class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
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
                            
                            <form action="profile.php?action=verify" method="POST" enctype="multipart/form-data" class="bg-gray-800/50 p-6 rounded-xl border border-dashed border-gray-600 text-center">
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
                    <form action="profile.php?action=update_privacy" method="POST">
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
                </div>

            <?php else: ?>
                <!-- Actividad reciente del usuario -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-8">
                     <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                         <i class="fas fa-clock text-primary"></i> Actividad Reciente
                     </h3>
                     <div class="space-y-4">
                         <div class="text-center py-12 text-gray-500">
                             <i class="fas fa-eye-slash text-4xl mb-3"></i>
                             <p>La actividad de este usuario es privada</p>
                         </div>
                     </div>
                </div>
            <?php endif; ?>

        </div>
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
</script>

<?php if (!$isOwnProfile && isset($_SESSION['user_id'])): ?>

<script>
    document.getElementById('rating-form')?.addEventListener('submit', function(e){
        e.preventDefault();
        const form = e.currentTarget;
        const data = new FormData(form);

        fetch('rating.php', { method: 'POST', body: data })
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

</body>
</html>
