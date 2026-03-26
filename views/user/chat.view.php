<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Vista completa del chat -->
<div class="h-[calc(100vh-80px)] flex flex-col md:flex-row overflow-hidden bg-secondary">

    <!-- Barra lateral de chats -->
    <div class="w-full md:w-80 lg:w-96 xl:w-[26rem] border-r border-gray-700 bg-surface flex flex-col shrink-0 <?= $selectedConversationId ? 'hidden md:flex' : 'flex' ?>">

        <!-- Header de chats -->
        <div class="px-5 py-4 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-comments text-primary text-sm"></i>
                    </div>
                    <h2 class="text-lg lg:text-xl font-bold text-white"><?= t('chat.title') ?></h2>
                </div>
                <span class="text-xs text-gray-500 bg-gray-800 px-2.5 py-1 rounded-full"><?= count($chats) ?></span>
            </div>
        </div>

        <!-- Listado de conversaciones -->
        <div class="flex-1 overflow-y-auto">
            <?php if (empty($chats)): ?>
                <div class="p-10 text-center text-gray-500">
                    <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="far fa-comments text-2xl text-gray-600"></i>
                    </div>
                    <p class="text-sm font-medium"><?= t('chat.no_conversations') ?></p>
                </div>
            <?php else: ?>
                <div class="py-2">
                    <?php foreach ($chats as $chat): ?>
                        <?php
                        $isActive = ($selectedConversationId == $chat['idConversation']);
                        $hasUnread = (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']);
                        ?>
                        <a href="<?= url('/chat') ?>?conversation_id=<?= $chat['idConversation'] ?>"
                           class="flex items-center gap-3.5 px-4 py-3.5 mx-2 rounded-xl transition-all <?= $isActive ? 'bg-primary/10 border border-primary/20' : 'hover:bg-white/5 border border-transparent' ?>">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br <?= $isActive ? 'from-primary to-primary-dark' : 'from-gray-700 to-gray-600' ?> flex items-center justify-center text-sm font-bold text-white uppercase shadow-lg">
                                    <?= substr($chat['otherUserName'], 0, 2) ?>
                                </div>
                                <?php if ($hasUnread): ?>
                                    <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full border-2 border-[#161b22] animate-pulse"></div>
                                <?php endif; ?>
                            </div>
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="text-sm lg:text-base font-bold truncate <?= $isActive ? 'text-primary' : 'text-white' ?>"><?= htmlspecialchars($chat['otherUserName']) ?></h4>
                                    <span class="text-[10px] lg:text-xs text-gray-500 shrink-0 ml-2 tabular-nums"><?= date('H:i', strtotime($chat['fechaCreacion'])) ?></span>
                                </div>
                                <p class="text-xs text-primary/60 truncate mb-0.5">
                                    <i class="fas fa-route text-[9px] mr-1"></i>
                                    <?= htmlspecialchars($chat['nombreOrigen']) ?> → <?= htmlspecialchars($chat['nombreDestino']) ?>
                                </p>
                                <p class="text-xs lg:text-sm truncate <?= $hasUnread ? 'font-semibold text-white' : 'text-gray-500' ?>">
                                    <?php if ($chat['idEmisor'] == $_SESSION['user_id']): ?>
                                        <i class="fas fa-reply text-[9px] mr-1 text-gray-600"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($chat['mensaje']) ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex-1 flex flex-col min-w-0 bg-secondary relative <?= !$selectedConversationId ? 'hidden md:flex' : 'flex' ?>">

        <?php if ($selectedConversationId): ?>
            <!-- Encabezado del chat -->
            <div class="border-b border-gray-700 bg-surface flex items-center justify-between px-5 lg:px-6 py-3.5 shrink-0 shadow-lg z-10">
                <div class="flex items-center gap-4">
                    <a href="<?= url('/messages') ?>" class="md:hidden p-2 -ml-2 text-gray-400 hover:text-white">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-sm font-bold text-secondary uppercase shadow-lg shadow-primary/20">
                        <?= substr($otherUser['nombre'], 0, 2) ?>
                    </div>
                    <div>
                        <h3 class="text-base lg:text-lg font-bold text-white leading-tight"><?= htmlspecialchars($otherUser['nombre']) ?></h3>
                        <a href="<?= url('/profile') ?>?id=<?= $otherUser['idUsuario'] ?>" class="text-xs text-primary hover:underline flex items-center gap-1">
                            <i class="fas fa-user text-[9px]"></i> <?= t('chat.view_profile') ?>
                        </a>
                    </div>
                </div>

                <!-- Opciones -->
                <button onclick="confirmDeleteConversation(<?= $selectedConversationId ?>)" class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all" title="<?= t('chat.delete_conversation') ?>">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
            </div>

            <!-- Tarjeta de contexto del anuncio (desde conversations JOIN anuncios) -->
            <?php if ($contextRide): ?>
            <div class="bg-gradient-to-r from-gray-800 to-gray-800/50 border-b border-gray-700 px-4 sm:px-5 py-3 shrink-0 shadow-lg">
                <div class="flex items-center justify-between gap-3 sm:gap-4">
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary shrink-0 shadow-inner">
                             <i class="fas fa-car text-base sm:text-xl"></i>
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
                            <form action="<?= url('/reserve') ?>" method="POST" class="inline">
                                <input type="hidden" name="ride_id" value="<?= $anuncioId ?>">
                                <button type="submit" class="px-4 py-2 text-xs border border-primary/30 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors whitespace-nowrap shrink-0 font-medium shadow-sm cursor-pointer">
                                    <i class="fas fa-user-plus mr-1"></i> <?= t('chat.request_seat') ?>
                                </button>
                            </form>
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
            <div class="flex-1 overflow-y-auto px-4 lg:px-8 py-5 space-y-3" id="messages-container">
                <?php if (!empty($hasMore)): ?>
                <div id="load-more-wrap" class="text-center py-3">
                    <button onclick="loadOlderMessages()" id="load-more-btn" class="px-5 py-2 text-xs font-medium bg-white/5 text-gray-400 rounded-full hover:bg-white/10 hover:text-gray-200 transition border border-gray-700/50">
                        <i class="fas fa-arrow-up mr-1.5"></i> <?= t('chat.load_older') ?>
                    </button>
                </div>
                <?php endif; ?>
                <div id="messages-list">
                    <?php require __DIR__ . '/chat-messages.partial.php'; ?>
                </div>
            </div>

            <!-- Area del input -->
            <div class="px-4 lg:px-8 py-4 bg-surface border-t border-gray-700 shrink-0">
                <form action="<?= url('/chat') ?>?action=send" method="POST" class="flex items-end gap-3">
                    <input type="hidden" name="conversation_id" value="<?= $selectedConversationId ?>">
                    <input type="hidden" name="receiver_id"     value="<?= $otherUser['idUsuario'] ?>">

                    <div class="flex-1 bg-secondary rounded-2xl border border-gray-700 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary/30 transition-all">
                        <textarea name="message" rows="1" class="block w-full bg-transparent px-5 py-3.5 text-white placeholder-gray-600 outline-none text-sm lg:text-base resize-none max-h-32" placeholder="<?= t('chat.write_message') ?>" required oninput="this.style.height='auto'; this.style.height=this.scrollHeight + 'px'"></textarea>
                    </div>
                    <button type="submit" class="w-12 h-12 flex items-center justify-center bg-primary text-secondary rounded-2xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 hover:shadow-primary/40 shrink-0">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Empty state -->
            <div class="flex-1 flex flex-col items-center justify-center p-8">
                <div class="w-28 h-28 bg-gradient-to-br from-primary/10 to-primary/5 rounded-3xl flex items-center justify-center mb-6 border border-primary/10">
                    <i class="far fa-paper-plane text-4xl text-primary/40"></i>
                </div>
                <h3 class="text-xl lg:text-2xl font-bold text-white mb-2"><?= t('chat.your_messages') ?></h3>
                <p class="text-center max-w-sm text-gray-500 lg:text-base"><?= t('chat.select_conversation') ?></p>
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

    // Envío de mensajes por AJAX (sin recargar la página)
    function sendMessageAjax() {
        if (!textarea || !form) return;
        const msg = textarea.value.trim();
        if (!msg) return;

        const formData = new FormData(form);
        textarea.value = '';
        textarea.style.height = 'auto';

        // Añadir mensaje al DOM inmediatamente (optimistic)
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        const tempId = 'msg-temp-' + Date.now();
        const msgHtml = `
            <div class="flex w-full justify-end group" id="${tempId}">
                <div class="max-w-[80%] md:max-w-[65%] lg:max-w-[55%]">
                    <div class="relative px-4 py-3 rounded-2xl text-sm lg:text-base shadow-md bg-gradient-to-br from-primary to-primary-dark text-secondary rounded-br-md">
                        <p class="whitespace-pre-wrap message-content leading-relaxed">${escapeHtml(msg)}</p>
                        <div class="flex items-center justify-end gap-1.5 mt-1.5 opacity-70 text-[10px]">
                            <span>${timeStr}</span>
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>`;
        const messagesList = document.getElementById('messages-list');
        if (messagesList) messagesList.insertAdjacentHTML('beforeend', msgHtml);
        if (container) container.scrollTop = container.scrollHeight;

        // Enviar al servidor
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.message) {
                // Actualizar el ID temporal con el real
                const tempEl = document.getElementById(tempId);
                if (tempEl) tempEl.id = 'msg-' + data.message.idMensaje;
            }
        })
        .catch(() => {
            showChatToast('Error al enviar el mensaje', 'error');
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (textarea && form) {
        // Prevenir submit normal del form
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessageAjax();
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessageAjax();
            }
        });
    }

    function fetchMessages() {
        const urlParams      = new URLSearchParams(window.location.search);
        const conversationId = urlParams.get('conversation_id');
        if (!conversationId) return;

        // Obtener el ID del último mensaje que tenemos
        const allMsgs = document.querySelectorAll('#messages-list [id^="msg-"]');
        let lastId = 0;
        allMsgs.forEach(el => {
            const id = parseInt(el.id.replace('msg-', '').replace('temp-', ''));
            if (!isNaN(id) && id > lastId) lastId = id;
        });

        fetch(`<?= url("/chat") ?>?action=load&conversation_id=${conversationId}&after=${lastId}`)
            .then(response => response.text())
            .then(html => {
                const trimmed = html.trim();
                if (trimmed && container) {
                    const messagesList = document.getElementById('messages-list');
                    if (messagesList) messagesList.insertAdjacentHTML('beforeend', trimmed);
                    if (!isUserScrolling) container.scrollTop = container.scrollHeight;
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

        fetch(`<?= url("/chat") ?>?action=load&conversation_id=${conversationId}&offset=${chatOffset}`)
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
