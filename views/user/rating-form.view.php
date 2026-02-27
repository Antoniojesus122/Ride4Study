<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Ride4Study/login.php');
    exit;
}

// Validar que se hayan pasado los parámetros necesarios
if (!isset($tripDetails) || !isset($userToRate)) {
    $_SESSION['error'] = 'No se han proporcionado los datos necesarios para valorar el viaje.';
    header('Location: /Ride4Study/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
    <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Valorar Viaje - Ride4Study</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            
            <style>
                .star-rating {
                    display: flex;
                    flex-direction: row-reverse;
                    justify-content: center;
                    gap: 5px;
                }
                .star-rating input {
                    display: none;
                }
                .star-rating label {
                    cursor: pointer;
                    font-size: 2rem;
                    color: #d1d5db;
                    transition: color 0.2s;
                }
                .star-rating input:checked ~ label,
                .star-rating label:hover,
                .star-rating label:hover ~ label {
                    color: #fbbf24;
                }
            </style>
        </head>
        <body class="bg-gradient-to-br from-purple-50 to-blue-50 min-h-screen">

            <!-- Header -->
            <?php require_once __DIR__ . '/../layouts/header.php'; ?>

            <div class="container mx-auto px-4 py-8 max-w-3xl">
                <!-- Título -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-star text-yellow-400"></i>
                        Valora tu experiencia
                    </h1>
                    <p class="text-gray-600">Tu opinión ayuda a construir una comunidad de confianza</p>
                </div>

                <!-- Card del viaje -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Detalles del viaje</h3>
                            <p class="text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($tripDetails['fechaSalida'])); ?> 
                                a las <?php echo date('H:i', strtotime($tripDetails['horaSalida'])); ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                            <?php echo ucfirst($tripDetails['tipo']); ?>
                        </span>
                    </div>

                    <div class="flex items-center gap-4 text-gray-700">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-green-500"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($tripDetails['origenNombre']); ?></span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-500"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($tripDetails['destinoNombre']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Usuario a valorar -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <div class="flex items-center gap-4 mb-6">
                        <img src="<?php echo $userToRate['foto'] ? '/Ride4Study/public/uploads/profiles/' . $userToRate['foto'] : '/Ride4Study/public/img/default-avatar.png'; ?>" 
                            alt="<?php echo htmlspecialchars($userToRate['nombre']); ?>"
                            class="w-20 h-20 rounded-full object-cover border-4 border-purple-200">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?php echo htmlspecialchars($userToRate['nombre']); ?>
                            </h3>
                            <p class="text-gray-600">
                                <i class="fas fa-user-tag text-purple-500"></i>
                                <?php echo ucfirst($userRole); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario de valoración -->
                <form id="ratingForm" class="bg-white rounded-xl shadow-lg p-8">
                    <input type="hidden" name="idViaje" value="<?php echo $tripDetails['idViaje']; ?>">
                    <input type="hidden" name="idValorado" value="<?php echo $userToRate['id']; ?>">

                    <!-- Valoración general -->
                    <div class="mb-8">
                        <label class="block text-center text-lg font-semibold text-gray-700 mb-4">
                            Valoración General <span class="text-red-500">*</span>
                        </label>
                        <div class="star-rating" id="generalRating">
                            <input type="radio" name="puntuacion" id="star5" value="5" required>
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="puntuacion" id="star4" value="4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="puntuacion" id="star3" value="3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="puntuacion" id="star2" value="2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="puntuacion" id="star1" value="1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                        <p class="text-center text-sm text-gray-500 mt-2">Haz clic en las estrellas para valorar</p>
                    </div>

                    <hr class="my-8">

                    <!-- Categorías específicas -->
                    <h3 class="text-xl font-bold text-gray-800 mb-6 text-center">
                        <i class="fas fa-th-list text-purple-500"></i>
                        Valoración Detallada <span class="text-gray-400 text-sm font-normal">(Opcional)</span>
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <!-- Puntualidad -->
                        <div class="bg-purple-50 rounded-lg p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock text-purple-600"></i>
                                Puntualidad
                            </label>
                            <select name="puntualidad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">No valorar</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                <option value="3">⭐⭐⭐ Bueno</option>
                                <option value="2">⭐⭐ Regular</option>
                                <option value="1">⭐ Malo</option>
                            </select>
                        </div>

                        <!-- Comunicación -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-comments text-blue-600"></i>
                                Comunicación
                            </label>
                            <select name="comunicacion" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">No valorar</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                <option value="3">⭐⭐⭐ Bueno</option>
                                <option value="2">⭐⭐ Regular</option>
                                <option value="1">⭐ Malo</option>
                            </select>
                        </div>

                        <?php if ($userRole === 'conductor'): ?>
                        <!-- Vehículo (solo para conductores) -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-car text-green-600"></i>
                                Vehículo
                            </label>
                            <select name="vehiculo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">No valorar</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                <option value="3">⭐⭐⭐ Bueno</option>
                                <option value="2">⭐⭐ Regular</option>
                                <option value="1">⭐ Malo</option>
                            </select>
                        </div>

                        <!-- Conducción (solo para conductores) -->
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-steering-wheel text-yellow-600"></i>
                                Conducción
                            </label>
                            <select name="conduccion" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="">No valorar</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                <option value="3">⭐⭐⭐ Bueno</option>
                                <option value="2">⭐⭐ Regular</option>
                                <option value="1">⭐ Malo</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Comportamiento -->
                        <div class="bg-pink-50 rounded-lg p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-smile text-pink-600"></i>
                                Comportamiento
                            </label>
                            <select name="comportamiento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                <option value="">No valorar</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                <option value="3">⭐⭐⭐ Bueno</option>
                                <option value="2">⭐⭐ Regular</option>
                                <option value="1">⭐ Malo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Comentario -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-comment-dots text-gray-600"></i>
                            Comparte tu experiencia <span class="text-gray-400 font-normal">(Opcional, máx. 500 caracteres)</span>
                        </label>
                        <textarea name="comentario" 
                                id="comentario"
                                rows="4" 
                                maxlength="500"
                                placeholder="Cuéntanos cómo fue tu experiencia..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
                        <p class="text-sm text-gray-500 mt-1">
                            <span id="charCount">0</span>/500 caracteres
                        </p>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4">
                        <button type="button" 
                                onclick="window.location.href='/Ride4Study/dashboard.php'"
                                class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" 
                                id="submitBtn"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Enviar Valoración
                        </button>
                    </div>
                </form>

                <!-- Mensaje de respuesta -->
                <div id="responseMessage" class="hidden mt-6"></div>
            </div>

            <!-- Footer -->
            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

            <script>
                // Contador de caracteres
                const comentario = document.getElementById('comentario');
                const charCount = document.getElementById('charCount');
                
                comentario.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });

                // Envío del formulario
                const form = document.getElementById('ratingForm');
                const submitBtn = document.getElementById('submitBtn');
                const responseMessage = document.getElementById('responseMessage');

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Validar que se haya seleccionado una puntuación general
                    const puntuacion = document.querySelector('input[name="puntuacion"]:checked');
                    if (!puntuacion) {
                        showMessage('Por favor, selecciona una valoración general', 'error');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';

                    try {
                        const formData = new FormData(form);
                        const response = await fetch('/Ride4Study/rating.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            showMessage(result.message || '¡Valoración enviada con éxito!', 'success');
                            setTimeout(() => {
                                window.location.href = '/Ride4Study/dashboard.php';
                            }, 2000);
                        } else {
                            showMessage(result.message || 'Error al enviar la valoración', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Valoración';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showMessage('Error de conexión. Por favor, inténtalo de nuevo.', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Valoración';
                    }
                });

                function showMessage(message, type) {
                    responseMessage.className = type === 'success' 
                        ? 'bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg flex items-center gap-3' 
                        : 'bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg flex items-center gap-3';
                    
                    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                    responseMessage.innerHTML = `<i class="fas ${icon} text-xl"></i><span>${message}</span>`;
                    responseMessage.classList.remove('hidden');
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            </script>
        </body>
    </html>
