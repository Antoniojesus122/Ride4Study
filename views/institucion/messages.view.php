<?php $pageTitle = 'Mensajes'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<main class="md:ml-[72px] flex-1 min-w-0 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 lg:p-10">

    <?php $flashData = $flashData ?? getFlash(); ?>
    <?php if ($flashData && $flashData['type'] === 'success'): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= htmlspecialchars($flashData['message']) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">
            Error: <?= htmlspecialchars($flashData['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Cabecera -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Mensajes con el equipo de Ride4Study</h1>
            <p class="text-sm text-gray-500 mt-1">Comunicación directa con el administrador de la plataforma.</p>
        </div>
        <button type="button" onclick="openNewThread()"
                class="px-5 py-2.5 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition flex items-center gap-2">
            <i class="fas fa-pen" aria-hidden="true"></i> Nuevo hilo
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">

        <!-- Columna 1: lista de hilos -->
        <aside class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden h-fit">
            <div class="px-4 py-3 border-b border-gray-700 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                Tus hilos
            </div>
            <?php if (empty($hilos)): ?>
                <div class="p-6 text-center text-sm text-gray-500">
                    <i class="fas fa-inbox text-2xl text-gray-600 mb-2 block" aria-hidden="true"></i>
                    Aún no hay hilos. Pulsa "Nuevo hilo" para iniciar una conversación con el admin.
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-700/60 max-h-[70vh] overflow-y-auto">
                    <?php foreach ($hilos as $h):
                        $isActiva = ($asunto !== '' && $asunto === $h['asunto']);
                        $noLeidos = (int)$h['no_leidos'];
                    ?>
                    <li>
                        <a href="<?= url('/institution/messages') ?>?asunto=<?= urlencode($h['asunto']) ?>"
                           class="flex items-start gap-3 p-4 hover:bg-gray-800 transition <?= $isActiva ? 'bg-blue-500/5 border-l-2 border-l-blue-400' : '' ?>">
                            <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-200 truncate"><?= htmlspecialchars($h['asunto']) ?></p>
                                    <?php if ($h['ultima_fecha']): ?>
                                        <span class="text-xs text-gray-500 shrink-0"><?= date('d/m', strtotime($h['ultima_fecha'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                    <?= (int)$h['total'] ?> mensaje<?= (int)$h['total'] !== 1 ? 's' : '' ?>
                                    <?php if ($h['ultimo_emisor'] === 'admin'): ?>
                                        <span class="text-gray-600">· responde admin</span>
                                    <?php else: ?>
                                        <span class="text-gray-600">· tú</span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($noLeidos > 0): ?>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500/15 text-red-400 border border-red-500/30">
                                        <?= $noLeidos ?> nuevo<?= $noLeidos !== 1 ? 's' : '' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>

        <!-- Columna 2: hilo -->
        <section class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <?php if (empty($asunto)): ?>
            <div class="flex flex-col items-center justify-center py-24 px-6 text-center">
                <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-envelope-open-text text-xl text-gray-500" aria-hidden="true"></i>
                </div>
                <p class="text-gray-300 font-medium">Selecciona un hilo</p>
                <p class="text-sm text-gray-500 mt-1">Elige una conversación en la bandeja o inicia un hilo nuevo.</p>
            </div>
        <?php else: ?>
            <header class="px-6 py-4 border-b border-gray-700 flex items-center gap-3">
                <a href="<?= url('/institution/messages') ?>" class="text-gray-400 hover:text-white transition" title="Volver">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-white truncate"><?= htmlspecialchars($asunto) ?></p>
                    <p class="text-xs text-gray-500 truncate">Con el equipo Ride4Study</p>
                </div>
            </header>

            <div class="p-6 space-y-4 max-h-[55vh] overflow-y-auto">
                <?php foreach ($hiloMensajes as $m):
                    $esInst = $m['emisor'] === 'institucion';
                ?>
                <div class="flex <?= $esInst ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[75%] rounded-xl px-4 py-3 border <?= $esInst ? 'bg-blue-500/10 border-blue-500/20' : 'bg-gray-700/60 border-gray-600/50' ?>">
                        <div class="flex items-center justify-between gap-4 mb-1">
                            <span class="text-xs font-semibold <?= $esInst ? 'text-blue-400' : 'text-primary' ?>">
                                <?= $esInst ? 'Tú' : (htmlspecialchars($m['admin_nombre'] ?? 'Admin') . ' (admin)') ?>
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

            <form method="POST" action="<?= url('/institution/messages/send') ?>" class="p-5 border-t border-gray-700 bg-gray-900/40">
                <input type="hidden" name="asunto" value="<?= htmlspecialchars($asunto) ?>">
                <textarea name="mensaje" rows="3" required maxlength="5000"
                          placeholder="Escribe tu respuesta..."
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500 resize-none"></textarea>
                <div class="flex items-center justify-end mt-3">
                    <button type="submit" class="px-5 py-2 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition flex items-center gap-2">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Responder
                    </button>
                </div>
            </form>
        <?php endif; ?>
        </section>
    </div>

    </div>
</main>

<!-- Modal: nuevo hilo -->
<div id="newThreadModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeNewThread()"></div>
    <div class="relative w-full max-w-lg bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
        <div class="px-6 pt-6 pb-2 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Nuevo hilo con el admin</h3>
            <button type="button" onclick="closeNewThread()" class="text-gray-500 hover:text-gray-200 transition">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= url('/institution/messages/send') ?>" class="px-6 pb-6 pt-2 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Asunto</label>
                <input type="text" name="asunto" required maxlength="255"
                       placeholder="Ej: Consulta sobre datos de contacto"
                       class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Mensaje</label>
                <textarea name="mensaje" rows="5" required maxlength="5000"
                          placeholder="Escribe el mensaje..."
                          class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500 resize-none"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeNewThread()" class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewThread() {
        const m = document.getElementById('newThreadModal');
        m.classList.remove('hidden'); m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeNewThread() {
        const m = document.getElementById('newThreadModal');
        m.classList.add('hidden'); m.classList.remove('flex');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNewThread();
    });
</script>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
