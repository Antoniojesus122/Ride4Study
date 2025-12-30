<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Encabezado del perfil -->
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-xl overflow-hidden mb-8">
        <div class="h-32 bg-gradient-to-r from-primary to-purple-600 relative">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>
        <div class="px-8 pb-8 flex flex-col md:flex-row items-end -mt-12 gap-6 relative">
             <div class="relative group">
                <div class="w-32 h-32 rounded-full border-4 border-surface bg-gray-800 flex items-center justify-center overflow-hidden">
                    <?php if (!empty($profileUser['foto_perfil'])): ?>
                        <img src="public/uploads/profiles/<?= htmlspecialchars($profileUser['foto_perfil']) ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-4xl font-bold text-white"><?= strtoupper(substr($profileUser['nombre'], 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($isOwnProfile): ?>
                <button onclick="document.getElementById('photo-input').click()" class="absolute bottom-0 right-0 bg-gray-900 text-white p-2 rounded-full border border-gray-700 hover:bg-primary transition-colors cursor-pointer shadow-lg" title="Cambiar foto">
                    <i class="fas fa-camera text-sm"></i>
                </button>
                <?php endif; ?>
             </div>
             
             <div class="flex-1 mb-2">
                 <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars($profileUser['nombre']) ?></h1>
                 <p class="text-gray-400 flex items-center gap-2">
                     <i class="fas fa-map-marker-alt text-gray-500"></i> <?= htmlspecialchars($profileUser['ciudad'] ?? 'Sin localidad') ?>
                     <span class="mx-1">•</span>
                     <i class="fas fa-university text-gray-500"></i> <?= htmlspecialchars($profileUser['institucion'] ?? 'Sin institución') ?>
                 </p>
             </div>

             <?php if (!$isOwnProfile): ?>
                <a href="chat.php?user_id=<?= $profileUser['idUsuario'] ?>" class="mb-4 px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-colors shadow-lg shadow-primary/20">
                    <i class="fas fa-comment-alt mr-2"></i> Contactar
                </a>
             <?php else: ?>
                 <!-- Navegación de pestañas -->
                 <div class="flex gap-2 mb-2 w-full md:w-auto overflow-x-auto">
                     <button onclick="switchTab('profile')" id="tab-profile" class="px-4 py-2 rounded-lg bg-gray-800 text-white font-medium hover:bg-gray-700 transition-colors border border-gray-700">Perfil</button>
                     <button onclick="switchTab('security')" id="tab-security" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors border border-transparent">Seguridad</button>
                     <button onclick="switchTab('privacy')" id="tab-privacy" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors border border-transparent">Privacidad</button>
                     <button onclick="switchTab('verification')" id="tab-verification" class="px-4 py-2 rounded-lg bg-transparent text-gray-400 font-medium hover:text-white hover:bg-gray-800 transition-colors border border-transparent">Verificación</button>
                 </div>
             <?php endif; ?>
        </div>
    </div>

    <!-- Contenido principal -->
     <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
        <!-- Información del perfil -->
        <div class="space-y-6">
            <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                 <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Información</h3>
                 <div class="space-y-4">
                     <div>
                         <p class="text-xs text-gray-500">Estado de verificación</p>
                         <div class="mt-1">
                             <?php if($profileUser['estado_verificacion'] == 2): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                    <i class="fas fa-check-circle"></i> Verificado
                                </span>
                             <?php elseif($profileUser['estado_verificacion'] == 1): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                             <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400 border border-gray-500/20">
                                    No verificado
                                </span>
                             <?php endif; ?>
                         </div>
                     </div>
                     <div>
                         <p class="text-xs text-gray-500">Vehículo</p>
                         <p class="text-white"><?= htmlspecialchars($profileUser['vehiculo'] ?? 'No especificado') ?></p>
                     </div>
                     <div>
                         <p class="text-xs text-gray-500">Miembro desde</p>
                         <p class="text-white">Octubre 2025</p>
                     </div>
                 </div>
            </div>

            <!-- Sobre mí -->
             <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                 <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Sobre mí</h3>
                 <p class="text-gray-400 text-sm leading-relaxed italic">
                     <?= !empty($profileUser['biografia']) ? nl2br(htmlspecialchars($profileUser['biografia'])) : 'Este usuario no ha escrito nada sobre sí mismo aún.' ?>
                 </p>
             </div>
        </div>

        <!-- Formulario de edición -->
        <div class="lg:col-span-2">
            
            <?php if ($isOwnProfile): ?>
                
                <!-- Perfil -->
                <div id="content-profile" class="bg-surface rounded-2xl border border-gray-700 p-8">
                     <h3 class="text-xl font-bold text-white mb-6">Editar Perfil</h3>
                     <form action="profile.php?action=update" method="POST" enctype="multipart/form-data">
                         <!-- Input oculto para la foto de perfil -->
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
                                 <textarea name="biografia" rows="4" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm resize-none"><?= htmlspecialchars($profileUser['biografia'] ?? '') ?></textarea>
                             </div>
                         </div>
                         <div class="flex justify-end">
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                                 Guardar Cambios
                             </button>
                         </div>
                     </form>
                </div>

                <!-- Seguridad -->
                <div id="content-security" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
                    <h3 class="text-xl font-bold text-white mb-6">Cambiar Contraseña</h3>
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
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                                 Actualizar Contraseña
                             </button>
                         </div>
                    </form>
                </div>

                <!-- Verificación -->
                <div id="content-verification" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
                     <h3 class="text-xl font-bold text-white mb-4">Verificación de Estudiante</h3>
                     
                     <?php if ($profileUser['estado_verificacion'] == 2): ?>
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-6 text-center">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                            <h4 class="text-lg font-bold text-green-500">¡Tu cuenta está verificada!</h4>
                            <p class="text-gray-400 mt-2 text-sm">Ya puedes disfrutar de todas las ventajas de ser un estudiante verificado.</p>
                        </div>
                     <?php elseif ($profileUser['estado_verificacion'] == 1): ?>
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-6 text-center">
                            <i class="fas fa-clock text-4xl text-yellow-500 mb-3"></i>
                            <h4 class="text-lg font-bold text-yellow-500">Solicitud en revisión</h4>
                            <p class="text-gray-400 mt-2 text-sm">Estamos revisando tus documentos. Te notificaremos pronto.</p>
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
                                <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                                    Enviar Documentación
                                </button>
                            </form>
                        </div>
                     <?php endif; ?>
                </div>

                <!-- Privacidad -->
                <div id="content-privacy" class="hidden bg-surface rounded-2xl border border-gray-700 p-8">
                    <h3 class="text-xl font-bold text-white mb-6">Privacidad y Configuración</h3>
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
                             <button type="submit" class="px-6 py-3 bg-primary text-secondary font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                                 Guardar Preferencias
                             </button>
                         </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- Información privada protegida -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-8 h-full flex flex-col items-center justify-center text-center opacity-50">
                     <i class="fas fa-user-lock text-4xl text-gray-600 mb-4"></i>
                     <p class="text-gray-400">La información privada de este usuario está protegida.</p>
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
        btn.classList.remove('bg-gray-800', 'text-white', 'border-gray-700');
        btn.classList.add('bg-transparent', 'text-gray-400', 'border-transparent');
    });

    document.getElementById('content-' + tabName).classList.remove('hidden');
    const activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.classList.remove('bg-transparent', 'text-gray-400', 'border-transparent');
    activeBtn.classList.add('bg-gray-800', 'text-white', 'border-gray-700');
}

const urlParams = new URLSearchParams(window.location.search);
const tab = urlParams.get('tab');
if (tab) {
    switchTab(tab);
}
</script>

</body>
</html>
