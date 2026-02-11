<?php foreach ($messages as $msg): ?>
    <?php $isMe = $msg['idEmisor'] == $_SESSION['user_id']; ?>
    <?php $isSystem = isset($msg['tipo']) && $msg['tipo'] === 'sistema'; ?>
    
    <?php if ($isSystem): ?>
        <!-- Contexto del viaje, para saber sobre que viaje hablan -->
        <div class="flex w-full justify-center my-4">
            <div class="max-w-md bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <i class="fas fa-info-circle text-blue-400"></i>
                    <span class="text-xs font-bold text-blue-400 uppercase tracking-wide">Información del viaje</span>
                </div>
                <p class="text-sm text-gray-300 whitespace-pre-line leading-relaxed"><?= htmlspecialchars($msg['mensaje']) ?></p>
                <span class="text-[10px] text-gray-500 mt-2 block"><?= date('H:i', strtotime($msg['fechaCreacion'])) ?></span>
            </div>
        </div>
    <?php else: ?>
        <!-- Mensaje normal -->
        <div class="flex w-full <?= $isMe ? 'justify-end' : 'justify-start' ?> group" id="msg-<?= $msg['idMensaje'] ?>">
            <div class="max-w-[85%] md:max-w-[70%]">
                <div class="relative px-4 py-2.5 rounded-2xl text-sm shadow-sm <?= $isMe ? 'bg-primary text-secondary rounded-tr-sm' : 'bg-gray-800 text-gray-200 rounded-tl-sm' ?>">
                    <p class="whitespace-pre-wrap message-content leading-relaxed"><?= htmlspecialchars($msg['mensaje']) ?></p>
                    
                    <div class="flex items-center justify-end gap-1.5 mt-1 opacity-60 text-[10px]">
                        <span><?= date('H:i', strtotime($msg['fechaCreacion'])) ?></span>
                        <?php if($isMe): ?>
                            <i class="fas fa-check<?= $msg['leido'] ? '-double' : '' ?>"></i>
                        <?php endif; ?>
                        
                        <?php if ($isMe && (time() - strtotime($msg['fechaCreacion']) < 3600)): ?>
                            <div class="hidden group-hover:flex gap-2 ml-2 border-l border-black/20 pl-2">
                                <button onclick="editMessage(<?= $msg['idMensaje'] ?>)" class="hover:text-white" title="Editar mensaje"><i class="fas fa-pen"></i></button>
                                <button onclick="deleteMessage(<?= $msg['idMensaje'] ?>)" class="hover:text-red-900" title="Eliminar mensaje"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
