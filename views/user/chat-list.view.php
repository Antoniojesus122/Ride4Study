<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white">Mensajes</h2>
        <p class="text-gray-400 mt-2">Tus conversaciones con otros usuarios.</p>
    </div>

    <div class="bg-surface rounded-2xl border border-gray-700 overflow-hidden shadow-lg">
        <?php if (empty($chats)): ?>
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="far fa-comments text-3xl text-gray-500"></i>
                </div>
                <h3 class="text-lg font-medium text-white">No hay mensajes aún</h3>
                <p class="text-gray-400 mt-2">Contacta con conductores o pasajeros para empezar.</p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-700">
                <?php foreach ($chats as $chat): ?>
                    <li>
                        <a href="<?= url('/chat') ?>?user_id=<?= $chat['idUsuario'] ?>" class="block hover:bg-white/5 transition-colors p-4">
                            <div class="flex items-center gap-4">
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
                                        <h4 class="text-base font-bold text-white truncate"><?= htmlspecialchars($chat['nombre']) ?></h4>
                                        <span class="text-xs text-gray-500"><?= date('d M H:i', strtotime($chat['fechaCreacion'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-400 truncate <?php echo (!$chat['leido'] && $chat['idEmisor'] != $_SESSION['user_id']) ? 'font-semibold text-white' : ''; ?>">
                                        <?= ($chat['idEmisor'] == $_SESSION['user_id'] ? 'Tú: ' : '') . htmlspecialchars($chat['mensaje']) ?>
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
    if(confirm('¿Estás seguro de que deseas eliminar esta conversación completa?')) {
        document.getElementById('delete-user-id').value = userId;
        document.getElementById('delete-chat-form').submit();
    }
}
</script>

</body>
</html>
