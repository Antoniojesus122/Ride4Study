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

// Valores de localización para el autocompletado
// Si se está editando el anuncio, las localidades se cargan desde $origenLoc y $destinoLoc
$preOrigen  = $_POST['origen_nombre'] ?? ($isEdit && isset($origenLoc) ? $origenLoc['nombreLocalidad'] : '');
$preOrigenLat = $_POST['origen_lat'] ?? ($isEdit && isset($origenLoc) ? ($origenLoc['lat'] ?? '') : '');
$preOrigenLng = $_POST['origen_lng'] ?? ($isEdit && isset($origenLoc) ? ($origenLoc['lng'] ?? '') : '');
$preDestino  = $_POST['destino_nombre'] ?? ($isEdit && isset($destinoLoc) ? $destinoLoc['nombreLocalidad'] : '');
$preDestinoLat = $_POST['destino_lat'] ?? ($isEdit && isset($destinoLoc) ? ($destinoLoc['lat'] ?? '') : '');
$preDestinoLng = $_POST['destino_lng'] ?? ($isEdit && isset($destinoLoc) ? ($destinoLoc['lng'] ?? '') : '');
?>

<body>
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

                            <!-- Origen (Autocompletado) -->
                            <div class="group relative">
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">Origen</label>
                                <div class="relative">
                                    <i class="fas fa-map-marker-alt absolute left-3 top-3.5 text-gray-500 z-10"></i>
                                    <input type="text" id="origen" autocomplete="off" required
                                        value="<?= htmlspecialchars($preOrigen) ?>"
                                        placeholder="Escribe una ciudad..."
                                        class="block w-full pl-10 pr-10 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500 outline-none">
                                    <!-- Spinner de carga -->
                                    <div id="origen-spinner" class="hidden absolute right-3 top-3.5">
                                        <i class="fas fa-spinner fa-spin text-gray-500 text-sm"></i>
                                    </div>
                                    <!-- Icono de check cuando se selecciona -->
                                    <div id="origen-check" class="<?= !empty($preOrigenLat) ? '' : 'hidden' ?> absolute right-3 top-3.5">
                                        <i class="fas fa-check-circle text-green-400 text-sm"></i>
                                    </div>
                                    <!-- Hidden inputs para enviar nombre + coordenadas -->
                                    <input type="hidden" name="origen_nombre" id="origen_nombre" value="<?= htmlspecialchars($preOrigen) ?>">
                                    <input type="hidden" name="origen_lat" id="origen_lat" value="<?= htmlspecialchars($preOrigenLat) ?>">
                                    <input type="hidden" name="origen_lng" id="origen_lng" value="<?= htmlspecialchars($preOrigenLng) ?>">
                                </div>
                                <!-- Dropdown de sugerencias -->
                                <div id="origen-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
                            </div>

                            <!-- Destino (Autocompletado) -->
                            <div class="group relative">
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">Destino</label>
                                <div class="relative">
                                    <i class="fas fa-flag-checkered absolute left-3 top-3.5 text-gray-500 z-10"></i>
                                    <input type="text" id="destino" autocomplete="off" required
                                        value="<?= htmlspecialchars($preDestino) ?>"
                                        placeholder="Escribe una ciudad..."
                                        class="block w-full pl-10 pr-10 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500 outline-none">
                                    <!-- Spinner de carga -->
                                    <div id="destino-spinner" class="hidden absolute right-3 top-3.5">
                                        <i class="fas fa-spinner fa-spin text-gray-500 text-sm"></i>
                                    </div>
                                    <!-- Icono de check cuando se selecciona -->
                                    <div id="destino-check" class="<?= !empty($preDestinoLat) ? '' : 'hidden' ?> absolute right-3 top-3.5">
                                        <i class="fas fa-check-circle text-green-400 text-sm"></i>
                                    </div>
                                    <!-- Hidden inputs para enviar nombre + coordenadas -->
                                    <input type="hidden" name="destino_nombre" id="destino_nombre" value="<?= htmlspecialchars($preDestino) ?>">
                                    <input type="hidden" name="destino_lat" id="destino_lat" value="<?= htmlspecialchars($preDestinoLat) ?>">
                                    <input type="hidden" name="destino_lng" id="destino_lng" value="<?= htmlspecialchars($preDestinoLng) ?>">
                                </div>
                                <!-- Dropdown de sugerencias -->
                                <div id="destino-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
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
        // Clase para manejar el autocompletado de ciudades con Nominatim
        // Solo cuidades y pueblos de España
        // Máximo 5 sugerencias y tiene un retardo de 300 milisegundos
        class CityAutocomplete {
            constructor(inputId) {
                this.input      = document.getElementById(inputId);
                this.dropdown   = document.getElementById(inputId + '-dropdown');
                this.spinner    = document.getElementById(inputId + '-spinner');
                this.check      = document.getElementById(inputId + '-check');
                this.hiddenName = document.getElementById(inputId + '_nombre');
                this.hiddenLat  = document.getElementById(inputId + '_lat');
                this.hiddenLng  = document.getElementById(inputId + '_lng');
                this.debounceTimer = null;
                this.selected   = false; // Indica si el usuario ha seleccionado una opción

                this._bindEvents();
            }

            // Vincular eventos al input
            _bindEvents() {
                // Al escribir: buscar con retardo
                this.input.addEventListener('input', () => {
                    this.selected = false;
                    this._clearHidden();
                    this.check.classList.add('hidden');

                    clearTimeout(this.debounceTimer);
                    const query = this.input.value.trim();

                    if (query.length < 2) {
                        this._hideDropdown();
                        return;
                    }

                    this.debounceTimer = setTimeout(() => this._search(query), 300);
                });

                // Cerrar dropdown al perder el foco
                this.input.addEventListener('blur', () => {
                    setTimeout(() => this._hideDropdown(), 200);
                });

                // Al hacer focus, si hay texto y no está seleccionado, buscar de nuevo
                this.input.addEventListener('focus', () => {
                    if (this.input.value.trim().length >= 2 && !this.selected) {
                        this._search(this.input.value.trim());
                    }
                });

                // Permitir navegación con teclado en el desplegable (bueno para la accesibilidad)
                this.input.addEventListener('keydown', (e) => {
                    if (this.dropdown.classList.contains('hidden')) return;

                    const items = this.dropdown.querySelectorAll('[data-city]');
                    const active = this.dropdown.querySelector('.bg-gray-700');
                    let index = Array.from(items).indexOf(active);

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (active) active.classList.remove('bg-gray-700');
                        index = (index + 1) % items.length;
                        items[index].classList.add('bg-gray-700');
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (active) active.classList.remove('bg-gray-700');
                        index = index <= 0 ? items.length - 1 : index - 1;
                        items[index].classList.add('bg-gray-700');
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (active) active.click();
                    } else if (e.key === 'Escape') {
                        this._hideDropdown();
                    }
                });
            }

            // Consultar la API de Nominatim
            async _search(query) {
                this.spinner.classList.remove('hidden');

                const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
                    format: 'json',
                    q: query,
                    countrycodes: 'es',
                    limit: '5',
                    addressdetails: '1',
                    'accept-language': 'es'
                });

                try {
                    const res = await fetch(url, {
                        headers: { 'User-Agent': 'Ride4Study/1.0' }
                    });
                    const data = await res.json();
                    this._renderResults(data);
                } catch (err) {
                    console.error('Error al buscar ciudades:', err);
                    this._hideDropdown();
                } finally {
                    this.spinner.classList.add('hidden');
                }
            }

            // Mostrar las sugerencias en el desplegable
            _renderResults(results) {
                this.dropdown.innerHTML = '';

                if (!results.length) {
                    this.dropdown.innerHTML = `
                        <div class="px-4 py-3 text-sm text-gray-500 flex items-center gap-2">
                            <i class="fas fa-search-minus"></i> No se encontraron resultados
                        </div>`;
                    this.dropdown.classList.remove('hidden');
                    return;
                }

                results.forEach(place => {
                    // Extraer solo el nombre de la ciudad (sin región ni país, que muchas veces la api devuelve dichos datos)
                    const cityName = this._extractCityName(place);
                    // Texto secundario: provincia / comunidad
                    const subtitle = this._extractSubtitle(place);

                    const item = document.createElement('div');
                    item.dataset.city = cityName;
                    item.dataset.lat  = place.lat;
                    item.dataset.lng  = place.lon;
                    item.className = 'px-4 py-3 cursor-pointer hover:bg-gray-700 transition-colors border-b border-gray-700/50 last:border-0';
                    item.innerHTML = `
                        <div class="flex items-center gap-3">
                            <i class="fas fa-map-pin text-primary text-sm"></i>
                            <div>
                                <p class="text-sm text-white font-medium">${this._escapeHtml(cityName)}</p>
                                ${subtitle ? `<p class="text-xs text-gray-500">${this._escapeHtml(subtitle)}</p>` : ''}
                            </div>
                        </div>`;

                    // Al hacer click, seleccionar esta ciudad
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault(); // Evitar que se cierre el desplegable antes del click
                        this._selectCity(cityName, place.lat, place.lon);
                    });

                    this.dropdown.appendChild(item);
                });

                this.dropdown.classList.remove('hidden');
            }

            // Extraer solo el nombre de la ciudad del resultado de Nominatim
            _extractCityName(place) {
                const addr = place.address || {};
                // Intentar extraer el nombre más específico de la ciudad
                return addr.city || addr.town || addr.village || addr.municipality || addr.hamlet || place.name || place.display_name.split(',')[0];
            }

            // Extraer subtítulo, es decir la ciudad o comunidad autónoma
            _extractSubtitle(place) {
                const addr = place.address || {};
                const parts = [];
                if (addr.province) parts.push(addr.province);
                if (addr.state && addr.state !== addr.province) parts.push(addr.state);
                return parts.join(', ');
            }

            // Seleccionar una ciudad del desplegable
            _selectCity(name, lat, lng) {
                this.input.value      = name;
                this.hiddenName.value = name;
                this.hiddenLat.value  = lat;
                this.hiddenLng.value  = lng;
                this.selected = true;

                this.check.classList.remove('hidden');
                this._hideDropdown();
            }

            // Limpiar los inputs hidden (cuando el usuario edita manualmente)
            _clearHidden() {
                this.hiddenName.value = '';
                this.hiddenLat.value  = '';
                this.hiddenLng.value  = '';
            }

            // Ocultar dropdown
            _hideDropdown() {
                this.dropdown.classList.add('hidden');
                this.dropdown.innerHTML = '';
            }
            
            _escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }

        // Iniciar autocompletado
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar autocompletado para origen y destino
            new CityAutocomplete('origen');
            new CityAutocomplete('destino');

            // Toggle de campos según tipo (conductor/pasajero)
            const typeRadios = document.querySelectorAll('input[name="tipo"]');
            const seatsInput = document.querySelector('input[name="plazasDisponibles"]');
            const priceInput = document.querySelector('input[name="precio"]');

            function toggleInputs() {
                const selectedType = document.querySelector('input[name="tipo"]:checked').value;

                if (selectedType === 'busco') {
                    // Campos deshabilitados para tipo busco transporte
                    seatsInput.disabled = true;
                    seatsInput.value = '1';
                    priceInput.disabled = true;
                    priceInput.value = '';
                    seatsInput.classList.add('opacity-50', 'cursor-not-allowed');
                    priceInput.classList.add('opacity-50', 'cursor-not-allowed');
                    seatsInput.removeAttribute('required');
                } else {
                    // Campos habilitados
                    seatsInput.disabled = false;
                    priceInput.disabled = false;
                    seatsInput.classList.remove('opacity-50', 'cursor-not-allowed');
                    priceInput.classList.remove('opacity-50', 'cursor-not-allowed');
                    seatsInput.setAttribute('required', 'required');
                    if (!seatsInput.value) seatsInput.value = '1';
                }
            }

            typeRadios.forEach(radio => radio.addEventListener('change', toggleInputs));
            toggleInputs();
        });

        // Validaciones del formulario antes de enviar
        document.getElementById('publishForm').addEventListener('submit', function(e) {
            const origenNombre  = document.getElementById('origen_nombre').value;
            const origenLat     = document.getElementById('origen_lat').value;
            const destinoNombre = document.getElementById('destino_nombre').value;
            const destinoLat    = document.getElementById('destino_lat').value;
            const fecha         = document.getElementById('fechaSalida').value;
            const horaSalida    = document.getElementById('horaSalida').value;
            const horaRegreso   = document.getElementById('horaRegreso').value;

            // Validar que se haya seleccionado ciudad del autocompletado
            if (!origenNombre || !origenLat) {
                e.preventDefault();
                alert('Selecciona una ciudad de origen de la lista de sugerencias.');
                document.getElementById('origen').focus();
                return;
            }

            if (!destinoNombre || !destinoLat) {
                e.preventDefault();
                alert('Selecciona una ciudad de destino de la lista de sugerencias.');
                document.getElementById('destino').focus();
                return;
            }

            // Validación Origen y Destino iguales
            if (origenNombre.toLowerCase() === destinoNombre.toLowerCase()) {
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