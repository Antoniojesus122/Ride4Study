<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$isEdit = isset($ride); // Verificar si se está en modo de edición
$formAction = $isEdit ? url('/edit-ride') : url('/publish');
$submitText = $isEdit ? t('publish.save') : t('publish.create');
$pageTitle = $isEdit ? t('publish.edit_title') : t('publish.create_title');
$pageDesc = $isEdit ? t('publish.edit_desc') : t('publish.create_desc');

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
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-10">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold text-white"><?= $pageTitle ?></h2>
                <p class="text-gray-400 mt-2 lg:text-lg"><?= $pageDesc ?></p>
            </div>
            <div class="hidden sm:flex items-center gap-3">
                <a href="<?= url('/my-rides') ?>" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-600 text-gray-300 hover:text-white hover:bg-gray-800 transition-all font-medium">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> <?= t('publish.cancel') ?>
                </a>
                <button type="submit" form="publishForm" class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i> <?= $submitText ?>
                </button>
            </div>
        </div>

        <!-- Alertas de error -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6" role="alert">
                <p class="font-bold mb-1"><i class="fas fa-exclamation-triangle mr-2" aria-hidden="true"></i><?= t('publish.fix_errors') ?></p>
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="POST" id="publishForm">
            <?php if ($isEdit): ?>
                <input type="hidden" name="ride_id" value="<?= $ride['idAnuncio'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Columna izquierda: Tipo + Ruta + Horarios (7/12) -->
                <div class="lg:col-span-7 flex flex-col gap-6">

                    <!-- Tipo de viaje -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-6 lg:flex-1">
                        <h3 class="text-base lg:text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><i class="fas fa-exchange-alt text-primary text-sm" aria-hidden="true"></i></div>
                            <?= t('publish.driver_or_passenger') ?>
                        </h3>

                        <?php
                            $currentType = isset($_POST['tipo']) ? $_POST['tipo'] : ($isEdit ? strtolower($ride['tipo']) : 'ofrezco');
                        ?>

                        <?php if ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers): ?>
                            <div class="p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-lock text-yellow-500" aria-hidden="true"></i>
                                    <p class="text-yellow-400 text-sm font-medium"><?= t('publish.cant_change_type') ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="ofrezco" class="peer hidden"
                                    <?= $currentType == 'ofrezco' ? 'checked' : '' ?>
                                    <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'disabled' : '' ?>>
                                <div class="p-5 rounded-xl border-2 border-gray-600 <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-800' ?> peer-checked:border-primary peer-checked:bg-primary/10 text-gray-400 peer-checked:text-white transition-all text-center">
                                    <i class="fas fa-car text-2xl mb-2" aria-hidden="true"></i>
                                    <div class="font-bold text-sm lg:text-base"><?= t('publish.i_drive') ?></div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="busco" class="peer hidden"
                                    <?= $currentType == 'busco' ? 'checked' : '' ?>
                                    <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'disabled' : '' ?>>
                                <div class="p-5 rounded-xl border-2 border-gray-600 <?= ($isEdit && isset($hasAcceptedPassengers) && $hasAcceptedPassengers) ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-800' ?> peer-checked:border-purple-500 peer-checked:bg-purple-500/10 text-gray-400 peer-checked:text-white transition-all text-center">
                                    <i class="fas fa-walking text-2xl mb-2" aria-hidden="true"></i>
                                    <div class="font-bold text-sm lg:text-base"><?= t('publish.i_search') ?></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Ruta: Origen y Destino -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-6 lg:flex-1">
                        <h3 class="text-base lg:text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center"><i class="fas fa-route text-blue-400 text-sm" aria-hidden="true"></i></div>
                            <?= t('publish.route_schedule') ?>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Origen -->
                            <div class="group relative">
                                <label for="origen" class="block text-sm font-medium text-gray-400 mb-1.5"><?= t('publish.origin') ?></label>
                                <div class="relative">
                                    <i class="fas fa-map-marker-alt absolute left-3 top-3.5 text-primary z-10" aria-hidden="true"></i>
                                    <input type="text" id="origen" autocomplete="off" required
                                        value="<?= htmlspecialchars($preOrigen) ?>"
                                        placeholder="<?= t('publish.city_placeholder') ?>"
                                        class="block w-full pl-10 pr-10 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500 outline-none">
                                    <div id="origen-spinner" class="hidden absolute right-3 top-3.5"><i class="fas fa-spinner fa-spin text-gray-500 text-sm" aria-hidden="true"></i></div>
                                    <div id="origen-check" class="<?= !empty($preOrigenLat) ? '' : 'hidden' ?> absolute right-3 top-3.5"><i class="fas fa-check-circle text-green-400 text-sm" aria-hidden="true"></i></div>
                                    <input type="hidden" name="origen_nombre" id="origen_nombre" value="<?= htmlspecialchars($preOrigen) ?>">
                                    <input type="hidden" name="origen_lat" id="origen_lat" value="<?= htmlspecialchars($preOrigenLat) ?>">
                                    <input type="hidden" name="origen_lng" id="origen_lng" value="<?= htmlspecialchars($preOrigenLng) ?>">
                                </div>
                                <div id="origen-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
                            </div>

                            <!-- Destino -->
                            <div class="group relative">
                                <label for="destino" class="block text-sm font-medium text-gray-400 mb-1.5"><?= t('publish.destination') ?></label>
                                <div class="relative">
                                    <i class="fas fa-flag-checkered absolute left-3 top-3.5 text-red-400 z-10" aria-hidden="true"></i>
                                    <input type="text" id="destino" autocomplete="off" required
                                        value="<?= htmlspecialchars($preDestino) ?>"
                                        placeholder="<?= t('publish.city_placeholder') ?>"
                                        class="block w-full pl-10 pr-10 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary placeholder-gray-500 outline-none">
                                    <div id="destino-spinner" class="hidden absolute right-3 top-3.5"><i class="fas fa-spinner fa-spin text-gray-500 text-sm" aria-hidden="true"></i></div>
                                    <div id="destino-check" class="<?= !empty($preDestinoLat) ? '' : 'hidden' ?> absolute right-3 top-3.5"><i class="fas fa-check-circle text-green-400 text-sm" aria-hidden="true"></i></div>
                                    <input type="hidden" name="destino_nombre" id="destino_nombre" value="<?= htmlspecialchars($preDestino) ?>">
                                    <input type="hidden" name="destino_lat" id="destino_lat" value="<?= htmlspecialchars($preDestinoLat) ?>">
                                    <input type="hidden" name="destino_lng" id="destino_lng" value="<?= htmlspecialchars($preDestinoLng) ?>">
                                </div>
                                <div id="destino-dropdown" class="hidden absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-xl shadow-2xl overflow-hidden"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Horarios -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-6 lg:flex-1">
                        <h3 class="text-base lg:text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center"><i class="far fa-clock text-purple-400 text-sm" aria-hidden="true"></i></div>
                            <?= t('publish.schedule') ?>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            <!-- Fecha -->
                            <div>
                                <label for="fechaSalida" class="block text-sm font-medium text-gray-400 mb-1.5"><?= t('publish.departure_date') ?></label>
                                <div class="relative">
                                    <i class="far fa-calendar-alt absolute left-3 top-3.5 text-gray-500" aria-hidden="true"></i>
                                    <input type="date" name="fechaSalida" id="fechaSalida" required min="<?= date('Y-m-d') ?>"
                                        value="<?= getVal('fechaSalida', $isEdit ? $ride : [], $_POST) ?>"
                                        class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                                </div>
                            </div>

                            <!-- Hora Salida -->
                            <div>
                                <label for="horaSalida" class="block text-sm font-medium text-gray-400 mb-1.5"><?= t('publish.departure_time') ?></label>
                                <div class="relative">
                                    <i class="far fa-clock absolute left-3 top-3.5 text-gray-500" aria-hidden="true"></i>
                                    <input type="time" name="horaSalida" id="horaSalida" required
                                        value="<?= getVal('horaSalida', $isEdit ? $ride : [], $_POST) ?>"
                                        class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                                </div>
                            </div>

                            <!-- Hora Regreso -->
                            <div>
                                <label for="horaRegreso" class="block text-sm font-medium text-gray-400 mb-1.5"><?= t('publish.return_time') ?></label>
                                <div class="relative">
                                    <i class="fas fa-undo absolute left-3 top-3.5 text-gray-500" aria-hidden="true"></i>
                                    <input type="time" name="horaRegreso" id="horaRegreso"
                                        value="<?= getVal('horaRegreso', $isEdit ? $ride : [], $_POST) ?>"
                                        class="block w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-600 rounded-xl text-white focus:ring-primary focus:border-primary [color-scheme:dark]">
                                </div>
                                <p class="text-xs text-gray-500 mt-1"><?= t('publish.return_hint') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Detalles + Acciones -->
                <div class="lg:col-span-5 flex flex-col gap-6">

                    <!-- Plazas, Precio y Descripción -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-5 lg:flex-1">
                        <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-green-500/10 flex items-center justify-center"><i class="fas fa-sliders-h text-green-400 text-xs" aria-hidden="true"></i></div>
                            <?= t('publish.details') ?>
                        </h3>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <!-- Plazas -->
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1"><?= t('publish.seats') ?></label>
                                <div class="relative">
                                    <i class="fas fa-chair absolute left-3 top-2.5 text-gray-500 text-xs" aria-hidden="true"></i>
                                    <input type="number" name="plazasDisponibles" min="1" max="8" required
                                        value="<?= getVal('plazasDisponibles', $isEdit ? $ride : [], $_POST, '1') ?>"
                                        class="block w-full pl-8 pr-3 py-2.5 bg-gray-800 border border-gray-600 rounded-xl text-white text-sm focus:ring-primary focus:border-primary">
                                </div>
                            </div>

                            <!-- Precio -->
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1"><?= t('publish.price') ?></label>
                                <div class="relative text-white">
                                    <i class="fas fa-euro-sign absolute left-3 top-2.5 text-gray-500 text-xs" aria-hidden="true"></i>
                                    <input type="number" name="precio" min="0" step="0.50"
                                        value="<?= getVal('precio', $isEdit ? $ride : [], $_POST) ?>"
                                        placeholder="<?= t('publish.price_placeholder') ?>"
                                        class="block w-full pl-8 pr-3 py-2.5 bg-gray-800 border border-gray-600 rounded-xl text-white text-sm focus:ring-primary focus:border-primary placeholder-gray-500">
                                </div>
                            </div>
                        </div>

                        <!-- Descripcion -->
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1"><?= t('publish.comments') ?></label>
                            <textarea name="descripcion" rows="3"
                                    placeholder="<?= t('publish.comments_placeholder') ?>"
                                    class="block w-full px-3 py-2.5 bg-gray-800 border border-gray-600 rounded-xl text-white text-sm focus:ring-primary focus:border-primary resize-none placeholder-gray-500"><?= htmlspecialchars(getVal('descripcion', $isEdit ? $ride : [], $_POST)) ?></textarea>
                            <p class="text-[10px] text-gray-500 mt-1"><?= t('publish.max_chars') ?></p>
                        </div>
                    </div>

                    <!-- Mapa interactivo -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-6 flex-1 flex flex-col">
                        <h3 class="text-base lg:text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center"><i class="fas fa-map-marked-alt text-blue-400 text-sm" aria-hidden="true"></i></div>
                            <?= t('publish.map_interactive') ?>
                        </h3>

                        <!-- Botones de selección en mapa -->
                        <div class="flex gap-2 mb-3">
                            <button type="button" id="btn-set-origin"
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border-2 border-gray-600 text-gray-400 text-sm font-medium transition-all hover:border-green-500/50 hover:text-green-400"
                                onclick="setMapMode('origin')">
                                <span class="w-3 h-3 rounded-full bg-green-400 border-2 border-white shadow-sm"></span>
                                <?= t('publish.map_set_origin') ?>
                            </button>
                            <button type="button" id="btn-set-destination"
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border-2 border-gray-600 text-gray-400 text-sm font-medium transition-all hover:border-red-500/50 hover:text-red-400"
                                onclick="setMapMode('destination')">
                                <span class="w-3 h-3 rounded-full bg-red-400 border-2 border-white shadow-sm"></span>
                                <?= t('publish.map_set_destination') ?>
                            </button>
                        </div>

                        <!-- Indicador de modo activo -->
                        <div id="map-mode-indicator" class="hidden mb-3 px-3 py-2 rounded-lg bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs text-center font-medium">
                            <i class="fas fa-mouse-pointer mr-1" aria-hidden="true"></i>
                            <span id="map-mode-text"></span>
                        </div>

                        <div id="publish-map" class="w-full min-h-[20rem] flex-1 rounded-xl border border-gray-600 bg-gray-800 overflow-hidden cursor-crosshair" style="z-index: 1;"></div>
                        <div id="route-info" class="hidden mt-3 flex items-center gap-4 text-sm">
                            <span class="flex items-center gap-1.5 text-gray-400">
                                <i class="fas fa-road text-primary text-xs" aria-hidden="true"></i>
                                <span id="route-distance" class="text-white font-medium">--</span> km
                            </span>
                            <span class="flex items-center gap-1.5 text-gray-400">
                                <i class="fas fa-clock text-primary text-xs" aria-hidden="true"></i>
                                <span id="route-duration" class="text-white font-medium">--</span> min
                            </span>
                        </div>
                        <p id="map-placeholder" class="text-xs text-gray-500 mt-3 text-center">
                            <i class="fas fa-info-circle mr-1" aria-hidden="true"></i> <?= t('publish.map_hint') ?>
                            <br><span class="text-gray-600"><?= t('publish.map_or_type') ?></span>
                        </p>
                        <input type="hidden" name="ruta_polyline" id="ruta_polyline" value="<?= htmlspecialchars($isEdit ? ($ride['ruta_polyline'] ?? '') : '') ?>">
                    </div>

                    <!-- Boton de envio (solo móvil, en desktop está en el header) -->
                    <div class="bg-surface rounded-2xl border border-gray-700 p-6 sm:hidden">
                        <button type="submit" class="w-full px-8 py-4 rounded-xl bg-primary text-secondary font-bold text-lg hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i> <?= $submitText ?>
                        </button>
                        <a href="<?= url('/my-rides') ?>" class="w-full flex items-center justify-center mt-3 px-6 py-3 rounded-xl border border-gray-600 text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-medium">
                            <?= t('publish.cancel') ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>
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
                            <i class="fas fa-search-minus" aria-hidden="true"></i> <?= t('publish.no_results') ?>
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
                            <i class="fas fa-map-pin text-primary text-sm" aria-hidden="true"></i>
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
                alert('<?= t('publish.err_origin') ?>');
                document.getElementById('origen').focus();
                return;
            }

            if (!destinoNombre || !destinoLat) {
                e.preventDefault();
                alert('<?= t('publish.err_destination') ?>');
                document.getElementById('destino').focus();
                return;
            }

            // Validación Origen y Destino iguales
            if (origenNombre.toLowerCase() === destinoNombre.toLowerCase()) {
                e.preventDefault();
                alert('<?= t('publish.err_same') ?>');
                return;
            }

            // Validación fecha de salida
            const today = new Date().toISOString().split('T')[0];
            if (fecha < today) {
                e.preventDefault();
                alert('<?= t('publish.err_past_date') ?>');
                return;
            }

            // Validación hora de salida para viajes del mismo día
            if (fecha === today) {
                const ahora = new Date();
                const horaActual = ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');

                if (horaSalida <= horaActual) {
                    e.preventDefault();
                    alert('<?= t('publish.err_past_time') ?>');
                    return;
                }
            }

            // Validación hora de regreso
            if (horaRegreso && horaRegreso <= horaSalida) {
                e.preventDefault();
                alert('<?= t('publish.err_return_before') ?>');
                return;
            }
        });

        // Vista previa del mapa con ruta + selección interactiva
        const ORS_API_KEY = '<?= $_ENV['ORS_API_KEY'] ?? '' ?>';
        const MAPTILER_KEY = '<?= $_ENV['MAPTILER_KEY'] ?? '' ?>';
        let publishMap = null;
        let routeLayer = null;
        let markersLayer = null;
        let mapClickMode = null; // 'origin' | 'destination' | null
        let reverseGeocodeTimer = null;

        // Iconos reutilizables
        const greenIcon = L.divIcon({
            html: '<div style="background:#34d399;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>',
            iconSize: [14, 14], iconAnchor: [7, 7], className: ''
        });
        const redIcon = L.divIcon({
            html: '<div style="background:#f87171;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>',
            iconSize: [14, 14], iconAnchor: [7, 7], className: ''
        });

        // Inicializar mapa
        function initPublishMap() {
            if (publishMap) return;
            publishMap = L.map('publish-map', {
                zoomControl: true,
                attributionControl: false
            }).setView([39.5, -3.5], 6);

            L.tileLayer(`https://api.maptiler.com/maps/streets-v2-light/{z}/{x}/{y}{r}.png?key=${MAPTILER_KEY}&language=es`, {
                maxZoom: 19,
                tileSize: 512,
                zoomOffset: -1,
                attribution: '&copy; <a href="https://www.maptiler.com/copyright/">MapTiler</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(publishMap);

            routeLayer = L.layerGroup().addTo(publishMap);
            markersLayer = L.layerGroup().addTo(publishMap);

            // Click en el mapa para seleccionar ubicación
            publishMap.on('click', function(e) {
                if (!mapClickMode) return;
                handleMapClick(e.latlng.lat, e.latlng.lng);
            });
        }

        // Activar modo de selección en mapa
        function setMapMode(mode) {
            const btnOrigin = document.getElementById('btn-set-origin');
            const btnDest = document.getElementById('btn-set-destination');
            const indicator = document.getElementById('map-mode-indicator');
            const modeText = document.getElementById('map-mode-text');
            const mapEl = document.getElementById('publish-map');

            // Si ya está activo el mismo modo, desactivar
            if (mapClickMode === mode) {
                mapClickMode = null;
                btnOrigin.classList.remove('border-green-500', 'text-green-400', 'bg-green-500/10');
                btnDest.classList.remove('border-red-500', 'text-red-400', 'bg-red-500/10');
                btnOrigin.classList.add('border-gray-600', 'text-gray-400');
                btnDest.classList.add('border-gray-600', 'text-gray-400');
                indicator.classList.add('hidden');
                mapEl.style.cursor = 'crosshair';
                return;
            }

            mapClickMode = mode;
            initPublishMap();

            // Resetear estilos
            btnOrigin.classList.remove('border-green-500', 'text-green-400', 'bg-green-500/10');
            btnDest.classList.remove('border-red-500', 'text-red-400', 'bg-red-500/10');
            btnOrigin.classList.add('border-gray-600', 'text-gray-400');
            btnDest.classList.add('border-gray-600', 'text-gray-400');

            if (mode === 'origin') {
                btnOrigin.classList.remove('border-gray-600', 'text-gray-400');
                btnOrigin.classList.add('border-green-500', 'text-green-400', 'bg-green-500/10');
                modeText.textContent = '<?= t('publish.map_click_origin') ?>';
                indicator.classList.remove('hidden', 'border-red-500/30', 'bg-red-500/10', 'text-red-400');
                indicator.classList.add('border-green-500/30', 'bg-green-500/10', 'text-green-400');
            } else {
                btnDest.classList.remove('border-gray-600', 'text-gray-400');
                btnDest.classList.add('border-red-500', 'text-red-400', 'bg-red-500/10');
                modeText.textContent = '<?= t('publish.map_click_destination') ?>';
                indicator.classList.remove('hidden', 'border-green-500/30', 'bg-green-500/10', 'text-green-400');
                indicator.classList.add('border-red-500/30', 'bg-red-500/10', 'text-red-400');
            }

            mapEl.style.cursor = 'crosshair';
        }

        // Manejar click en el mapa
        function handleMapClick(lat, lng) {
            const isOrigin = mapClickMode === 'origin';
            const prefix = isOrigin ? 'origen' : 'destino';

            // Actualizar campos hidden de coordenadas
            document.getElementById(prefix + '_lat').value = lat;
            document.getElementById(prefix + '_lng').value = lng;

            // Reverse geocoding con Nominatim
            clearTimeout(reverseGeocodeTimer);
            reverseGeocodeTimer = setTimeout(() => {
                const url = 'https://nominatim.openstreetmap.org/reverse?' + new URLSearchParams({
                    format: 'json',
                    lat: lat,
                    lon: lng,
                    zoom: 14,
                    addressdetails: '1',
                    'accept-language': 'es'
                });

                fetch(url, { headers: { 'User-Agent': 'Ride4Study/1.0' } })
                    .then(r => r.json())
                    .then(data => {
                        const addr = data.address || {};
                        const cityName = addr.city || addr.town || addr.village || addr.municipality || addr.hamlet || data.display_name.split(',')[0];

                        // Rellenar los campos de texto e hidden
                        document.getElementById(prefix).value = cityName;
                        document.getElementById(prefix + '_nombre').value = cityName;

                        // Mostrar check verde
                        document.getElementById(prefix + '-check').classList.remove('hidden');

                        // Actualizar vista previa de ruta
                        updateRoutePreview();
                    })
                    .catch(err => {
                        console.error('Reverse geocode error:', err);
                        // Aun sin nombre, las coordenadas ya están puestas
                        document.getElementById(prefix).value = lat.toFixed(4) + ', ' + lng.toFixed(4);
                        document.getElementById(prefix + '_nombre').value = lat.toFixed(4) + ', ' + lng.toFixed(4);
                        updateRoutePreview();
                    });
            }, 100);

            // Cambiar automáticamente al siguiente modo
            if (mapClickMode === 'origin') {
                // Si no hay destino aún, pasar a modo destino
                const dLat = parseFloat(document.getElementById('destino_lat').value || 0);
                if (!dLat) {
                    setMapMode('destination');
                } else {
                    setMapMode(null); // Desactivar
                    mapClickMode = null;
                }
            } else {
                setMapMode(null); // Desactivar tras poner destino
                mapClickMode = null;
            }

            // Actualizar marcadores inmediatamente
            updateMapMarkers();
        }

        // Actualizar marcadores en el mapa (sin ruta, solo puntos)
        function updateMapMarkers() {
            if (!publishMap) return;
            markersLayer.clearLayers();

            const oLat = parseFloat(document.getElementById('origen_lat').value || 0);
            const oLng = parseFloat(document.getElementById('origen_lng').value || 0);
            const dLat = parseFloat(document.getElementById('destino_lat').value || 0);
            const dLng = parseFloat(document.getElementById('destino_lng').value || 0);

            if (oLat && oLng) L.marker([oLat, oLng], { icon: greenIcon }).addTo(markersLayer);
            if (dLat && dLng) L.marker([dLat, dLng], { icon: redIcon }).addTo(markersLayer);

            // Ajustar vista si hay al menos un punto
            const bounds = [];
            if (oLat && oLng) bounds.push([oLat, oLng]);
            if (dLat && dLng) bounds.push([dLat, dLng]);
            if (bounds.length === 1) publishMap.setView(bounds[0], 10);
            if (bounds.length === 2) publishMap.fitBounds(bounds, { padding: [40, 40] });
        }

        // Actualizar ruta cuando se seleccionan origen y destino
        function updateRoutePreview() {
            const oLat = parseFloat(document.getElementById('origen_lat')?.value || 0);
            const oLng = parseFloat(document.getElementById('origen_lng')?.value || 0);
            const dLat = parseFloat(document.getElementById('destino_lat')?.value || 0);
            const dLng = parseFloat(document.getElementById('destino_lng')?.value || 0);

            if (!oLat || !oLng || !dLat || !dLng) return;

            initPublishMap();
            routeLayer.clearLayers();
            markersLayer.clearLayers();

            L.marker([oLat, oLng], { icon: greenIcon }).addTo(markersLayer);
            L.marker([dLat, dLng], { icon: redIcon }).addTo(markersLayer);

            // Pedir ruta a OpenRouteService
            const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${ORS_API_KEY}&start=${oLng},${oLat}&end=${dLng},${dLat}`;

            fetch(url, { headers: { 'Accept': 'application/json, application/geo+json' } })
                .then(r => r.json())
                .then(data => {
                    const feature = data.features?.[0];
                    if (!feature) return;

                    const coords = feature.geometry.coordinates.map(c => [c[1], c[0]]);
                    const polyline = L.polyline(coords, {
                        color: '#34d399', weight: 4, opacity: 0.8, smoothFactor: 1
                    }).addTo(routeLayer);

                    publishMap.fitBounds(polyline.getBounds(), { padding: [30, 30] });

                    // Info de distancia y duración
                    const dist = (feature.properties.summary.distance / 1000).toFixed(1);
                    const dur = Math.ceil(feature.properties.summary.duration / 60);
                    document.getElementById('route-distance').textContent = dist;
                    document.getElementById('route-duration').textContent = dur;
                    document.getElementById('route-info').classList.remove('hidden');
                    document.getElementById('map-placeholder').classList.add('hidden');

                    // Guardar polyline para el backend
                    document.getElementById('ruta_polyline').value = JSON.stringify(feature.geometry.coordinates);
                })
                .catch(err => console.error('Error fetching route:', err));
        }

        // Observar cambios en los campos de coordenadas
        initPublishMap();
        const coordFields = ['origen_lat', 'origen_lng', 'destino_lat', 'destino_lng'];
        coordFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                const observer = new MutationObserver(() => updateRoutePreview());
                observer.observe(el, { attributes: true, attributeFilter: ['value'] });
                el.addEventListener('change', updateRoutePreview);
            }
        });

        // Sobreescribir el setter de value en los campos hidden para detectar cambios
        coordFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                const desc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
                Object.defineProperty(el, 'value', {
                    set(v) {
                        desc.set.call(this, v);
                        setTimeout(updateRoutePreview, 100);
                    },
                    get() { return desc.get.call(this); }
                });
            }
        });

        <?php if ($isEdit && !empty($ride['ruta_polyline'])): ?>
        // Si estamos editando y hay ruta guardada, mostrarla
        setTimeout(() => {
            initPublishMap();
            try {
                const coords = JSON.parse('<?= addslashes($ride['ruta_polyline']) ?>');
                const latLngs = coords.map(c => [c[1], c[0]]);
                const polyline = L.polyline(latLngs, {
                    color: '#34d399', weight: 4, opacity: 0.8, smoothFactor: 1
                }).addTo(routeLayer);
                publishMap.fitBounds(polyline.getBounds(), { padding: [30, 30] });

                L.marker(latLngs[0], { icon: greenIcon }).addTo(markersLayer);
                L.marker(latLngs[latLngs.length - 1], { icon: redIcon }).addTo(markersLayer);
                document.getElementById('map-placeholder').classList.add('hidden');

                <?php if (!empty($ride['distancia_km'])): ?>
                document.getElementById('route-distance').textContent = '<?= $ride['distancia_km'] ?>';
                document.getElementById('route-duration').textContent = '<?= $ride['duracion_min'] ?? '--' ?>';
                document.getElementById('route-info').classList.remove('hidden');
                <?php endif; ?>
            } catch(e) { console.error('Error loading saved route:', e); }
        }, 300);
        <?php endif; ?>
    </script>
</body>