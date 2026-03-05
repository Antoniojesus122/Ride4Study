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
                    <form action="profile.php?action=change_password" method="POST" class="max-w-md">
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
                                        'horaRegreso'         => $ar['horaRegreso'] ?? null,
                                        'descripcion'         => null,
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


<?php if (!$isOwnProfile && !empty($activeRides)): ?>
<!-- Modal de detalles del anuncio -->
<div id="profile-ride-modal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="profile-modal-backdrop"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-surface text-left shadow-2xl transition-all duration-300 sm:my-8 sm:w-full sm:max-w-2xl border border-gray-700 opacity-0 translate-y-4 sm:scale-95" id="profile-modal-panel">

                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <i class="fas fa-route text-primary text-sm"></i>
                        </div>
                        <h3 class="text-base font-semibold text-white">Detalles del Anuncio</h3>
                    </div>
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-all" onclick="closeProfileRideModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="px-5 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Info del viaje -->
                        <div class="md:col-span-2 space-y-5">
                            <div class="flex items-center justify-between">
                                <span id="prm-tipo-badge" class="px-3 py-1 rounded-full text-xs font-semibold border"></span>
                                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt text-gray-500"></i>
                                    <span id="prm-fecha">—</span>
                                </span>
                            </div>

                            <!-- Ruta -->
                            <div class="relative pl-7 space-y-5">
                                <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-gray-700"></div>
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-primary bg-surface z-10"></div>
                                    <div>
                                        <p class="text-base font-bold text-white" id="prm-origin"></p>
                                        <p class="text-xs text-primary font-mono mt-0.5" id="prm-time-start"></p>
                                    </div>
                                </div>
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-gray-500 bg-surface z-10"></div>
                                    <div>
                                        <p class="text-base font-bold text-white" id="prm-dest"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" id="prm-time-end"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Precio y plazas -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-800 rounded-xl p-3.5 border border-gray-700" id="prm-price-container">
                                    <p class="text-xs text-gray-400 mb-1">Precio por plaza</p>
                                    <p class="text-xl font-bold text-primary" id="prm-price"></p>
                                </div>
                                <div class="bg-gray-800 rounded-xl p-3.5 border border-gray-700">
                                    <p class="text-xs text-gray-400 mb-1">Plazas disponibles</p>
                                    <p class="text-xl font-bold text-white flex items-center gap-2">
                                        <span id="prm-seats"></span>
                                        <i class="fas fa-chair text-sm text-gray-500"></i>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Info del usuario -->
                        <div class="md:col-span-1">
                            <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 h-full flex flex-col">
                                <div class="text-center mb-4">
                                    <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700" id="prm-avatar"></div>
                                    <h4 class="font-bold text-white truncate" id="prm-driver-name"></h4>
                                    <div class="flex items-center justify-center mt-2">
                                        <span class="bg-yellow-500/10 text-yellow-500 px-2.5 py-1 rounded-full border border-yellow-500/20 flex items-center gap-1.5 text-xs font-semibold">
                                            <i class="fas fa-star text-xs"></i>
                                            <span id="prm-rating"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2.5 pt-4 border-t border-gray-700 flex-1">
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <i class="fas fa-shield-alt w-4 text-center" id="prm-verified-icon"></i>
                                        <span id="prm-verified" class="text-sm"></span>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-700">
                                    <a href="profile.php?id=<?= (int)$profileUser['idUsuario'] ?>" class="w-full flex justify-center items-center gap-2 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-lg py-2 text-sm font-medium transition-all">
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
                            onclick="closeProfileRideModal()">
                        Cerrar
                    </button>
                    <a href="#" id="prm-btn-contact"
                       class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-600 bg-gray-800 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-comment-alt text-xs"></i> Contactar
                    </a>
                    <button type="button" id="prm-btn-reserve"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-ticket-alt text-xs"></i> Solicitar Plaza
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

    const prmBtnStyles = {
        active:   'w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary text-secondary text-sm font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2',
        disabled: 'w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-700 bg-gray-800 text-sm font-bold text-gray-500 cursor-not-allowed flex items-center justify-center gap-2',
    };

    function openProfileRideModal(ride) {
        const btnReserve = document.getElementById('prm-btn-reserve');

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
            ? new Date(ride.fechaSalida).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
            : '—';

        // Ruta
        document.getElementById('prm-origin').textContent     = ride.nombreOrigen;
        document.getElementById('prm-dest').textContent       = ride.nombreDestino;
        document.getElementById('prm-time-start').textContent = ride.horaSalida.substring(0, 5);
        document.getElementById('prm-time-end').textContent   = ride.horaRegreso
            ? 'Regreso: ' + ride.horaRegreso.substring(0, 5)
            : 'Llegada aprox.';

        // Precio y plazas
        const priceEl        = document.getElementById('prm-price');
        const priceContainer = document.getElementById('prm-price-container');
        if (ride.tipo.toLowerCase() === 'ofrezco' && ride.precio != null) {
            priceEl.textContent = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(ride.precio);
            priceContainer.style.display = '';
        } else {
            priceContainer.style.display = 'none';
        }
        document.getElementById('prm-seats').textContent = ride.plazasDisponibles ?? '—';

        // Avatar
        const avatarEl = document.getElementById('prm-avatar');
        if (ride.foto_perfil) {
            avatarEl.innerHTML = `<img src="public/uploads/profiles/${encodeURIComponent(ride.foto_perfil)}" alt="avatar" class="w-full h-full object-cover">`;
            avatarEl.className = 'w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg overflow-hidden bg-gradient-to-br from-gray-600 to-gray-700';
        } else {
            avatarEl.innerHTML = ride.nombreUsuario.substring(0, 2).toUpperCase();
            avatarEl.className = 'w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-secondary shadow-lg bg-gradient-to-br from-primary to-primary-dark';
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

        // Contactar
        document.getElementById('prm-btn-contact').href = 'chat.php?anuncio_id=' + ride.idAnuncio + '&other_user_id=' + ride.idUsuario;

        // Botón de reserva
        btnReserve.onclick  = null;
        btnReserve.disabled = false;
        btnReserve.style.display = 'flex';

        if (ride.tipo.toLowerCase() === 'ofrezco' && ride.plazasDisponibles > 0) {
            btnReserve.className = prmBtnStyles.active;
            btnReserve.innerHTML = '<i class="fas fa-ticket-alt text-xs"></i> Solicitar Plaza';
            btnReserve.onclick   = () => { window.location.href = 'reserve.php?ride_id=' + ride.idAnuncio; };
        } else if (ride.tipo.toLowerCase() === 'ofrezco' && ride.plazasDisponibles <= 0) {
            btnReserve.className = prmBtnStyles.disabled;
            btnReserve.innerHTML = '<i class="fas fa-ban text-xs"></i> Viaje completo';
            btnReserve.disabled  = true;
        } else {
            // Tipo "busco" — solo contactar
            btnReserve.style.display = 'none';
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
