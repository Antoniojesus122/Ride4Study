<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-2xl overflow-hidden relative">
        <!-- Alertas de error -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-400 p-4 mb-0" role="alert">
                <p class="font-bold">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white">Publicar un viaje</h2>
                <p class="text-gray-400 mt-2">Rellena los detalles para encontrar compañeros de viaje.</p>
            </div>

            <form action="publish.php" method="POST" id="publishForm" class="space-y-8">
                
                <!-- Sección 1: Ruta -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-primary border-b border-gray-700 pb-2 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt"></i> Ruta y Horario
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400 mb-2">¿Eres conductor o pasajero?</label>
                            <div class="flex gap-4">
                                <label class="flex-1">
                                    <input type="radio" name="tipo" value="ofrezco" class="peer hidden" checked>
                                    <div class="p-4 rounded-xl border border-gray-600 cursor-pointer peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-white text-gray-400 transition-all text-center hover:bg-gray-800">
                                        <i class="fas fa-car text-xl mb-2"></i>
                                        <div class="font-bold">Llevo coche</div>
                                    </div>
                                </label>
                                <label class="flex-1">
                                    <input type="radio" name="tipo" value="busco" class="peer hidden">
                                    <div class="p-4 rounded-xl border border-gray-600 cursor-pointer peer-checked:border-purple-500 peer-checked:bg-purple-500/10 peer-checked:text-white text-gray-400 transition-all text-center hover:bg-gray-800">
                                        <i class="fas fa-walking text-xl mb-2"></i>
                                        <div class="font-bold">Busco plaza</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Origen -->
                        <div class="group relative">
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Origen</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-3.5 text-gray-500"></i>
                                <select name="origen" id="origen" required class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary appearance-none cursor-pointer">
                                    <option value="">Selecciona origen...</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['idLocalidad'] ?>" <?= (isset($_POST['origen']) && $_POST['origen'] == $loc['idLocalidad']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($loc['nombreLocalidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-4 text-xs text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Destino -->
                        <div class="group relative">
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Destino</label>
                            <div class="relative">
                                <i class="fas fa-flag-checkered absolute left-3 top-3.5 text-gray-500"></i>
                                <select name="destino" id="destino" required class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary appearance-none cursor-pointer">
                                    <option value="">Selecciona destino...</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['idLocalidad'] ?>" <?= (isset($_POST['destino']) && $_POST['destino'] == $loc['idLocalidad']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($loc['nombreLocalidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-4 text-xs text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Fecha de salida</label>
                            <div class="relative">
                                <i class="far fa-calendar-alt absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="date" name="fechaSalida" id="fechaSalida" required min="<?= date('Y-m-d') ?>" 
                                       value="<?= $_POST['fechaSalida'] ?? '' ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                            </div>
                        </div>

                        <!-- Hora Salida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Hora de salida</label>
                            <div class="relative">
                                <i class="far fa-clock absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="time" name="horaSalida" id="horaSalida" required 
                                       value="<?= $_POST['horaSalida'] ?? '' ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                            </div>
                        </div>

                        <!-- Hora Regreso (Opcional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Hora de regreso (Opcional)</label>
                            <div class="relative">
                                <i class="fas fa-history absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="time" name="horaRegreso" id="horaRegreso" 
                                       value="<?= $_POST['horaRegreso'] ?? '' ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Solo si haces viaje de vuelta el mismo día.</p>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Detalles del Viaje -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-primary border-b border-gray-700 pb-2 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Detalles del Viaje
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Plazas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Plazas disponibles</label>
                            <div class="relative">
                                <i class="fas fa-chair absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="number" name="plazasDisponibles" min="1" max="8" required 
                                       value="<?= $_POST['plazasDisponibles'] ?? '1' ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <!-- Precio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Precio por plaza (€)</label>
                            <div class="relative text-white">
                                <i class="fas fa-euro-sign absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="number" name="precio" min="0" step="0.50" 
                                       value="<?= $_POST['precio'] ?? '' ?>"
                                       placeholder="Ej: 5.00 (Opcional)"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Comentarios adicionales</label>
                            <textarea name="descripcion" rows="3" 
                                      placeholder="Punto de encuentro exacto, tamaño de equipaje permitido, si aceptas mascotas..."
                                      class="block w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary resize-none placeholder-gray-500"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-700 flex items-center justify-end gap-4">
                    <a href="dashboard.php" class="px-6 py-3 rounded-xl border border-gray-600 text-gray-300 hover:text-white hover:bg-gray-800 transition-all font-medium">Cancelar</a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                        Publicar Viaje <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('publishForm').addEventListener('submit', function(e) {
    const origen = document.getElementById('origen').value;
    const destino = document.getElementById('destino').value;
    const fecha = document.getElementById('fechaSalida').value;
    const horaSalida = document.getElementById('horaSalida').value;
    const horaRegreso = document.getElementById('horaRegreso').value;
    
    // Validación Origen y Destino
    if (origen === destino) {
        e.preventDefault();
        alert('El origen y el destino no pueden ser el mismo.');
        return;
    }

    // Validación hora de regreso
    if (horaRegreso && horaRegreso <= horaSalida) {
        e.preventDefault();
        alert('La hora de regreso debe ser posterior a la hora de salida.');
        return;
    }

    // Validación fecha de salida
    const today = new Date().toISOString().split('T')[0];
    if (fecha < today) {
        e.preventDefault();
        alert('La fecha de salida no puede ser en el pasado.');
        return;
    }
});
</script>
</body>
</html>
