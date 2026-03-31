<?php foreach ($messages as $msg): ?>
    <?php $isMe = $msg['idEmisor'] == $_SESSION['user_id']; ?>

    <div class="flex w-full <?= $isMe ? 'justify-end' : 'justify-start' ?> group" id="msg-<?= $msg['idMensaje'] ?>">
        <div class="max-w-[75%] md:max-w-[60%]">
            <div class="px-3.5 py-2 rounded-xl text-[15px] <?= $isMe
                ? 'bg-primary/15 text-gray-100 rounded-br-none'
                : 'bg-surface-light/60 text-gray-200 rounded-bl-none' ?>">
                <p class="whitespace-pre-wrap message-content leading-snug"><?= htmlspecialchars($msg['mensaje']) ?></p>
                <p class="text-[10px] mt-0.5 <?= $isMe ? 'text-primary/50 text-right' : 'text-gray-500 text-right' ?>">
                    <?= date('H:i', strtotime($msg['fechaCreacion'])) ?>
                    <?php if ($isMe): ?>
                        <i class="fas fa-check<?= $msg['leido'] ? '-double text-primary/70' : '' ?> ml-0.5"></i>
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($isMe && (time() - strtotime($msg['fechaCreacion']) < 3600)): ?>
            <div class="hidden group-hover:flex items-center gap-2.5 mt-0.5 justify-end pr-1">
                <button onclick="editMessage(<?= $msg['idMensaje'] ?>)" class="text-[10px] text-gray-600 hover:text-gray-300 transition"><i class="fas fa-pen"></i></button>
                <button onclick="deleteMessage(<?= $msg['idMensaje'] ?>)" class="text-[10px] text-gray-600 hover:text-red-400 transition"><i class="fas fa-trash"></i></button>
            </div>
            <?php elseif (!$isMe): ?>
            <div class="hidden group-hover:flex items-center mt-0.5 pl-1">
                <button onclick="openReportModal('chat', {idChat: <?= (int)$msg['idMensaje'] ?>, idUsuario: <?= (int)$msg['idEmisor'] ?>})" class="text-[10px] text-gray-600 hover:text-red-400 transition"><i class="fas fa-flag"></i></button>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
