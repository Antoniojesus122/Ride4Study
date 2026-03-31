<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 xl:px-14 py-8">
    <div class="mb-8">
        <h2 class="text-3xl lg:text-4xl font-bold text-white"><?= t('chatlist.title') ?></h2>
        <p class="text-gray-400 mt-2 lg:text-lg"><?= t('chatlist.subtitle') ?></p>
    </div>

    <?php if (!empty($chats)): ?>
        <div class="mb-6 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-500"></i>
            </div>
            <input type="text" id="chat-search" placeholder="<?= t('chatlist.search_placeholder') ?>" class="w-full pl-11 pr-4 py-3 bg-surface border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/30 transition-colors">
        </div>
    <?php endif; ?>

    <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden shadow-lg">
        <?php if (empty($chats)): ?>
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="far fa-comments text-3xl text-gray-500"></i>
                </div>
                <h3 class="text-lg font-medium text-white"><?= t('chatlist.no_messages') ?></h3>
                <p class="text-gray-400 mt-2"><?= t('chatlist.no_messages_desc') ?></p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-700">
                <?php foreach ($chats as $chat): ?>
                    <li>
                        <a href="<?= url('/chat') ?>?user_id=<?= $chat['idUsuario'] ?>" class="block hover:bg-white/5 transition-colors p-3 sm:p-4">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-lg font-bold text-white">
                                        <?= strtoupper(substr($chat['nombre'], 0, 2)) ?>
                                    </div>
                                    <?php if (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']): ?>
                                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-[#1F2937]"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <h4 class="text-base lg:text-lg font-bold text-white truncate"><?= htmlspecialchars($chat['nombre']) ?></h4>
                                        <span class="text-xs lg:text-sm text-gray-500"><?= date('d M H:i', strtotime($chat['fechaCreacion'])) ?></span>
                                    </div>
                                    <p class="text-sm lg:text-base text-gray-400 truncate <?php echo (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']) ? 'font-semibold text-white' : ''; ?>">
                                        <?= ($chat['idEmisor'] == $_SESSION['user_id'] ? t('chatlist.you') . ' ' : '') . htmlspecialchars($chat['mensaje']) ?>
                                    </p>
                                </div>
                                <button type="button" class="p-2 text-gray-500 hover:text-red-400 transition-colors z-10 relative" onclick="event.preventDefault(); confirmDeleteChat(<?= $chat['idUsuario'] ?>)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<form id="delete-chat-form" action="<?= url('/messages') ?>?action=delete_conversation" method="POST" class="hidden">
    <input type="hidden" name="user_id" id="delete-user-id">
</form>

<script>
    function confirmDeleteChat(userId) {
        if(confirm('<?= t('chatlist.delete_confirm') ?>')) {
            document.getElementById('delete-user-id').value = userId;
            document.getElementById('delete-chat-form').submit();
        }
    }

    // Buscador de conversaciones
    const searchInput = document.getElementById('chat-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.bg-surface ul li');
            let visibleCount = 0;
            items.forEach(item => {
                const name = item.querySelector('h4')?.textContent.toLowerCase() || '';
                const msg = item.querySelector('p')?.textContent.toLowerCase() || '';
                const match = name.includes(query) || msg.includes(query);
                item.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
        });
    }
</script>

</body>
</html>
