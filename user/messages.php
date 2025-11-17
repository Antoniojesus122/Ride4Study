        <?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ../index.php');
            exit;
        }

        require_once __DIR__ . '/../includes/db.php';
        $userId = $_SESSION['user_id'];

        try {
            // Obtener datos del usuario actual
            $stmtUser = $pdo->prepare("SELECT nombre, correo, foto_perfil FROM usuarios WHERE idUsuario = ?");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch();
            if (!$user) { session_destroy(); header('Location: ../index.php'); exit; }

            // Obtener la lista de conversaciones existentes
            $stmtConversations = $pdo->prepare("
                SELECT u.idUsuario, u.nombre, u.foto_perfil, m.mensaje, m.fechaCreacion
                FROM mensajes m
                JOIN usuarios u ON u.idUsuario = IF(m.idEmisor = ?, m.idReceptor, m.idEmisor)
                WHERE (m.idEmisor = ? OR m.idReceptor = ?)
                AND m.idMensaje IN (
                    SELECT MAX(idMensaje)
                    FROM mensajes
                    WHERE (idEmisor = ? OR idReceptor = ?)
                    GROUP BY IF(idEmisor = ?, idReceptor, idEmisor)
                )
                ORDER BY m.fechaCreacion DESC
            ");
            $stmtConversations->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
            $conversations = $stmtConversations->fetchAll();

            // Determinar el ID del chat activo con la lógica de prioridades
            $activeChatUserId = $_GET['to'] ?? $_GET['chat_with'] ?? ($conversations[0]['idUsuario'] ?? null);
            
            $activeChatUser = null;
            
            if ($activeChatUserId && $activeChatUserId != $userId) {
                
                $isNewConversation = true;
                foreach ($conversations as $convo) {
                    if ($convo['idUsuario'] == $activeChatUserId) {
                        $isNewConversation = false;
                        break;
                    }
                }

                if ($isNewConversation) {
                    $stmtNewUser = $pdo->prepare("SELECT idUsuario, nombre, foto_perfil FROM usuarios WHERE idUsuario = ?");
                    $stmtNewUser->execute([$activeChatUserId]);
                    $newUser = $stmtNewUser->fetch();

                    if ($newUser) {
                        $placeholderConvo = [
                            'idUsuario' => $newUser['idUsuario'],
                            'nombre' => $newUser['nombre'],
                            'foto_perfil' => $newUser['foto_perfil'],
                            'mensaje' => 'Inicia la conversación...',
                            'fechaCreacion' => date('Y-m-d H:i:s')
                        ];
                        array_unshift($conversations, $placeholderConvo);
                    }
                }
                
                $stmtActiveUser = $pdo->prepare("SELECT idUsuario, nombre, foto_perfil FROM usuarios WHERE idUsuario = ?");
                $stmtActiveUser->execute([$activeChatUserId]);
                $activeChatUser = $stmtActiveUser->fetch();

                if (!$activeChatUser) {
                    $activeChatUserId = null;
                }
            } else {
                $activeChatUserId = null;
            }

        } catch (PDOException $e) {
            die("Error al cargar los mensajes: " . htmlspecialchars($e->getMessage()));
        }
        ?>

        <!DOCTYPE html>
        <html lang="es" class="h-full">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>Mensajes - Ride4Study</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            colors: {
                                primary: '#6EE7B7', 'primary-dark': '#10B981', secondary: '#374151',
                                background: '#F9FAFB', text: '#1F2937', 'text-muted': '#6B7280',
                            },
                            keyframes: {
                                'fade-in-up': { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' }, }
                            },
                            animation: { 'fade-in-up': 'fade-in-up 0.25s ease-out' }
                        }
                    }
                }
            </script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
            <style>
                html, body { height: 100%; overflow: hidden; }
                #chat-box::-webkit-scrollbar { width: 8px; }
                #chat-box::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 10px; }
                #chat-box::-webkit-scrollbar-track { background: transparent; }
            </style>
        </head>

        <body class="h-full antialiased bg-background">
        <div class="h-full flex flex-col">

            <!-- Menú de navegación -->
            <nav class="bg-white shadow-sm flex-shrink-0 z-30">
                <div class="container mx-auto px-4 lg:px-6">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center gap-4">
                            <a href="dashboard.php" class="flex items-center gap-2 flex-shrink-0"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white"><i class="fas fa-car-side text-lg"></i></div><span class="text-2xl font-bold text-secondary tracking-tighter">RIDE4STUDY</span></a>
                            <div class="hidden md:block"><div class="flex items-baseline space-x-4"><a href="dashboard.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Dashboard</a><a href="my-rides.php" class="text-text-muted hover:bg-gray-100 hover:text-text rounded-md px-3 py-2 text-sm font-medium">Mis Viajes</a><a href="messages.php" class="bg-primary/10 text-primary-dark rounded-md px-3 py-2 text-sm font-semibold">Mensajes</a></div></div>
                        </div>
                        <div class="hidden md:block"><div class="flex items-center gap-4"><span class="text-sm text-text-muted">Hola, <span class="font-semibold text-text"><?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></span></span><a href="profile.php" class="relative"><div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center font-bold text-primary-dark ring-2 ring-primary-dark overflow-hidden"><?php if (!empty($user['foto_perfil'])): ?><img src="../assets/uploads/avatars/<?= htmlspecialchars($user['foto_perfil']) ?>" alt="Foto de perfil" class="w-full h-full object-cover"><?php else: ?><?= strtoupper(substr($user['nombre'], 0, 1)) ?><?php endif; ?></div></a><a href="../actions/logout_action.php" title="Cerrar sesión" class="text-text-muted hover:text-red-500"><i class="fas fa-sign-out-alt fa-lg"></i></a></div></div>
                    </div>
                </div>
            </nav>

            <main class="flex flex-1 overflow-hidden">
                <?php if (empty($conversations)): ?>
                    <div class="flex-1 flex items-center justify-center text-center">
                        <div><i class="fas fa-comments text-6xl text-gray-300"></i><h2 class="mt-6 text-3xl font-bold text-text">Aún no tienes mensajes</h2><p class="mt-2 text-lg text-text-muted">Cuando contactes con un usuario, tu conversación aparecerá aquí.</p><a href="dashboard.php" class="mt-6 inline-block rounded-md bg-primary px-6 py-3 text-sm font-semibold text-secondary shadow-sm hover:bg-primary-dark transition-all"><i class="fas fa-search mr-2"></i> Explorar Viajes</a></div>
                    </div>
                <?php else: ?>
                    <div class="flex flex-1 min-h-0">
                        <aside class="w-full md:w-1/3 lg:w-1/4 border-r bg-white overflow-y-auto">
                            <div class="p-4 border-b sticky top-0 bg-white z-10"><h1 class="text-2xl font-bold text-text">Conversaciones</h1></div>
                            <?php foreach ($conversations as $convo): ?>
                                <a href="?chat_with=<?= $convo['idUsuario'] ?>" class="flex items-center gap-4 p-4 border-b hover:bg-gray-50 transition <?= ($activeChatUserId == $convo['idUsuario']) ? 'bg-primary/10' : '' ?>">
                                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                                        <?php if ($convo['foto_perfil']): ?><img src="../assets/uploads/avatars/<?= htmlspecialchars($convo['foto_perfil']) ?>" class="object-cover w-full h-full"><?php else: ?><div class="flex items-center justify-center w-full h-full bg-secondary/10 text-secondary font-bold text-xl"><?= strtoupper(substr($convo['nombre'], 0, 1)) ?></div><?php endif; ?>
                                    </div>
                                    <div class="flex-1 overflow-hidden"><div class="flex justify-between items-center"><h3 class="font-semibold truncate text-text"><?= htmlspecialchars($convo['nombre']) ?></h3><p class="text-xs text-text-muted"><?= date('H:i', strtotime($convo['fechaCreacion'])) ?></p></div><p class="text-sm text-text-muted truncate"><?= htmlspecialchars($convo['mensaje']) ?></p></div>
                                </a>
                            <?php endforeach; ?>
                        </aside>

                        <section class="hidden md:flex flex-col w-full md:w-2/3 lg:w-3/4 bg-gray-200">
                            <?php if ($activeChatUser): ?>
                                <div class="flex-shrink-0 flex items-center gap-4 p-3 border-b bg-white shadow-sm z-10">
                                    <div class="w-10 h-10 rounded-full overflow-hidden">
                                        <?php if ($activeChatUser['foto_perfil']): ?><img src="../assets/uploads/avatars/<?= htmlspecialchars($activeChatUser['foto_perfil']) ?>" class="object-cover w-full h-full"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-secondary/10 text-secondary font-bold"><?= strtoupper(substr($activeChatUser['nombre'], 0, 1)) ?></div><?php endif; ?>
                                    </div>
                                    <h2 class="font-semibold text-lg text-text"><?= htmlspecialchars($activeChatUser['nombre']) ?></h2>
                                </div>

                                <div id="chat-box" class="flex-1 overflow-y-auto p-4" style="background-image: url('../assets/img/chat-bg.png');"></div>

                                <div class="flex-shrink-0 bg-background p-4">
                                    <form id="send-message-form" class="flex items-center gap-3">
                                        <input type="hidden" id="receptor-id" value="<?= $activeChatUserId ?>">
                                        <input type="text" id="message-input" placeholder="Escribe un mensaje..." class="flex-1 rounded-full border-gray-300 bg-white py-3 px-5 focus:ring-primary-dark focus:border-primary-dark outline-none" autocomplete="off">
                                        <button type="submit" class="rounded-full bg-primary h-12 w-12 flex items-center justify-center text-secondary hover:bg-primary-dark transition-colors text-xl">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="flex-1 flex items-center justify-center text-center">
                                    <div><i class="fas fa-comments text-6xl text-gray-300"></i><h2 class="mt-4 text-2xl font-semibold text-text-muted">Selecciona una conversación</h2></div>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                <?php endif; ?>
            </main>
        </div>

        <script>
        const chatBox = document.getElementById('chat-box');
        const sendMessageForm = document.getElementById('send-message-form');
        const messageInput = document.getElementById('message-input');
        const receptorId = document.getElementById('receptor-id')?.value;
        const currentUserId = <?= $userId ?>;

        async function fetchMessages(scrollToBottom = false) {
            if (!receptorId) return;
            try {
                const response = await fetch(`api/get_messages.php?chat_with=${receptorId}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const messages = await response.json();

                const shouldScroll = scrollToBottom || (chatBox.scrollTop + chatBox.clientHeight) >= chatBox.scrollHeight - 30;

                const existingIds = new Set(Array.from(chatBox.querySelectorAll('[data-id]')).map(e => e.dataset.id));
                
                messages.forEach(msg => {
                    if (existingIds.has(String(msg.idMensaje))) return;

                    const msgDate = new Date(msg.fechaCreacion);
                    const isSent = msg.idEmisor == currentUserId;

                    const msgDiv = document.createElement('div');
                    msgDiv.dataset.id = msg.idMensaje;
                    msgDiv.className = `flex ${isSent ? 'justify-end' : 'justify-start'} mb-2 animate-fade-in-up`;

                    msgDiv.innerHTML = `
                        <div class="max-w-[70%] px-4 py-2 rounded-lg ${
                            isSent
                                ? 'bg-primary/60 text-text rounded-br-lg'
                                : 'bg-white text-text rounded-bl-lg shadow-sm'
                        }">
                            <p class="break-words">${msg.mensaje.replace(/\n/g, '<br>')}</p>
                            <p class="text-[11px] text-text-muted/70 text-right mt-1">${msgDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                    `;
                    chatBox.appendChild(msgDiv);
                });

                if (shouldScroll) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            } catch (err) {
                console.error('Error cargando mensajes:', err);
            }
        }

        if (sendMessageForm) {
            sendMessageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (!message) return;

                messageInput.value = '';

                const formData = new FormData();
                formData.append('mensaje', message);
                formData.append('id_receptor', receptorId);

                try {
                    const response = await fetch('api/send_message.php', { method: 'POST', body: formData });
                    if (response.ok) {
                        fetchMessages(true);
                    }
                } catch (error) {
                    console.error('Error al enviar mensaje:', error);
                    messageInput.value = message;
                }
            });
        }

        if (receptorId) {
            fetchMessages(true);
            setInterval(() => fetchMessages(), 3000);
        }
        </script>
        </body>
        </html>