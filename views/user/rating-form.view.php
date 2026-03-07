<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('/login'));
    exit;
}

if (!isset($tripDetails) || !isset($userToRate)) {
    $_SESSION['error'] = 'No se han proporcionado los datos necesarios para valorar el viaje.';
    header('Location: ' . url('/dashboard'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-900">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Valorar Viaje - Ride4Study</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <style>
            body { font-family: 'Inter', sans-serif; }

            .star-rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: center;
                gap: 10px;
            }
            .star-rating input { display: none; }
            .star-rating label {
                cursor: pointer;
                font-size: 2.5rem;
                color: #374151;
                transition: color 0.2s, transform 0.2s;
            }
            .star-rating input:checked ~ label,
            .star-rating label:hover,
            .star-rating label:hover ~ label {
                color: #6EE7B7;
                transform: scale(1.1);
            }
        </style>
    </head>
    <body class="h-full text-gray-100 flex flex-col pt-28 bg-cover bg-center">
        <div class="fixed inset-0 bg-gray-900/90 z-[-1]"></div>

        <?php require_once __DIR__ . '/../layouts/header.php'; ?>

        <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 py-8">

            <!-- Título -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white">Valorar experiencia</h2>
                <p class="mt-1 text-gray-400">Tu opinión ayuda a construir una comunidad de confianza.</p>
            </div>

            <!-- Detalles del viaje -->
            <div class="bg-surface rounded-2xl border border-gray-700 p-5 mb-5 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                        <i class="fas fa-route text-primary"></i> Detalles del viaje
                    </h3>
                    <span class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-semibold">
                        <?php echo ucfirst($tripDetails['tipo']); ?>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-3">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <?php echo date('d/m/Y', strtotime($tripDetails['fechaSalida'])); ?>
                    <i class="far fa-clock ml-3 mr-1"></i>
                    <?php echo date('H:i', strtotime($tripDetails['horaSalida'])); ?>
                </p>
                <div class="flex items-center gap-3 bg-gray-800 rounded-xl p-4 text-sm">
                    <div class="flex items-center gap-2 flex-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                        <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($tripDetails['origenNombre']); ?></span>
                    </div>
                    <i class="fas fa-arrow-right text-gray-500 text-xs"></i>
                    <div class="flex items-center gap-2 flex-1 justify-end">
                        <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($tripDetails['destinoNombre']); ?></span>
                        <div class="w-2.5 h-2.5 rounded-full bg-cyan-400"></div>
                    </div>
                </div>
            </div>

            <!-- Usuario a valorar -->
            <div class="bg-surface rounded-2xl border border-gray-700 p-5 mb-5 shadow-lg">
                <div class="flex items-center gap-4">
                    <img src="<?php echo $userToRate['foto'] ? '/Ride4Study/public/uploads/profiles/' . $userToRate['foto'] : '/Ride4Study/public/img/default-avatar.png'; ?>"
                         alt="<?php echo htmlspecialchars($userToRate['nombre']); ?>"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-primary/40">
                    <div>
                        <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($userToRate['nombre']); ?></h3>
                        <p class="text-sm text-gray-400 mt-0.5 flex items-center gap-1.5">
                            <i class="fas fa-user-tag text-primary text-xs"></i>
                            <span class="capitalize"><?php echo $userRole; ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form id="ratingForm" class="bg-surface rounded-2xl border border-gray-700 p-6 shadow-lg space-y-8">
                <input type="hidden" name="idViaje" value="<?php echo $tripDetails['idViaje']; ?>">
                <input type="hidden" name="idValorado" value="<?php echo $userToRate['id']; ?>">

                <!-- Puntuación general -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1">
                        Valoración General <span class="text-red-400">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4">Selecciona las estrellas para puntuar</p>
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
                    <p id="ratingText" class="text-center text-sm font-medium text-primary mt-3 opacity-0 transition-opacity"></p>
                </div>

                <div class="h-px bg-gray-700"></div>

                <!-- Valoración detallada -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-300 mb-1 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-primary"></i> Valoración Detallada
                        <span class="text-gray-500 font-normal text-xs">(Opcional)</span>
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4 mt-4">

                        <!-- Puntualidad -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                                <i class="fas fa-clock text-primary"></i> Puntualidad
                            </label>
                            <div class="relative">
                                <select name="puntualidad" class="appearance-none w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm cursor-pointer">
                                    <option value="">No valorar</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                    <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                    <option value="3">⭐⭐⭐ Bueno</option>
                                    <option value="2">⭐⭐ Regular</option>
                                    <option value="1">⭐ Malo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Comunicación -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                                <i class="fas fa-comments text-cyan-400"></i> Comunicación
                            </label>
                            <div class="relative">
                                <select name="comunicacion" class="appearance-none w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm cursor-pointer">
                                    <option value="">No valorar</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                    <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                    <option value="3">⭐⭐⭐ Bueno</option>
                                    <option value="2">⭐⭐ Regular</option>
                                    <option value="1">⭐ Malo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <?php if ($userRole === 'conductor'): ?>
                        <!-- Vehículo -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                                <i class="fas fa-car text-green-400"></i> Vehículo
                            </label>
                            <div class="relative">
                                <select name="vehiculo" class="appearance-none w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm cursor-pointer">
                                    <option value="">No valorar</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                    <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                    <option value="3">⭐⭐⭐ Bueno</option>
                                    <option value="2">⭐⭐ Regular</option>
                                    <option value="1">⭐ Malo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Conducción -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                                <i class="fas fa-steering-wheel text-yellow-400"></i> Conducción
                            </label>
                            <div class="relative">
                                <select name="conduccion" class="appearance-none w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm cursor-pointer">
                                    <option value="">No valorar</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                    <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                    <option value="3">⭐⭐⭐ Bueno</option>
                                    <option value="2">⭐⭐ Regular</option>
                                    <option value="1">⭐ Malo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Comportamiento -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                                <i class="fas fa-smile text-pink-400"></i> Comportamiento
                            </label>
                            <div class="relative">
                                <select name="comportamiento" class="appearance-none w-full bg-gray-800 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm cursor-pointer">
                                    <option value="">No valorar</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excelente</option>
                                    <option value="4">⭐⭐⭐⭐ Muy bueno</option>
                                    <option value="3">⭐⭐⭐ Bueno</option>
                                    <option value="2">⭐⭐ Regular</option>
                                    <option value="1">⭐ Malo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="h-px bg-gray-700"></div>

                <!-- Comentario -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-400 flex items-center gap-1.5">
                        <i class="fas fa-comment-dots text-primary"></i>
                        Comparte tu experiencia
                        <span class="text-gray-600 font-normal">(Opcional, máx. 500 caracteres)</span>
                    </label>
                    <textarea name="comentario"
                              id="comentario"
                              rows="4"
                              maxlength="500"
                              placeholder="Cuéntanos cómo fue tu experiencia con este viaje..."
                              class="w-full bg-gray-800 border border-white/10 rounded-xl px-5 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all text-sm"></textarea>
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-600">Tu comentario será visible públicamente</p>
                        <p class="text-xs text-gray-400"><span id="charCount" class="text-primary font-semibold">0</span>/500</p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-2">
                    <button type="button"
                            onclick="window.location.href='<?= url("/dashboard") ?>'"
                            class="flex-1 px-5 py-3 bg-gray-800 border border-gray-700 text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-700 hover:border-gray-600 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit"
                            id="submitBtn"
                            class="flex-1 px-5 py-3 bg-primary text-secondary rounded-xl text-sm font-bold shadow-lg shadow-primary/25 hover:bg-primary-dark transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Enviar Valoración
                    </button>
                </div>
            </form>

            <!-- Mensaje de respuesta -->
            <div id="responseMessage" class="hidden mt-4"></div>

        </div>

        <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

        <script>
            const ratingTexts = {
                1: '😞 Muy mala experiencia',
                2: '😕 Experiencia regular',
                3: '😊 Experiencia buena',
                4: '😃 Muy buena experiencia',
                5: '🌟 ¡Experiencia excelente!'
            };

            document.querySelectorAll('input[name="puntuacion"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const text = document.getElementById('ratingText');
                    text.textContent = ratingTexts[this.value];
                    text.style.opacity = '1';
                });
            });

            document.getElementById('comentario').addEventListener('input', function () {
                document.getElementById('charCount').textContent = this.value.length;
            });

            const form        = document.getElementById('ratingForm');
            const submitBtn   = document.getElementById('submitBtn');
            const responseMsg = document.getElementById('responseMessage');

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!document.querySelector('input[name="puntuacion"]:checked')) {
                    showMessage('Por favor, selecciona una valoración general.', 'error');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';

                try {
                    const res    = await fetch('<?= url("/rating") ?>', { method: 'POST', body: new FormData(form) });
                    const result = await res.json();

                    if (result.success) {
                        showMessage(result.message || '¡Valoración enviada con éxito!', 'success');
                        setTimeout(() => { window.location.href = '<?= url("/dashboard") ?>'; }, 2000);
                    } else {
                        showMessage(result.message || 'Error al enviar la valoración.', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Valoración';
                    }
                } catch (err) {
                    showMessage('Error de conexión. Por favor, inténtalo de nuevo.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Valoración';
                }
            });

            function showMessage(message, type) {
                const isSuccess = type === 'success';
                responseMsg.className = `bg-surface border ${isSuccess ? 'border-primary/30' : 'border-red-500/30'} rounded-2xl p-4 flex items-center gap-3`;
                responseMsg.innerHTML = `
                    <div class="flex items-center justify-center w-10 h-10 rounded-full ${isSuccess ? 'bg-primary/10' : 'bg-red-500/10'} shrink-0">
                        <i class="fas ${isSuccess ? 'fa-check-circle text-primary' : 'fa-exclamation-circle text-red-400'} text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-white">${message}</span>
                `;
                responseMsg.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        </script>
    </body>
</html>