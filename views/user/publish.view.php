<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$isEdit = isset($ride); // Verificar si se está en modo de edición 
$formAction = $isEdit ? url('/edit-ride') : url('/publish');
$submitText = $isEdit ? 'Guardar Cambios' : 'Publicar Viaje';
$pageTitle = $isEdit ? 'Editar Viaje' : 'Publicar un viaje';
$pageDesc = $isEdit ? 'Modifica los detalles de tu viaje.' : 'Rellena los detalles para encontrar compañeros de viaje.';

function getVal($field, $ride, $post, $default = '') {
    if (isset($post[$field])) return $post[$field];
    if (isset($ride[$field])) return $ride[$field];
    return $default;
}
?>
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
                <h2 class="text-3xl font-bold text-white"><?= $pageTitle ?></h2>
                <p class="text-gray-400 mt-2"><?= $pageDesc ?></p>
            </div>

            <form action="<?= $formAction ?>" method="POST" id="publishForm" class="space-y-8">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
                <?php endif; ?>
                
                <!-- Sección 1: Ruta -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-primary border-b border-gray-700 pb-2 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt"></i> Ruta y Horario
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400 mb-2">¿Eres conductor o pasajero?</label>
                            <?php 
                                $currentType = isset($_POST['tipo']) ? $_POST['tipo'] : ($isEdit ? strtolower($ride['tipo']) : 'ofrezco');
                            ?>
                            
                            <?php if ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers): ?>
                                <!-- Mensaje informativo cuando hay pasajeros confirmados -->
                                <div class="p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl mb-3">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-lock text-yellow-500 mt-0.5"></i>
                                        <div>
                                            <p class="text-yellow-400 text-sm font-medium">No puedes cambiar el tipo de viaje</p>
                                            <p class="text-yellow-300/70 text-xs mt-1">Ya hay pasajeros con reserva confirmada en este viaje.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex gap-4">
                                <label class="flex-1">
                                    <input type="radio" name="tipo" value="ofrezco" class="peer hidden" 
                                           <?= $currentType == 'ofrezco' ? 'checked' : '' ?>
                                           <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'disabled' : '' ?>>
                                    <div class="p-4 rounded-xl border border-gray-600 <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-800' ?> peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-white text-gray-400 transition-all text-center">
                                        <i class="fas fa-car text-xl mb-2"></i>
                                        <div class="font-bold">Llevo coche</div>
                                    </div>
                                </label>
                                <label class="flex-1">
                                    <input type="radio" name="tipo" value="busco" class="peer hidden" 
                                           <?= $currentType == 'busco' ? 'checked' : '' ?>
                                           <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'disabled' : '' ?>>
                                    <div class="p-4 rounded-xl border border-gray-600 <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-800' ?> peer-checked:border-purple-500 peer-checked:bg-purple-500/10 peer-checked:text-white text-gray-400 transition-all text-center">
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
                                    <?php 
                                    $selOrigen = getVal('origen', $isEdit ? $ride : [], $_POST);
                                    foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['idLocalidad'] ?>" <?= $selOrigen == $loc['idLocalidad'] ? 'selected' : '' ?>>
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
                                    <?php 
                                    $selDestino = getVal('destino', $isEdit ? $ride : [], $_POST);
                                    foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['idLocalidad'] ?>" <?= $selDestino == $loc['idLocalidad'] ? 'selected' : '' ?>>
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
                                       value="<?= getVal('fechaSalida', $isEdit ? $ride : [], $_POST) ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                            </div>
                        </div>

                        <!-- Hora Salida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Hora de salida</label>
                            <div class="relative">
                                <i class="far fa-clock absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="time" name="horaSalida" id="horaSalida" required 
                                       value="<?= getVal('horaSalida', $isEdit ? $ride : [], $_POST) ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                            </div>
                        </div>

                        <!-- Hora Regreso (Opcional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Hora de regreso (Opcional)</label>
                            <div class="relative">
                                <i class="fas fa-history absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="time" name="horaRegreso" id="horaRegreso" 
                                       value="<?= getVal('horaRegreso', $isEdit ? $ride : [], $_POST) ?>"
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
                                       value="<?= getVal('plazasDisponibles', $isEdit ? $ride : [], $_POST, '1') ?>"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <!-- Precio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Precio por plaza (€)</label>
                            <div class="relative text-white">
                                <i class="fas fa-euro-sign absolute left-3 top-3.5 text-gray-500"></i>
                                <input type="number" name="precio" min="0" step="0.50" 
                                       value="<?= getVal('precio', $isEdit ? $ride : [], $_POST) ?>"
                                       placeholder="Ej: 5.00 (Opcional)"
                                       class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Comentarios adicionales</label>
                            <textarea name="descripcion" rows="3" 
                                      placeholder="Punto de encuentro exacto, tamaño de equipaje permitido, si aceptas mascotas..."
                                      class="block w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary resize-none placeholder-gray-500"><?= htmlspecialchars(getVal('descripcion', $isEdit ? $ride : [], $_POST)) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-700 flex items-center justify-end gap-4">
                    <a href="<?= url('/dashboard') ?>" class="px-6 py-3 rounded-xl border border-gray-600 text-gray-300 hover:text-white hover:bg-gray-800 transition-all font-medium">Cancelar</a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                        <?= $submitText ?> <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const typeRadios = document.querySelectorAll('input[name="tipo"]');
    const seatsInput = document.querySelector('input[name="plazasDisponibles"]');
    const priceInput = document.querySelector('input[name="precio"]');
    const seatsContainer = seatsInput.closest('div').parentElement;
    const priceContainer = priceInput.closest('div').parentElement;

    function toggleInputs() {
        const selectedType = document.querySelector('input[name="tipo"]:checked').value;
        
        if (selectedType === 'busco') {
            // Campos deshabilitados para tipo busco transporte
            seatsInput.disabled = true;
            seatsInput.value = '1'; // Valor por defecto para que no falle la validación
            priceInput.disabled = true;
            priceInput.value = '';
            
            // Estilo visual para los campos deshabilitados
            seatsInput.classList.add('opacity-50', 'cursor-not-allowed');
            priceInput.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Quitar el required de plazas cuando es tipo busco
            seatsInput.removeAttribute('required');
        } else {
            // Campos habilitados
            seatsInput.disabled = false;
            priceInput.disabled = false;
            
            seatsInput.classList.remove('opacity-50', 'cursor-not-allowed');
            priceInput.classList.remove('opacity-50', 'cursor-not-allowed');
            
            // Restaurar el required de plazas
            seatsInput.setAttribute('required', 'required');
            
            // Si estaba vacío, poner valor por defecto
            if (!seatsInput.value) {
                seatsInput.value = '1';
            }
        }
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleInputs);
    });

    toggleInputs();
});

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

    // Validación fecha de salida
    const today = new Date().toISOString().split('T')[0];
    if (fecha < today) {
        e.preventDefault();
        alert('La fecha de salida no puede ser en el pasado.');
        return;
    }
    
    // Validación hora de salida para viajes del mismo día
    if (fecha === today) {
        const ahora = new Date();
        const horaActual = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');
        
        if (horaSalida <= horaActual) {
            e.preventDefault();
            alert('Para viajes del mismo día, la hora de salida debe ser posterior a la hora actual.');
            return;
        }
    }

    // Validación hora de regreso
    if (horaRegreso && horaRegreso <= horaSalida) {
        e.preventDefault();
        alert('La hora de regreso debe ser posterior a la hora de salida.');
        return;
    }
});
</script>
</body>
</html>
