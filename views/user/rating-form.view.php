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

    require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 12px;
    }
    .star-rating input { display: none; }
    .star-rating label {
        cursor: pointer;
        font-size: 2.8rem;
        color: #4B5563;
        transition: all 0.2s ease;
    }
    .star-rating label:hover {
        transform: scale(1.2);
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #FBBF24;
        filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.4));
    }
</style>

<div class="w-full mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-10">

    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl lg:text-4xl font-bold text-white"><?= t('rating.title') ?></h2>
            <p class="text-gray-400 mt-2 lg:text-lg"><?= t('rating.subtitle') ?></p>
        </div>
        <div class="hidden sm:flex items-center gap-3">
            <a href="<?= url('/my-rides') ?>?tab=past-bookings" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-600 text-gray-300 hover:text-white hover:bg-gray-800 transition-all font-medium">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> <?= t('rating.cancel') ?>
            </a>
            <button type="submit" form="ratingForm" class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-paper-plane" aria-hidden="true"></i> <?= t('rating.submit') ?>
            </button>
        </div>
    </div>

    <!-- Mensaje de respuesta -->
    <div id="responseMessage" class="hidden mb-6"></div>

    <form id="ratingForm">
        <input type="hidden" name="idViaje" value="<?= $tripDetails['idViaje'] ?>">
        <input type="hidden" name="idValorado" value="<?= $userToRate['id'] ?>">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Columna izquierda: Puntuación + Detallada -->
            <div class="lg:col-span-7 flex flex-col gap-6">

                <!-- Puntuación general -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><i class="fas fa-star text-primary text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.general') ?> <span class="text-red-400 text-sm">*</span>
                    </h3>

                    <p class="text-sm text-gray-400 mb-5"><?= t('rating.select_stars') ?></p>

                    <div class="bg-gray-800/40 rounded-xl p-6 border border-gray-700/40">
                        <div class="star-rating" id="generalRating">
                            <input type="radio" name="puntuacion" id="star5" value="5" required>
                            <label for="star5"><i class="fas fa-star" aria-hidden="true"></i></label>
                            <input type="radio" name="puntuacion" id="star4" value="4">
                            <label for="star4"><i class="fas fa-star" aria-hidden="true"></i></label>
                            <input type="radio" name="puntuacion" id="star3" value="3">
                            <label for="star3"><i class="fas fa-star" aria-hidden="true"></i></label>
                            <input type="radio" name="puntuacion" id="star2" value="2">
                            <label for="star2"><i class="fas fa-star" aria-hidden="true"></i></label>
                            <input type="radio" name="puntuacion" id="star1" value="1">
                            <label for="star1"><i class="fas fa-star" aria-hidden="true"></i></label>
                        </div>
                        <p id="ratingText" class="text-center text-sm font-semibold text-primary mt-4 h-5 opacity-0 transition-all"></p>
                    </div>
                </div>

                <!-- Valoración detallada -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-1 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center"><i class="fas fa-sliders-h text-cyan-400 text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.detailed') ?>
                        <span class="text-gray-500 font-normal text-xs ml-1"><?= t('rating.optional') ?></span>
                    </h3>
                    <p class="text-sm text-gray-400 mb-5 ml-10"><?= t('rating.detailed_desc') ?></p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Puntualidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5 flex items-center gap-2">
                                <i class="fas fa-clock text-primary text-xs" aria-hidden="true"></i> <?= t('rating.punctuality') ?>
                            </label>
                            <div class="relative">
                                <select name="puntualidad" class="appearance-none w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:ring-primary focus:border-primary transition-all text-sm cursor-pointer hover:border-gray-500 outline-none">
                                    <option value=""><?= t('rating.no_rate') ?></option>
                                    <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; <?= t('rating.excellent') ?></option>
                                    <option value="4">&#11088;&#11088;&#11088;&#11088; <?= t('rating.very_good') ?></option>
                                    <option value="3">&#11088;&#11088;&#11088; <?= t('rating.good') ?></option>
                                    <option value="2">&#11088;&#11088; <?= t('rating.fair') ?></option>
                                    <option value="1">&#11088; <?= t('rating.bad') ?></option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs" aria-hidden="true"></i></div>
                            </div>
                        </div>

                        <!-- Comunicación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5 flex items-center gap-2">
                                <i class="fas fa-comments text-cyan-400 text-xs" aria-hidden="true"></i> <?= t('rating.communication') ?>
                            </label>
                            <div class="relative">
                                <select name="comunicacion" class="appearance-none w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:ring-primary focus:border-primary transition-all text-sm cursor-pointer hover:border-gray-500 outline-none">
                                    <option value=""><?= t('rating.no_rate') ?></option>
                                    <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; <?= t('rating.excellent') ?></option>
                                    <option value="4">&#11088;&#11088;&#11088;&#11088; <?= t('rating.very_good') ?></option>
                                    <option value="3">&#11088;&#11088;&#11088; <?= t('rating.good') ?></option>
                                    <option value="2">&#11088;&#11088; <?= t('rating.fair') ?></option>
                                    <option value="1">&#11088; <?= t('rating.bad') ?></option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs" aria-hidden="true"></i></div>
                            </div>
                        </div>

                        <?php if ($userRole === 'conductor'): ?>
                        <!-- Vehículo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5 flex items-center gap-2">
                                <i class="fas fa-car text-green-400 text-xs" aria-hidden="true"></i> <?= t('rating.vehicle') ?>
                            </label>
                            <div class="relative">
                                <select name="vehiculo" class="appearance-none w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:ring-primary focus:border-primary transition-all text-sm cursor-pointer hover:border-gray-500 outline-none">
                                    <option value=""><?= t('rating.no_rate') ?></option>
                                    <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; <?= t('rating.excellent') ?></option>
                                    <option value="4">&#11088;&#11088;&#11088;&#11088; <?= t('rating.very_good') ?></option>
                                    <option value="3">&#11088;&#11088;&#11088; <?= t('rating.good') ?></option>
                                    <option value="2">&#11088;&#11088; <?= t('rating.fair') ?></option>
                                    <option value="1">&#11088; <?= t('rating.bad') ?></option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs" aria-hidden="true"></i></div>
                            </div>
                        </div>

                        <!-- Conducción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5 flex items-center gap-2">
                                <i class="fas fa-road text-yellow-400 text-xs" aria-hidden="true"></i> <?= t('rating.driving') ?>
                            </label>
                            <div class="relative">
                                <select name="conduccion" class="appearance-none w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:ring-primary focus:border-primary transition-all text-sm cursor-pointer hover:border-gray-500 outline-none">
                                    <option value=""><?= t('rating.no_rate') ?></option>
                                    <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; <?= t('rating.excellent') ?></option>
                                    <option value="4">&#11088;&#11088;&#11088;&#11088; <?= t('rating.very_good') ?></option>
                                    <option value="3">&#11088;&#11088;&#11088; <?= t('rating.good') ?></option>
                                    <option value="2">&#11088;&#11088; <?= t('rating.fair') ?></option>
                                    <option value="1">&#11088; <?= t('rating.bad') ?></option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs" aria-hidden="true"></i></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Comportamiento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5 flex items-center gap-2">
                                <i class="fas fa-smile text-pink-400 text-xs" aria-hidden="true"></i> <?= t('rating.behavior') ?>
                            </label>
                            <div class="relative">
                                <select name="comportamiento" class="appearance-none w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white focus:ring-primary focus:border-primary transition-all text-sm cursor-pointer hover:border-gray-500 outline-none">
                                    <option value=""><?= t('rating.no_rate') ?></option>
                                    <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; <?= t('rating.excellent') ?></option>
                                    <option value="4">&#11088;&#11088;&#11088;&#11088; <?= t('rating.very_good') ?></option>
                                    <option value="3">&#11088;&#11088;&#11088; <?= t('rating.good') ?></option>
                                    <option value="2">&#11088;&#11088; <?= t('rating.fair') ?></option>
                                    <option value="1">&#11088; <?= t('rating.bad') ?></option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comentario -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-1 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center"><i class="fas fa-comment-dots text-purple-400 text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.share_experience') ?>
                        <span class="text-gray-500 font-normal text-xs ml-1"><?= t('rating.max_chars') ?></span>
                    </h3>
                    <p class="text-sm text-gray-400 mb-4 ml-10"><?= t('rating.comment_public') ?></p>

                    <textarea name="comentario"
                              id="comentario"
                              rows="5"
                              maxlength="500"
                              placeholder="<?= t('rating.comment_placeholder') ?>"
                              class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:ring-primary focus:border-primary resize-none transition-all text-sm outline-none hover:border-gray-500"></textarea>
                    <div class="flex justify-end mt-2">
                        <p class="text-xs text-gray-400"><span id="charCount" class="text-primary font-semibold">0</span>/500</p>
                    </div>
                </div>

                <!-- Botones móvil -->
                <div class="sm:hidden flex gap-3">
                    <a href="<?= url('/my-rides') ?>?tab=past-bookings" class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-800 transition-all font-medium text-sm">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> <?= t('rating.cancel') ?>
                    </a>
                    <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-secondary font-bold hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all text-sm">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> <?= t('rating.submit') ?>
                    </button>
                </div>
            </div>

            <!-- Columna derecha: Info del viaje + usuario -->
            <div class="lg:col-span-5 flex flex-col gap-6">

                <!-- Datos del viaje -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center"><i class="fas fa-route text-blue-400 text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.ride_details') ?>
                    </h3>

                    <div class="space-y-4">
                        <!-- Tipo badge + fecha -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="px-3 py-1 bg-<?= $tripDetails['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/10 text-<?= $tripDetails['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-400 rounded-full text-xs font-bold border border-<?= $tripDetails['tipo'] === 'ofrezco' ? 'blue' : 'purple' ?>-500/20 uppercase tracking-wide">
                                <?= ucfirst($tripDetails['tipo']) ?>
                            </span>
                            <span class="text-sm text-gray-400 flex items-center gap-1.5">
                                <i class="far fa-calendar text-xs" aria-hidden="true"></i>
                                <?= date('d M, Y', strtotime($tripDetails['fechaSalida'])) ?>
                            </span>
                            <span class="text-sm text-gray-400 flex items-center gap-1.5">
                                <i class="far fa-clock text-xs" aria-hidden="true"></i>
                                <?= date('H:i', strtotime($tripDetails['horaSalida'])) ?>
                            </span>
                        </div>

                        <!-- Ruta -->
                        <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-700/40">
                            <div class="flex items-stretch gap-4">
                                <div class="flex flex-col items-center pt-1 pb-1">
                                    <div class="w-3.5 h-3.5 rounded-full border-[3px] border-primary bg-surface shadow-md shadow-primary/20 shrink-0"></div>
                                    <div class="w-0.5 flex-1 bg-gradient-to-b from-primary/60 to-gray-600 my-1"></div>
                                    <div class="w-3.5 h-3.5 rounded-full border-[3px] border-gray-500 bg-surface shrink-0"></div>
                                </div>
                                <div class="flex-1 flex flex-col justify-between gap-4">
                                    <div>
                                        <h4 class="text-base font-bold text-white"><?= htmlspecialchars($tripDetails['origenNombre']) ?></h4>
                                        <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1" aria-hidden="true"></i><?= t('dashboard.departure') ?>: <?= substr($tripDetails['horaSalida'], 0, 5) ?></p>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-white"><?= htmlspecialchars($tripDetails['destinoNombre']) ?></h4>
                                        <?php if (!empty($tripDetails['horaLlegada'])): ?>
                                            <p class="text-sm text-primary font-semibold mt-0.5"><i class="far fa-clock text-xs mr-1" aria-hidden="true"></i><?= t('dashboard.arrival_label') ?>: <?= substr($tripDetails['horaLlegada'], 0, 5) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($tripDetails['horaRegreso'])): ?>
                            <div class="flex items-center gap-2 bg-purple-500/10 px-3 py-2 rounded-lg border border-purple-500/20">
                                <i class="fas fa-undo text-purple-400 text-xs" aria-hidden="true"></i>
                                <span class="text-sm text-purple-400 font-medium"><?= t('myrides.return_time') ?> <?= substr($tripDetails['horaRegreso'], 0, 5) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Usuario a valorar -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center"><i class="fas fa-user text-green-400 text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.rating_to') ?>
                    </h3>

                    <div class="flex items-center gap-4 bg-gray-800/40 p-4 rounded-xl border border-gray-700/40">
                        <?php if (!empty($userToRate['foto'])): ?>
                            <img src="<?= BASE_PATH ?>/public/uploads/profiles/<?= $userToRate['foto'] ?>"
                                 alt="<?= htmlspecialchars($userToRate['nombre']) ?>"
                                 class="w-14 h-14 rounded-xl object-cover ring-2 ring-primary/30 shrink-0">
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-lg font-bold text-secondary shrink-0">
                                <?= strtoupper(substr($userToRate['nombre'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="text-lg font-bold text-white truncate"><?= htmlspecialchars($userToRate['nombre']) ?></p>
                            <p class="text-sm text-gray-400 mt-0.5 flex items-center gap-1.5">
                                <i class="fas fa-user-tag text-primary text-xs" aria-hidden="true"></i>
                                <span class="capitalize"><?= $userRole ?></span>
                            </p>
                        </div>
                        <a href="<?= url('/profile') ?>?id=<?= $userToRate['id'] ?>" class="text-primary hover:text-primary-dark transition-colors shrink-0">
                            <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Info de ayuda -->
                <div class="bg-surface rounded-2xl border border-gray-700 p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center"><i class="fas fa-lightbulb text-yellow-400 text-sm" aria-hidden="true"></i></div>
                        <?= t('rating.tips_title') ?>
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <i class="fas fa-check-circle text-primary mt-0.5 shrink-0" aria-hidden="true"></i>
                            <?= t('rating.tip_honest') ?>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <i class="fas fa-check-circle text-primary mt-0.5 shrink-0" aria-hidden="true"></i>
                            <?= t('rating.tip_constructive') ?>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <i class="fas fa-check-circle text-primary mt-0.5 shrink-0" aria-hidden="true"></i>
                            <?= t('rating.tip_respectful') ?>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    const ratingTexts = {
        1: '<?= t('rating.mood_1') ?>',
        2: '<?= t('rating.mood_2') ?>',
        3: '<?= t('rating.mood_3') ?>',
        4: '<?= t('rating.mood_4') ?>',
        5: '<?= t('rating.mood_5') ?>'
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
            showMessage('<?= t('rating.err_select') ?>', 'error');
            return;
        }

        // Deshabilitar todos los botones submit
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i>Enviando...';
        });

        try {
            const res    = await fetch('<?= url("/rating") ?>', { method: 'POST', body: new FormData(form) });
            const result = await res.json();

            if (result.success) {
                showMessage(result.message || '<?= t('rating.success') ?>', 'success');
                setTimeout(() => { window.location.href = '<?= url("/my-rides") ?>?tab=past-bookings'; }, 2000);
            } else {
                showMessage(result.message || '<?= t('rating.err_submit') ?>', 'error');
                resetButtons();
            }
        } catch (err) {
            showMessage('<?= t('rating.err_connection') ?>', 'error');
            resetButtons();
        }
    });

    function resetButtons() {
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-2" aria-hidden="true"></i><?= t('rating.submit') ?>';
        });
    }

    function showMessage(message, type) {
        const isSuccess = type === 'success';
        responseMsg.className = `mb-6 bg-${isSuccess ? 'green' : 'red'}-500/10 border border-${isSuccess ? 'green' : 'red'}-500/50 text-${isSuccess ? 'green' : 'red'}-500 p-4 rounded-xl flex items-center gap-3`;
        responseMsg.innerHTML = `
            <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'} text-xl" aria-hidden="true"></i>
            <div class="font-medium">${message}</div>
        `;
        responseMsg.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>
