<?php foreach ($messages as $msg): ?>
    <?php $isMe = $msg['idEmisor'] == $_SESSION['user_id']; ?>

    <div class="flex w-full <?= $isMe ? 'justify-end' : 'justify-start' ?> group" id="msg-<?= $msg['idMensaje'] ?>">
        <div class="max-w-[80%] md:max-w-[65%] lg:max-w-[55%]">
            <div class="relative px-4 py-3 rounded-2xl text-sm lg:text-base shadow-md <?= $isMe
                ? 'bg-gradient-to-br from-primary to-primary-dark text-secondary rounded-br-md'
                : 'bg-surface text-gray-200 rounded-bl-md border border-gray-700/50' ?>">
                <p class="whitespace-pre-wrap message-content leading-relaxed"><?= htmlspecialchars($msg['mensaje']) ?></p>

                <div class="flex items-center justify-end gap-1.5 mt-1.5 <?= $isMe ? 'opacity-70' : 'opacity-50' ?> text-[10px]">
                    <span><?= date('H:i', strtotime($msg['fechaCreacion'])) ?></span>
                    <?php if ($isMe): ?>
                        <i class="fas fa-check<?= $msg['leido'] ? '-double' : '' ?>"></i>
                    <?php endif; ?>

                    <?php if ($isMe && (time() - strtotime($msg['fechaCreacion']) < 3600)): ?>
                        <div class="hidden group-hover:flex gap-2 ml-2 border-l <?= $isMe ? 'border-black/20' : 'border-white/10' ?> pl-2">
                            <button onclick="editMessage(<?= $msg['idMensaje'] ?>)" class="hover:text-white transition-colors" title="<?= t('chat.edit_message') ?>"><i class="fas fa-pen"></i></button>
                            <button onclick="deleteMessage(<?= $msg['idMensaje'] ?>)" class="hover:text-red-300 transition-colors" title="<?= t('chat.delete_message') ?>"><i class="fas fa-trash"></i></button>
                        </div>
                    <?php elseif (!$isMe): ?>
                        <div class="hidden group-hover:flex gap-2 ml-2 border-l border-white/10 pl-2">
                            <button onclick="openReportModal('chat', {idChat: <?= (int)$msg['idMensaje'] ?>, idUsuario: <?= (int)$msg['idEmisor'] ?>})" class="hover:text-red-400 transition-colors" title="<?= t('chat.report_message') ?>"><i class="fas fa-flag text-[9px]"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
