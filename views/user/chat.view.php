<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Vista completa del chat -->
<div class="h-[calc(100vh-80px)] flex flex-col md:flex-row overflow-hidden bg-[#111827]">
    
    <!-- Barra lateral de chats -->
    <div class="w-full md:w-80 lg:w-96 border-r border-gray-700 bg-surface flex flex-col shrink-0 <?= $selectedConversationId ? 'hidden md:flex' : 'flex' ?>">
        
        <!-- Header de chats -->
        <div class="p-4 border-b border-gray-700">
            <h2 class="text-xl font-bold text-white">Mensajes</h2>
        </div>

        <!-- Listado de conversaciones -->
        <div class="flex-1 overflow-y-auto">
            <?php if (empty($chats)): ?>
                <div class="p-8 text-center text-gray-400">
                    <i class="far fa-comments text-4xl mb-3 opacity-50"></i>
                    <p class="text-sm">No tienes conversaciones activas.</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-800">
                    <?php foreach ($chats as $chat): ?>
                        <?php $isActive = ($selectedConversationId == $chat['idConversation']); ?>
                        <li>
                            <a href="chat.php?conversation_id=<?= $chat['idConversation'] ?>" class="block p-4 hover:bg-white/5 transition-colors <?= $isActive ? 'bg-white/5 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-sm font-bold text-white uppercase">
                                            <?= substr($chat['otherUserName'], 0, 2) ?>
                                        </div>
                                        <?php if (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']): ?>
                                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-surface"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline mb-0.5">
                                            <h4 class="text-sm font-bold text-white truncate <?= $isActive ? 'text-primary' : '' ?>"><?= htmlspecialchars($chat['otherUserName']) ?></h4>
                                            <span class="text-[10px] text-gray-500 shrink-0 ml-2"><?= date('H:i', strtotime($chat['fechaCreacion'])) ?></span>
                                        </div>
                                        <!-- Ruta del anuncio -->
                                        <p class="text-[10px] text-primary/70 truncate mb-0.5">
                                            <i class="fas fa-car text-[9px] mr-1"></i>
                                            <?= htmlspecialchars($chat['nombreOrigen']) ?> → <?= htmlspecialchars($chat['nombreDestino']) ?>
                                        </p>
                                        <!-- Último mensaje -->
                                        <p class="text-xs text-gray-400 truncate <?= (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']) ? 'font-semibold text-white' : '' ?>">
                                            <?php if ($chat['idEmisor'] == $_SESSION['user_id']): ?>
                                                <i class="fas fa-reply text-[10px] mr-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($chat['mensaje']) ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex-1 flex flex-col min-w-0 bg-[#0B1120] relative <?= !$selectedConversationId ? 'hidden md:flex' : 'flex' ?>">
        
        <?php if ($selectedConversationId): ?>
            <!-- Encabezado del chat -->
            <div class="h-16 border-b border-gray-700 bg-surface flex items-center justify-between px-4 shrink-0 shadow-sm z-10">
                <div class="flex items-center gap-3">
                    <a href="messages.php" class="md:hidden p-2 -ml-2 text-gray-400 hover:text-white">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-sm font-bold text-white uppercase">
                        <?= substr($otherUser['nombre'], 0, 2) ?>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white leading-tight"><?= htmlspecialchars($otherUser['nombre']) ?></h3>
                        <a href="profile.php?id=<?= $otherUser['idUsuario'] ?>" class="text-xs text-primary hover:underline">Ver perfil</a>
                    </div>
                </div>

                <!-- Opciones de conversación -->
                <button onclick="confirmDeleteConversation(<?= $selectedConversationId ?>)" class="p-2 text-gray-400 hover:text-red-400 transition-colors" title="Eliminar conversación">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <!-- Tarjeta de contexto del anuncio (desde conversations JOIN anuncios) -->
            <?php if ($contextRide): ?>
            <div class="bg-gradient-to-r from-gray-800 to-gray-800/50 border-b border-gray-700 p-4 shrink-0 shadow-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary shrink-0 shadow-inner">
                             <i class="fas fa-car text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-400 mb-1">💬 Conversación sobre este viaje:</p>
                            <div class="flex items-center gap-2 text-white font-semibold text-sm mb-1">
                                <span class="truncate"><?= htmlspecialchars($contextRide['nombreOrigen']) ?></span>
                                <i class="fas fa-arrow-right text-[10px] text-primary shrink-0"></i>
                                <span class="truncate"><?= htmlspecialchars($contextRide['nombreDestino']) ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($contextRide['fechaSalida'])) ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="far fa-clock"></i>
                                    <?= substr($contextRide['horaSalida'], 0, 5) ?>
                                </span>
                                <?php if (!empty($contextRide['precio'])): ?>
                                <span class="flex items-center gap-1 text-primary font-semibold">
                                    <i class="fas fa-euro-sign"></i>
                                    <?= number_format($contextRide['precio'], 2) ?>€
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <a href="reserve.php?ride_id=<?= $contextRide['idAnuncio'] ?>" 
                       class="px-4 py-2 text-xs border border-primary/30 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors whitespace-nowrap shrink-0 font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Solicitar plaza
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Area de mensajes -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                <?php require __DIR__ . '/chat-messages.partial.php'; ?>
            </div>

            <!-- Area del input -->
            <div class="p-4 bg-surface border-t border-gray-700 shrink-0">
                <form action="chat.php?action=send" method="POST" class="flex items-end gap-3">
                    <input type="hidden" name="conversation_id" value="<?= $selectedConversationId ?>">
                    <input type="hidden" name="receiver_id"     value="<?= $otherUser['idUsuario'] ?>">
                    
                    <div class="flex-1 bg-gray-800 rounded-xl border border-gray-600 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                        <textarea name="message" rows="1" class="block w-full bg-transparent p-3 text-white placeholder-gray-500 outline-none text-sm resize-none max-h-32" placeholder="Escribe un mensaje..." required oninput="this.style.height='auto'; this.style.height=this.scrollHeight + 'px'"></textarea>
                    </div>
                    <button type="submit" class="p-3 bg-primary text-secondary rounded-xl hover:bg-primary-dark transition-colors shadow-lg shadow-primary/20">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center text-gray-500 p-8">
                <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <i class="far fa-paper-plane text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Tus Mensajes</h3>
                <p class="text-center max-w-sm">Selecciona una conversación de la lista para ver el historial de chat o comenzar a escribir.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formularios y modales -->
<form id="delete-conversation-form" action="messages.php?action=delete_conversation" method="POST" class="hidden">
    <input type="hidden" name="conversation_id" id="delete-conversation-id">
</form>

<div id="edit-modal" class="fixed inset-0 z-[60] hidden">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md border border-gray-700 shadow-2xl">
             <h3 class="text-white font-bold mb-4">Editar mensaje</h3>
             <form id="edit-form" onsubmit="submitEdit(event)">
                 <input type="hidden" name="message_id" id="edit-msg-id">
                 <textarea name="message" id="edit-msg-text" rows="3" class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-white mb-4 focus:border-primary outline-none"></textarea>
                 <div class="flex justify-end gap-2">
                     <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700">Cancelar</button>
                     <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-secondary font-bold">Guardar</button>
                 </div>
             </form>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('messages-container');
    const textarea  = document.querySelector('textarea[name="message"]');
    const form      = document.querySelector('form[action="chat.php?action=send"]');
    let isUserScrolling = false;
    let refreshInterval;

    if (container) {
        container.scrollTop = container.scrollHeight;

        container.addEventListener('scroll', () => {
             isUserScrolling = container.scrollTop + container.clientHeight < container.scrollHeight - 50;
        });
    }

    if (textarea && form) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    form.submit();
                }
            }
        });
    }

    function fetchMessages() {
        const urlParams        = new URLSearchParams(window.location.search);
        const conversationId   = urlParams.get('conversation_id');
        
        if (!conversationId) return;

        fetch(`chat.php?action=fetch_messages&conversation_id=${conversationId}`)
            .then(response => response.text())
            .then(html => {
                if (container) {
                     const oldScrollTop = container.scrollTop;
                     container.innerHTML = html;
                     if (!isUserScrolling) {
                          container.scrollTop = container.scrollHeight;
                     } else {
                          container.scrollTop = oldScrollTop;
                     }
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    if (window.location.search.includes('conversation_id')) {
        refreshInterval = setInterval(fetchMessages, 3000);
    }
    
    function confirmDeleteConversation(conversationId) {
        if (confirm('¿Eliminar toda la conversación? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-conversation-id').value = conversationId;
            document.getElementById('delete-conversation-form').submit();
        }
    }

    function deleteMessage(id) {
        if (confirm('¿Eliminar este mensaje?')) {
            const formData = new FormData();
            formData.append('message_id', id);
            fetch('chat.php?action=delete', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('msg-' + id);
                    if (el) el.remove();
                }
            });
        }
    }

    function editMessage(id) {
        const text = document.querySelector(`#msg-${id} .message-content`).textContent;
        document.getElementById('edit-msg-id').value   = id;
        document.getElementById('edit-msg-text').value = text;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() { document.getElementById('edit-modal').classList.add('hidden'); }

    function submitEdit(e) {
        e.preventDefault();
        const id      = document.getElementById('edit-msg-id').value;
        const text    = document.getElementById('edit-msg-text').value;
        const formData = new FormData();
        formData.append('message_id', id);
        formData.append('message', text);

        fetch('chat.php?action=edit', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(res => res.json())
        .then(data => {
              if (data.success) {
                  const el = document.querySelector(`#msg-${id} .message-content`);
                  if (el) el.textContent = text;
                  closeEditModal();
              } else { alert(data.error || 'Error'); }
        });
    }
</script>

</body>
</html>
