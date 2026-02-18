<?php foreach ($messages as $msg): ?>
    <?php $isMe = $msg['idEmisor'] == $_SESSION['user_id']; ?>
    
    <!-- Mensaje normal -->
    <div class="flex w-full <?= $isMe ? 'justify-end' : 'justify-start' ?> group" id="msg-<?= $msg['idMensaje'] ?>">
        <div class="max-w-[85%] md:max-w-[70%]">
            <div class="relative px-4 py-2.5 rounded-2xl text-sm shadow-sm <?= $isMe ? 'bg-primary text-secondary rounded-tr-sm' : 'bg-gray-800 text-gray-200 rounded-tl-sm' ?>">
                <p class="whitespace-pre-wrap message-content leading-relaxed"><?= htmlspecialchars($msg['mensaje']) ?></p>
                
                <div class="flex items-center justify-end gap-1.5 mt-1 opacity-60 text-[10px]">
                    <span><?= date('H:i', strtotime($msg['fechaCreacion'])) ?></span>
                    <?php if ($isMe): ?>
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
<?php endforeach; ?>
