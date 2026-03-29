<?php $pageTitle = 'Mensajes'; ?>
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
            <?php
            $msgs = [
                'conversation_deleted' => 'Conversacion eliminada correctamente',
                'message_deleted' => 'Mensaje eliminado correctamente',
            ];
            echo $msgs[$flashData['message']] ?? 'Operacion realizada';
            ?>
        </div>
    <?php endif; ?>
    <?php if ($flashData && $flashData['type'] === 'error'): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base">Error: <?= htmlspecialchars($flashData['message']) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-base text-gray-400"><?= $totalConversations ?> conversaciones</p>
    </div>

    <!-- Filtros -->
    <form method="GET" action="<?= url('/admin/messages') ?>" class="flex flex-wrap items-center gap-4 mb-6">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Buscar por nombre de usuario..."
               class="px-4 py-2.5 bg-gray-800/60 border border-gray-700 rounded-lg text-base text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary w-80">
        <button type="submit" class="px-5 py-2.5 text-base font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">Filtrar</button>
        <?php if (!empty($search)): ?>
        <a href="<?= url('/admin/messages') ?>" class="text-sm text-gray-400 hover:text-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Lista de conversaciones -->
    <?php if (empty($conversations)): ?>
        <div class="text-center py-20">
            <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            </div>
            <p class="text-gray-400 font-medium">No hay conversaciones</p>
            <?php if (!empty($search)): ?>
            <p class="text-gray-500 text-sm mt-1">No se encontraron resultados para "<?= htmlspecialchars($search) ?>"</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($conversations as $conv):
                $isReported = in_array($conv['idConversation'], $reportedIds);
            ?>
            <div class="bg-gray-800/50 border rounded-xl overflow-hidden <?= $isReported ? 'border-red-500/50 border-l-2 border-l-red-500' : 'border-gray-700' ?>">
                <!-- Header de conversación -->
                <div class="flex flex-col lg:flex-row lg:items-center gap-3 px-6 py-5 cursor-pointer hover:bg-gray-800/70 transition"
                     onclick="toggleConversation(<?= $conv['idConversation'] ?>)">
                    <!-- Usuarios -->
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        <span class="text-base font-medium text-gray-200 truncate"><?= htmlspecialchars($conv['user1_nombre']) ?></span>
                        <span class="text-gray-500 text-sm shrink-0">&harr;</span>
                        <span class="text-base font-medium text-gray-200 truncate"><?= htmlspecialchars($conv['user2_nombre']) ?></span>

                        <?php if ($isReported): ?>
                        <span class="ml-2 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-500/15 text-red-400 border border-red-500/30 shrink-0">Reportado</span>
                        <?php endif; ?>
                    </div>

                    <!-- Estadísticas -->
                    <div class="flex items-center gap-5 shrink-0">
                        <span class="text-sm text-gray-500 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            <?= (int)$conv['total_mensajes'] ?> mensajes
                        </span>
                        <?php if ($conv['ultima_fecha']): ?>
                        <span class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($conv['ultima_fecha'])) ?></span>
                        <?php endif; ?>

                        <!-- Indicador de expansión -->
                        <svg id="chevron-<?= $conv['idConversation'] ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                <!-- Preview del último mensaje -->
                <?php if ($conv['ultimo_mensaje']): ?>
                <div class="px-6 pb-4 -mt-1">
                    <p class="text-sm text-gray-500 truncate max-w-2xl">
                        <span class="text-gray-600">Ultimo:</span> <?= htmlspecialchars(mb_strimwidth($conv['ultimo_mensaje'], 0, 120, '...')) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Botón de eliminar conversación -->
                <div class="px-6 pb-4 flex items-center gap-3">
                    <form method="POST" action="<?= url('/admin/messages/delete-conversation') ?>" onsubmit="return confirm('Eliminar esta conversacion y todos sus mensajes? Esta accion no se puede deshacer.');">
                        <input type="hidden" name="conversation_id" value="<?= $conv['idConversation'] ?>">
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium bg-red-500/10 text-red-400 rounded-md hover:bg-red-500/20 transition border border-red-500/20">
                            Eliminar conversacion
                        </button>
                    </form>
                </div>

                <!-- Panel de mensajes -->
                <div id="messages-<?= $conv['idConversation'] ?>" class="hidden border-t border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-center py-8 text-gray-500">
                            <svg class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Cargando mensajes...
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('/admin/messages') ?>?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>"
               class="px-4 py-2 text-sm rounded-lg transition <?= $i === $page ? 'bg-primary text-gray-900 font-bold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>

<!-- Modal de conversaciones -->
<script>
    const loadedConversations = {};

    function toggleConversation(id) {
        const panel = document.getElementById('messages-' + id);
        const chevron = document.getElementById('chevron-' + id);

        if (!panel) return;

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';

            if (!loadedConversations[id]) {
                fetchMessages(id);
            }
        } else {
            panel.classList.add('hidden');
            if (chevron) chevron.style.transform = '';
        }
    }

    function fetchMessages(id) {
        const panel = document.getElementById('messages-' + id);
        const baseUrl = '<?= url('/admin/messages/view') ?>';

        fetch(baseUrl + '?id=' + id)
            .then(r => r.json())
            .then(data => {
                loadedConversations[id] = true;
                renderMessages(id, data);
            })
            .catch(err => {
                panel.querySelector('.p-6').innerHTML =
                    '<div class="text-center py-6 text-red-400 text-sm">Error al cargar los mensajes</div>';
            });
    }

    function renderMessages(id, data) {
        const panel = document.getElementById('messages-' + id);
        const conv = data.conversation;
        const messages = data.messages;

        if (!messages || messages.length === 0) {
            panel.querySelector('.p-6').innerHTML =
                '<div class="text-center py-6 text-gray-500 text-sm">No hay mensajes en esta conversacion</div>';
            return;
        }

        let html = '<div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">';

        messages.forEach(function(msg) {
            const isUser1 = (parseInt(msg.idEmisor) === parseInt(conv.user1_id));
            const alignClass = isUser1 ? 'mr-auto' : 'ml-auto';
            const bgClass = isUser1
                ? 'bg-gray-700/60 border-gray-600/50'
                : 'bg-primary/10 border-primary/20';
            const nameColor = isUser1 ? 'text-blue-400' : 'text-primary';

            const fecha = msg.fechaCreacion || '';
            let fechaFormatted = '';
            if (fecha) {
                const d = new Date(fecha);
                fechaFormatted = d.toLocaleDateString('es-ES', {day:'2-digit', month:'2-digit', year:'numeric'})
                    + ' ' + d.toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'});
            }

            html += '<div class="flex ' + (isUser1 ? 'justify-start' : 'justify-end') + '">';
            html += '<div class="max-w-[70%] ' + alignClass + ' rounded-xl px-4 py-3 border ' + bgClass + '">';
            html += '<div class="flex items-center justify-between gap-4 mb-1">';
            html += '<span class="text-xs font-semibold ' + nameColor + '">' + escapeHtml(msg.emisor_nombre || 'Usuario') + '</span>';
            html += '<span class="text-xs text-gray-500">' + fechaFormatted + '</span>';
            html += '</div>';
            html += '<p class="text-sm text-gray-300 break-words">' + escapeHtml(msg.mensaje) + '</p>';
            html += '<div class="mt-2 flex justify-end">';
            html += '<button onclick="deleteMessage(' + msg.idMensaje + ', ' + id + ')" class="text-xs text-red-400/60 hover:text-red-400 transition">Eliminar</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';
        panel.querySelector('.p-6').innerHTML = html;
    }

    function deleteMessage(messageId, conversationId) {
        if (!confirm('Eliminar este mensaje?')) return;

        const baseUrl = '<?= url('/admin/messages/delete-message') ?>';

        fetch(baseUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message_id=' + messageId
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadedConversations[conversationId] = false;
                fetchMessages(conversationId);
            } else {
                alert('Error al eliminar el mensaje');
            }
        })
        .catch(() => alert('Error de conexion'));
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text || ''));
        return div.innerHTML;
    }
</script>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>
