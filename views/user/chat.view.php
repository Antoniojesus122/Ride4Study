<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Vista completa del chat -->
<div class="h-[calc(100vh-80px)] flex flex-col md:flex-row overflow-hidden bg-[#111827]">
    
    <!-- Barra lateral de chats -->
    <div class="w-full md:w-80 lg:w-96 border-r border-gray-700 bg-surface flex flex-col shrink-0 <?= $selectedConversationId ? 'hidden md:flex' : 'flex' ?>">
        
        <!-- Header de chats -->
        <div class="p-4 border-b border-gray-700">
            <h2 class="text-xl font-bold text-white"><?= t('chat.title') ?></h2>
        </div>

        <!-- Listado de conversaciones -->
        <div class="flex-1 overflow-y-auto">
            <?php if (empty($chats)): ?>
                <div class="p-8 text-center text-gray-400">
                    <i class="far fa-comments text-4xl mb-3 opacity-50"></i>
                    <p class="text-sm"><?= t('chat.no_conversations') ?></p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-800">
                    <?php foreach ($chats as $chat): ?>
                        <?php $isActive = ($selectedConversationId == $chat['idConversation']); ?>
                        <li>
                            <a href="<?= url('/chat') ?>?conversation_id=<?= $chat['idConversation'] ?>" class="block p-4 hover:bg-white/5 transition-colors <?= $isActive ? 'bg-white/5 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
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
                    <a href="<?= url('/messages') ?>" class="md:hidden p-2 -ml-2 text-gray-400 hover:text-white">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-sm font-bold text-white uppercase">
                        <?= substr($otherUser['nombre'], 0, 2) ?>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white leading-tight"><?= htmlspecialchars($otherUser['nombre']) ?></h3>
                        <a href="<?= url('/profile') ?>?id=<?= $otherUser['idUsuario'] ?>" class="text-xs text-primary hover:underline"><?= t('chat.view_profile') ?></a>
                    </div>
                </div>

                <!-- Opciones de conversación -->
                <button onclick="confirmDeleteConversation(<?= $selectedConversationId ?>)" class="p-2 text-gray-400 hover:text-red-400 transition-colors" title="<?= t('chat.delete_conversation') ?>">
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
                            <p class="text-xs text-gray-400 mb-1">💬 <?= t('chat.about_ride') ?></p>
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
                    
                    <!-- Botones según tipo de anuncio -->
                    <?php
                    // Determinar rol del usuario actual en este anuncio
                    $anuncioId = $contextRide['idAnuncio'];

                    $stmtPub = $this->db->prepare("SELECT idUsuario FROM anuncios WHERE idAnuncio = :anuncioId");
                    $stmtPub->execute([':anuncioId' => $anuncioId]);
                    $pubResult = $stmtPub->fetch(PDO::FETCH_ASSOC);
                    $publisherId = $pubResult ? (int)$pubResult['idUsuario'] : null;
                    $isPublisher = ($publisherId === (int)$_SESSION['user_id']);

                    // Buscar si el usuario actual tiene una oferta/reserva en este anuncio
                    $stmtMyOffer = $this->db->prepare(
                        "SELECT estado FROM viajes
                         WHERE idAnuncio = :anuncioId
                         AND (idConductor = :uid1 OR idPasajero = :uid2)"
                    );
                    $stmtMyOffer->execute([
                        ':anuncioId' => $anuncioId,
                        ':uid1' => $_SESSION['user_id'],
                        ':uid2' => $_SESSION['user_id']
                    ]);
                    $myOffer = $stmtMyOffer->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (strtolower($contextRide['anuncioTipo']) === 'ofrezco'): ?>
                        <!-- ANUNCIO TIPO OFREZCO -->
                        <?php if ($isPublisher): ?>
                            <!-- Soy el conductor/publicador: ver solicitudes pendientes -->
                            <?php
                            $stmtPendingPass = $this->db->prepare(
                                "SELECT v.idPasajero, u.nombre as pasajeroNombre
                                 FROM viajes v
                                 JOIN usuarios u ON v.idPasajero = u.idUsuario
                                 WHERE v.idAnuncio = :anuncioId AND v.estado = 'pendiente'
                                 LIMIT 1"
                            );
                            $stmtPendingPass->execute([':anuncioId' => $anuncioId]);
                            $pendingPassenger = $stmtPendingPass->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <?php if ($pendingPassenger): ?>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 mr-1">
                                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($pendingPassenger['pasajeroNombre']) ?>
                                    </span>
                                    <button onclick="handleOfferResponse(<?= $anuncioId ?>, <?= $pendingPassenger['idPasajero'] ?>, 'accept')"
                                            class="px-3 py-1.5 text-xs bg-green-500/10 border border-green-500/30 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors font-medium shadow-sm"
                                            title="<?= t('chat.accept_offer') ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="handleOfferResponse(<?= $anuncioId ?>, <?= $pendingPassenger['idPasajero'] ?>, 'reject')"
                                            class="px-3 py-1.5 text-xs bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors font-medium shadow-sm"
                                            title="<?= t('chat.reject_offer') ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="px-4 py-2 text-xs bg-blue-500/10 text-blue-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-blue-500/30 shadow-sm">
                                    <i class="fas fa-hourglass-half mr-1"></i> <?= t('chat.no_requests') ?>
                                </span>
                            <?php endif; ?>
                        <?php elseif (!$myOffer): ?>
                            <!-- No soy el publicador y no he reservado aún -->
                            <a href="<?= url('/reserve') ?>?ride_id=<?= $anuncioId ?>"
                               class="px-4 py-2 text-xs border border-primary/30 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors whitespace-nowrap shrink-0 font-medium shadow-sm">
                                <i class="fas fa-user-plus mr-1"></i> <?= t('chat.request_seat') ?>
                            </a>
                        <?php elseif ($myOffer['estado'] === 'pendiente'): ?>
                            <span class="px-4 py-2 text-xs bg-yellow-500/10 text-yellow-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-yellow-500/30 shadow-sm">
                                <i class="fas fa-clock mr-1"></i> <?= t('chat.request_sent') ?>
                            </span>
                        <?php elseif ($myOffer['estado'] === 'aceptado'): ?>
                            <span class="px-4 py-2 text-xs bg-green-500/10 text-green-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-green-500/30 shadow-sm">
                                <i class="fas fa-check-circle mr-1"></i> <?= t('chat.seat_confirmed') ?>
                            </span>
                        <?php elseif ($myOffer['estado'] === 'rechazado'): ?>
                            <span class="px-4 py-2 text-xs bg-red-500/10 text-red-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-red-500/30 shadow-sm">
                                <i class="fas fa-times-circle mr-1"></i> <?= t('chat.request_rejected') ?>
                            </span>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- ANUNCIO TIPO BUSCO -->
                        <?php if (!$isPublisher && !$myOffer): ?>
                            <!-- No soy el publicador y no he ofrecido -->
                            <button onclick="offerRide(<?= $anuncioId ?>, <?= $_SESSION['user_id'] ?>)"
                                    class="px-4 py-2 text-xs border border-green-500/30 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors whitespace-nowrap shrink-0 font-medium shadow-sm hover:shadow-md">
                                <i class="fas fa-hand-holding-heart mr-1"></i> <?= t('chat.offer_ride') ?>
                            </button>
                        <?php elseif (!$isPublisher && $myOffer['estado'] === 'pendiente'): ?>
                            <span class="px-4 py-2 text-xs bg-yellow-500/10 text-yellow-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-yellow-500/30 shadow-sm">
                                <i class="fas fa-clock mr-1"></i> <?= t('chat.offer_sent') ?>
                            </span>
                        <?php elseif (!$isPublisher && $myOffer['estado'] === 'aceptado'): ?>
                            <span class="px-4 py-2 text-xs bg-green-500/10 text-green-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-green-500/30 shadow-sm">
                                <i class="fas fa-check-circle mr-1"></i> <?= t('chat.offer_accepted') ?>
                            </span>
                        <?php elseif (!$isPublisher && $myOffer['estado'] === 'rechazado'): ?>
                            <span class="px-4 py-2 text-xs bg-red-500/10 text-red-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-red-500/30 shadow-sm">
                                <i class="fas fa-times-circle mr-1"></i> <?= t('chat.offer_rejected') ?>
                            </span>
                        <?php elseif ($isPublisher): ?>
                            <!-- Soy el publicador (pasajero que busca viaje): gestionar ofertas -->
                            <?php
                            // Buscar oferta pendiente primero
                            $stmtPending = $this->db->prepare(
                                "SELECT v.idConductor, u.nombre as conductorNombre
                                 FROM viajes v
                                 JOIN usuarios u ON v.idConductor = u.idUsuario
                                 WHERE v.idAnuncio = :anuncioId
                                 AND v.idPasajero = :userId
                                 AND v.estado = 'pendiente'
                                 LIMIT 1"
                            );
                            $stmtPending->execute([':anuncioId' => $anuncioId, ':userId' => $_SESSION['user_id']]);
                            $pendingOffer = $stmtPending->fetch(PDO::FETCH_ASSOC);

                            // Si no hay pendiente, buscar oferta aceptada
                            $acceptedOffer = null;
                            if (!$pendingOffer) {
                                $stmtAccepted = $this->db->prepare(
                                    "SELECT v.idConductor, u.nombre as conductorNombre
                                     FROM viajes v
                                     JOIN usuarios u ON v.idConductor = u.idUsuario
                                     WHERE v.idAnuncio = :anuncioId
                                     AND v.idPasajero = :userId
                                     AND v.estado = 'aceptado'
                                     LIMIT 1"
                                );
                                $stmtAccepted->execute([':anuncioId' => $anuncioId, ':userId' => $_SESSION['user_id']]);
                                $acceptedOffer = $stmtAccepted->fetch(PDO::FETCH_ASSOC);
                            }
                            ?>

                            <?php if ($pendingOffer): ?>
                                <!-- Oferta pendiente: botones Aceptar/Rechazar -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 mr-1">
                                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($pendingOffer['conductorNombre']) ?>
                                    </span>
                                    <button onclick="handleOfferResponse(<?= $anuncioId ?>, <?= $pendingOffer['idConductor'] ?>, 'accept')"
                                            class="px-3 py-1.5 text-xs bg-green-500/10 border border-green-500/30 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors font-medium shadow-sm hover:shadow-md"
                                            title="<?= t('chat.accept_offer') ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="handleOfferResponse(<?= $anuncioId ?>, <?= $pendingOffer['idConductor'] ?>, 'reject')"
                                            class="px-3 py-1.5 text-xs bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors font-medium shadow-sm hover:shadow-md"
                                            title="<?= t('chat.reject_offer') ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php elseif ($acceptedOffer): ?>
                                <!-- Oferta aceptada -->
                                <span class="px-4 py-2 text-xs bg-green-500/10 text-green-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-green-500/30 shadow-sm">
                                    <i class="fas fa-check-circle mr-1"></i> <?= t('chat.offer_accepted') ?>
                                </span>
                            <?php else: ?>
                                <!-- Sin ofertas aún -->
                                <span class="px-4 py-2 text-xs bg-blue-500/10 text-blue-400 rounded-lg whitespace-nowrap shrink-0 font-medium border border-blue-500/30 shadow-sm animate-pulse">
                                    <i class="fas fa-hourglass-half mr-1"></i> <?= t('chat.waiting_offers') ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Area de mensajes -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                <?php if (!empty($hasMore)): ?>
                <div id="load-more-wrap" class="text-center py-3">
                    <button onclick="loadOlderMessages()" id="load-more-btn" class="px-4 py-2 text-xs font-medium bg-gray-800 text-gray-400 rounded-lg hover:bg-gray-700 hover:text-gray-200 transition border border-gray-700">
                        <i class="fas fa-arrow-up mr-1"></i> <?= t('chat.load_older') ?>
                    </button>
                </div>
                <?php endif; ?>
                <div id="messages-list">
                    <?php require __DIR__ . '/chat-messages.partial.php'; ?>
                </div>
            </div>

            <!-- Area del input -->
            <div class="p-4 bg-surface border-t border-gray-700 shrink-0">
                <form action="<?= url('/chat') ?>?action=send" method="POST" class="flex items-end gap-3">
                    <input type="hidden" name="conversation_id" value="<?= $selectedConversationId ?>">
                    <input type="hidden" name="receiver_id"     value="<?= $otherUser['idUsuario'] ?>">
                    
                    <div class="flex-1 bg-gray-800 rounded-xl border border-gray-600 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                        <textarea name="message" rows="1" class="block w-full bg-transparent p-3 text-white placeholder-gray-500 outline-none text-sm resize-none max-h-32" placeholder="<?= t('chat.write_message') ?>" required oninput="this.style.height='auto'; this.style.height=this.scrollHeight + 'px'"></textarea>
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
                <h3 class="text-xl font-bold text-white mb-2"><?= t('chat.your_messages') ?></h3>
                <p class="text-center max-w-sm"><?= t('chat.select_conversation') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formularios y modales -->
<form id="delete-conversation-form" action="<?= url('/messages') ?>?action=delete_conversation" method="POST" class="hidden">
    <input type="hidden" name="conversation_id" id="delete-conversation-id">
</form>

<div id="edit-modal" class="fixed inset-0 z-[60] hidden">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md border border-gray-700 shadow-2xl">
             <h3 class="text-white font-bold mb-4"><?= t('chat.edit_message') ?></h3>
             <form id="edit-form" onsubmit="submitEdit(event)">
                 <input type="hidden" name="message_id" id="edit-msg-id">
                 <textarea name="message" id="edit-msg-text" rows="3" class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-white mb-4 focus:border-primary outline-none"></textarea>
                 <div class="flex justify-end gap-2">
                     <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700"><?= t('chat.cancel') ?></button>
                     <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-secondary font-bold"><?= t('chat.save') ?></button>
                 </div>
             </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación del chat -->
<div id="chat-confirm-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4" onclick="if(event.target===this)closeChatConfirm()">
    <div class="bg-surface rounded-2xl border border-gray-700 shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center gap-4">
                <div id="ccm-icon-wrap" class="w-12 h-12 rounded-full flex items-center justify-center">
                    <i id="ccm-icon"></i>
                </div>
                <div>
                    <h3 id="ccm-title" class="text-xl font-bold text-white"></h3>
                    <p id="ccm-subtitle" class="text-sm text-gray-400"></p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <p id="ccm-message" class="text-gray-300 leading-relaxed"></p>
            <div id="ccm-warning-wrap" class="hidden mt-4 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
                <p class="text-sm text-yellow-400 flex items-start gap-2">
                    <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                    <span id="ccm-warning"></span>
                </p>
            </div>
        </div>
        <div class="p-6 bg-gray-800/50 border-t border-gray-700 flex gap-3">
            <button onclick="closeChatConfirm()" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-medium transition-all">
                <i class="fas fa-times mr-2"></i><?= t('chat.cancel') ?>
            </button>
            <button id="ccm-btn" onclick="executeChatConfirm()" class="flex-1 px-4 py-3 text-white rounded-xl font-bold transition-all shadow-lg">
                <?= t('chat.confirm') ?>
            </button>
        </div>
    </div>
</div>

<!-- Toast de notificación del chat -->
<div id="chat-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[80] flex items-center gap-3 px-5 py-3 rounded-xl border shadow-2xl backdrop-blur-sm">
    <i id="chat-toast-icon"></i>
    <p id="chat-toast-text" class="text-sm font-medium text-white"></p>
</div>

<script>
    const container = document.getElementById('messages-container');
    const textarea  = document.querySelector('textarea[name="message"]');
    const form      = document.querySelector('form[action="<?= url("/chat") ?>?action=send"]');
    let isUserScrolling = false;
    let refreshInterval;
    let chatOffset = 30;
    let loadingMore = false;

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

        fetch(`<?= url("/chat") ?>?action=fetch_messages&conversation_id=${conversationId}`)
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

    // Limpiar interval al salir de la página
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) clearInterval(refreshInterval);
    });

    // Pausar polling cuando el tab está oculto, reanudar al volver
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        } else if (window.location.search.includes('conversation_id') && !refreshInterval) {
            fetchMessages();
            refreshInterval = setInterval(fetchMessages, 3000);
        }
    });

    // Modal de confirmación personalizada para el chat
    let _chatConfirmCb = null;

    function showChatConfirm({ iconClass, iconColor, bgColor, title, subtitle, message, warning, confirmText, confirmBg }, onConfirm) {
        document.getElementById('ccm-icon-wrap').className = `w-12 h-12 rounded-full flex items-center justify-center ${bgColor}`;
        document.getElementById('ccm-icon').className      = `${iconClass} ${iconColor} text-xl`;
        document.getElementById('ccm-title').textContent   = title;
        document.getElementById('ccm-subtitle').textContent = subtitle || '';
        document.getElementById('ccm-message').textContent  = message;

        const warnWrap = document.getElementById('ccm-warning-wrap');
        if (warning) {
            document.getElementById('ccm-warning').textContent = warning;
            warnWrap.classList.remove('hidden');
        } else {
            warnWrap.classList.add('hidden');
        }

        const btn = document.getElementById('ccm-btn');
        btn.textContent = confirmText || '<?= t('chat.confirm') ?>';
        btn.className   = `flex-1 px-4 py-3 ${confirmBg} text-white rounded-xl font-bold transition-all shadow-lg`;

        _chatConfirmCb = onConfirm;
        document.getElementById('chat-confirm-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeChatConfirm() {
        document.getElementById('chat-confirm-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        _chatConfirmCb = null;
    }

    function executeChatConfirm() {
        const cb = _chatConfirmCb;
        closeChatConfirm();
        if (cb) cb();
    }

    // Toast de notificación
    let _toastTimer = null;

    function showChatToast(message, type = 'success') {
        const toast = document.getElementById('chat-toast');
        const icon  = document.getElementById('chat-toast-icon');
        const text  = document.getElementById('chat-toast-text');

        const styles = {
            success: { bg: 'bg-green-500/20 border-green-500/40', icon: 'fas fa-check-circle text-green-400' },
            error:   { bg: 'bg-red-500/20 border-red-500/40',     icon: 'fas fa-times-circle text-red-400'   },
            info:    { bg: 'bg-blue-500/20 border-blue-500/40',    icon: 'fas fa-info-circle text-blue-400'   },
        };
        const s = styles[type] || styles.info;

        toast.className  = `fixed bottom-6 left-1/2 -translate-x-1/2 z-[80] flex items-center gap-3 px-5 py-3 rounded-xl border shadow-2xl backdrop-blur-sm ${s.bg}`;
        icon.className   = `text-lg ${s.icon}`;
        text.textContent = message;

        toast.classList.remove('hidden');
        if (_toastTimer) clearTimeout(_toastTimer);
        _toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    // Eliminar conversación completa
    function confirmDeleteConversation(conversationId) {
        showChatConfirm({
            iconClass:  'fas fa-trash-alt',
            iconColor:  'text-red-500',
            bgColor:    'bg-red-500/10',
            title:      '<?= t('chat.delete_conv_title') ?>',
            subtitle:   '<?= t('chat.delete_conv_warning') ?>',
            message:    '<?= t('chat.delete_conv_confirm') ?>',
            confirmText: '<?= t('chat.delete_btn') ?>',
            confirmBg:  'bg-red-500 hover:bg-red-600',
        }, () => {
            document.getElementById('delete-conversation-id').value = conversationId;
            document.getElementById('delete-conversation-form').submit();
        });
    }

    // Eliminar mensaje
    function deleteMessage(id) {
        showChatConfirm({
            iconClass:  'fas fa-trash-alt',
            iconColor:  'text-red-500',
            bgColor:    'bg-red-500/10',
            title:      'Eliminar mensaje',
            subtitle:   'Esta acción no se puede deshacer',
            message:    '¿Estás seguro de que quieres eliminar este mensaje?',
            confirmText: 'Eliminar',
            confirmBg:  'bg-red-500 hover:bg-red-600',
        }, () => {
            const formData = new FormData();
            formData.append('message_id', id);
            fetch('<?= url("/chat") ?>?action=delete', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('msg-' + id);
                    if (el) el.remove();
                }
            });
        });
    }

    // Editar mensaje
    function editMessage(id) {
        const text = document.querySelector(`#msg-${id} .message-content`).textContent;
        document.getElementById('edit-msg-id').value   = id;
        document.getElementById('edit-msg-text').value = text;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() { document.getElementById('edit-modal').classList.add('hidden'); }

    function submitEdit(e) {
        e.preventDefault();
        const id       = document.getElementById('edit-msg-id').value;
        const text     = document.getElementById('edit-msg-text').value;
        const formData = new FormData();
        formData.append('message_id', id);
        formData.append('message', text);

        fetch('<?= url("/chat") ?>?action=edit', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.querySelector(`#msg-${id} .message-content`);
                if (el) el.textContent = text;
                closeEditModal();
            } else {
                showChatToast(data.error || 'No se pudo guardar el mensaje.', 'error');
            }
        });
    }

    // Cargar mensajes anteriores (paginación)
    function loadOlderMessages() {
        if (loadingMore) return;
        loadingMore = true;

        const urlParams      = new URLSearchParams(window.location.search);
        const conversationId = urlParams.get('conversation_id');
        if (!conversationId) return;

        const btn = document.getElementById('load-more-btn');
        if (btn) btn.textContent = '<?= t('chat.loading') ?>...';

        const oldScrollHeight = container.scrollHeight;

        fetch(`<?= url("/chat") ?>?action=fetch_messages&conversation_id=${conversationId}&offset=${chatOffset}`)
            .then(res => res.json())
            .then(data => {
                const messagesList = document.getElementById('messages-list');
                if (messagesList && data.html) {
                    messagesList.insertAdjacentHTML('afterbegin', data.html);
                    chatOffset += 30;

                    // Mantener la posición de scroll
                    container.scrollTop = container.scrollHeight - oldScrollHeight;
                }
                if (!data.hasMore) {
                    const wrap = document.getElementById('load-more-wrap');
                    if (wrap) wrap.remove();
                }
                loadingMore = false;
                if (btn && data.hasMore) {
                    btn.innerHTML = '<i class="fas fa-arrow-up mr-1"></i> <?= t('chat.load_older') ?>';
                }
            })
            .catch(() => {
                loadingMore = false;
                if (btn) btn.innerHTML = '<i class="fas fa-arrow-up mr-1"></i> <?= t('chat.load_older') ?>';
            });
    }

    // Ofrecer llevar a usuario que busca transporte
    function offerRide(anuncioId, userId) {
        showChatConfirm({
            iconClass:  'fas fa-hand-holding-heart',
            iconColor:  'text-green-400',
            bgColor:    'bg-green-500/10',
            title:      'Ofrecer llevarlo',
            subtitle:   'Confirma tu oferta de transporte',
            message:    '¿Confirmas que quieres ofrecer llevar a este usuario en su viaje?',
            warning:    'El usuario recibirá una notificación y podrá aceptar o rechazar tu oferta.',
            confirmText: 'Ofrecer',
            confirmBg:  'bg-green-500 hover:bg-green-600',
        }, () => {
            const formData = new FormData();
            formData.append('anuncio_id', anuncioId);
            formData.append('user_id', userId);

            fetch('<?= url("/chat") ?>?action=offer_ride', {
                method: 'POST',
                body: formData,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showChatToast(data.message || '¡Oferta enviada con éxito!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showChatToast(data.message || 'Error al enviar la oferta.', 'error');
                }
            })
            .catch(() => showChatToast('Error de conexión. Inténtalo de nuevo.', 'error'));
        });
    }

    // Aceptar o rechazar oferta/solicitud de transporte
    function handleOfferResponse(anuncioId, conductorId, action) {
        const isAccept = action === 'accept';
        showChatConfirm({
            iconClass:  isAccept ? 'fas fa-check-circle' : 'fas fa-times-circle',
            iconColor:  isAccept ? 'text-green-400'      : 'text-red-400',
            bgColor:    isAccept ? 'bg-green-500/10'     : 'bg-red-500/10',
            title:      isAccept ? 'Aceptar oferta'      : 'Rechazar oferta',
            subtitle:   'Confirma tu decisión',
            message:    isAccept
                ? '¿Confirmas que quieres aceptar esta oferta de transporte?'
                : '¿Confirmas que quieres rechazar esta oferta de transporte?',
            warning:    isAccept
                ? 'El conductor será notificado de que has aceptado su oferta.'
                : 'El conductor será notificado de que has rechazado su oferta.',
            confirmText: isAccept ? 'Aceptar' : 'Rechazar',
            confirmBg:  isAccept ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600',
        }, () => {
            const formData = new FormData();
            formData.append('ride_id', anuncioId);
            formData.append('passenger_id', conductorId);
            formData.append('action', action);

            fetch('<?= url("/manage-reservation") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (res.ok) {
                    showChatToast(
                        isAccept ? '¡Oferta aceptada con éxito!' : 'Oferta rechazada.',
                        isAccept ? 'success' : 'info'
                    );
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showChatToast('Error al procesar la respuesta.', 'error');
                }
            })
            .catch(() => showChatToast('Error de conexión. Inténtalo de nuevo.', 'error'));
        });
    }
</script>

</body>
</html>
