<?php $pageTitle = 'Mensajes instituciones'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10">

    <!-- Flash messages -->
    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
            <?= htmlspecialchars($flashData['message']) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">Error: <?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <!-- Cabecera -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Mensajes con instituciones</h1>
            <p class="text-sm text-gray-500 mt-1">Comunicación directa admin <span class="text-gray-600">&harr;</span> institución. Los mensajes privados entre usuarios no son visibles aquí.</p>
        </div>
        <?php if ($institucionActiva): ?>
            <button type="button" onclick="openNewThread()"
                    class="px-5 py-2.5 bg-primary text-secondary text-sm font-semibold rounded-lg hover:bg-primary-dark hover:text-white transition flex items-center gap-2">
                <i class="fas fa-pen" aria-hidden="true"></i>
                Nuevo hilo
            </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">

        <!-- Columna 1: bandeja de instituciones -->
        <aside class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden h-fit">
            <!-- Buscador -->
            <form method="GET" action="<?= url('/admin/messages') ?>" class="p-4 border-b border-gray-700">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Buscar institucion..."
                       class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </form>

            <!-- Listado -->
            <?php if (empty($instituciones)): ?>
                <div class="p-6 text-center text-sm text-gray-500">Sin instituciones</div>
            <?php else: ?>
                <ul class="divide-y divide-gray-700/60 max-h-[70vh] overflow-y-auto">
                    <?php foreach ($instituciones as $inst):
                        $isActiva = $institucionActiva && (int)$institucionActiva['idInstitucion'] === (int)$inst['idInstitucion'];
                        $noLeidos = (int)$inst['no_leidos'];
                    ?>
                    <li>
                        <a href="<?= url('/admin/messages') ?>?institucion=<?= (int)$inst['idInstitucion'] ?>"
                           class="flex items-start gap-3 p-4 hover:bg-gray-800 transition <?= $isActiva ? 'bg-primary/5 border-l-2 border-l-primary' : '' ?>">
                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <?php if (!empty($inst['logo'])): ?>
                                    <img src="<?= url('/public/uploads/institutions/') . htmlspecialchars($inst['logo']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-university" aria-hidden="true"></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-200 truncate"><?= htmlspecialchars($inst['nombre']) ?></p>
                                    <?php if ($inst['ultima_fecha']): ?>
                                        <span class="text-xs text-gray-500 shrink-0"><?= date('d/m', strtotime($inst['ultima_fecha'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                    <?php if ($inst['ultimo_asunto']): ?>
                                        <?php if ($inst['ultimo_emisor'] === 'admin'): ?>
                                            <span class="text-gray-600">Tu:</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($inst['ultimo_asunto']) ?>
                                    <?php else: ?>
                                        <span class="italic text-gray-600">Sin mensajes todavia</span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($noLeidos > 0): ?>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500/15 text-red-400 border border-red-500/30">
                                        <?= $noLeidos ?> sin leer
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>

        <!-- Columna 2: panel derecho -->
        <section class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">

        <?php if (!$institucionActiva): ?>
            <!-- Estado vacio: ninguna institucion seleccionada -->
            <div class="flex flex-col items-center justify-center py-24 px-6 text-center">
                <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-envelope-open-text text-xl text-gray-500" aria-hidden="true"></i>
                </div>
                <p class="text-gray-300 font-medium">Selecciona una institucion</p>
                <p class="text-sm text-gray-500 mt-1">Elige una institucion de la bandeja para ver sus hilos y responder.</p>
            </div>

        <?php elseif (empty($asunto)): ?>
            <!-- Listado de hilos de la institucion -->
            <header class="px-6 py-4 border-b border-gray-700 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <i class="fas fa-university" aria-hidden="true"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-white truncate"><?= htmlspecialchars($institucionActiva['nombre']) ?></p>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($institucionActiva['correo']) ?></p>
                </div>
            </header>

            <?php if (empty($hilos)): ?>
                <div class="py-20 text-center">
                    <p class="text-gray-400 font-medium">Sin hilos todavia</p>
                    <p class="text-sm text-gray-500 mt-1">Pulsa "Nuevo hilo" para iniciar la conversacion.</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-700/60">
                    <?php foreach ($hilos as $h): ?>
                    <li>
                        <a href="<?= url('/admin/messages') ?>?institucion=<?= (int)$institucionActiva['idInstitucion'] ?>&asunto=<?= urlencode($h['asunto']) ?>"
                           class="flex items-center gap-4 px-6 py-4 hover:bg-gray-800/60 transition">
                            <div class="w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-envelope text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-200 truncate">
                                    <?= htmlspecialchars($h['asunto']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?= (int)$h['total'] ?> mensaje<?= (int)$h['total'] !== 1 ? 's' : '' ?>
                                    <?php if ($h['ultimo_emisor'] === 'admin'): ?>
                                        <span class="text-gray-600">· última respuesta: admin</span>
                                    <?php else: ?>
                                        <span class="text-gray-600">· última respuesta: institución</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php if ((int)$h['no_leidos'] > 0): ?>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500/15 text-red-400 border border-red-500/30">
                                    <?= (int)$h['no_leidos'] ?> nuevos
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-500 shrink-0">
                                <?= date('d/m/Y H:i', strtotime($h['ultima_fecha'])) ?>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        <?php else: ?>
            <!-- Hilo concreto: mensajes + formulario de respuesta -->
            <header class="px-6 py-4 border-b border-gray-700 flex items-center gap-3">
                <a href="<?= url('/admin/messages') ?>?institucion=<?= (int)$institucionActiva['idInstitucion'] ?>"
                   class="text-gray-400 hover:text-white transition" title="Volver a los hilos">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-white truncate"><?= htmlspecialchars($asunto) ?></p>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($institucionActiva['nombre']) ?></p>
                </div>
            </header>

            <div class="p-6 space-y-4 max-h-[55vh] overflow-y-auto">
                <?php foreach ($hiloMensajes as $m):
                    $esAdmin = $m['emisor'] === 'admin';
                ?>
                <div class="flex <?= $esAdmin ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[75%] rounded-xl px-4 py-3 border <?= $esAdmin ? 'bg-primary/10 border-primary/20' : 'bg-gray-700/60 border-gray-600/50' ?>">
                        <div class="flex items-center justify-between gap-4 mb-1">
                            <span class="text-xs font-semibold <?= $esAdmin ? 'text-primary' : 'text-blue-400' ?>">
                                <?= $esAdmin ? htmlspecialchars($m['admin_nombre'] ?? 'Admin') . ' (admin)' : htmlspecialchars($institucionActiva['nombre']) ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($m['creado_en'])) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-200 whitespace-pre-line break-words"><?= htmlspecialchars($m['mensaje']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Responder -->
            <form method="POST" action="<?= url('/admin/messages/send') ?>" class="p-5 border-t border-gray-700 bg-gray-900/40">
                <input type="hidden" name="idInstitucion" value="<?= (int)$institucionActiva['idInstitucion'] ?>">
                <input type="hidden" name="asunto" value="<?= htmlspecialchars($asunto) ?>">
                <textarea name="mensaje" rows="3" required maxlength="5000"
                          placeholder="Escribe tu respuesta..."
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary resize-none"></textarea>
                <div class="flex items-center justify-end mt-3">
                    <button type="submit" class="px-5 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg hover:bg-primary-dark hover:text-white transition flex items-center gap-2">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        Responder
                    </button>
                </div>
            </form>
        <?php endif; ?>

        </section>
    </div>

    </div>
</main>

<!-- Modal: nuevo hilo -->
<?php if ($institucionActiva): ?>
<div id="newThreadModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeNewThread()"></div>
    <div class="relative w-full max-w-lg bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
        <div class="px-6 pt-6 pb-2 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Nuevo hilo con <?= htmlspecialchars($institucionActiva['nombre']) ?></h3>
            <button type="button" onclick="closeNewThread()" class="text-gray-500 hover:text-gray-200 transition">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= url('/admin/messages/send') ?>" class="px-6 pb-6 pt-2 space-y-4">
            <input type="hidden" name="idInstitucion" value="<?= (int)$institucionActiva['idInstitucion'] ?>">

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Asunto</label>
                <input type="text" name="asunto" required maxlength="255"
                       placeholder="Ej: Actualizacion de datos de contacto"
                       class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Mensaje</label>
                <textarea name="mensaje" rows="5" required maxlength="5000"
                          placeholder="Escribe el mensaje..."
                          class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeNewThread()"
                        class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-primary text-secondary rounded-lg hover:bg-primary-dark hover:text-white transition">
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewThread() {
        const m = document.getElementById('newThreadModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeNewThread() {
        const m = document.getElementById('newThreadModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNewThread();
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
