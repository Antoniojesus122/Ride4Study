    document.addEventListener('DOMContentLoaded', function() {
        const FIELD_NAMES = {
            messageId: 'idMensaje',
            senderId: 'idEmisor',
            receiverId: 'idReceptor',
            message: 'mensaje',
            created: 'fechaCreacion',
            read: 'leido'
        };
        const messageContainer = document.querySelector('.message-container');
        const messageForm = document.querySelector('#messageForm');
        const messageInput = document.querySelector('#messageInput');
        let lastMessageId = 0;

        // Función que scrollea de forma automática al último mensaje de la conversación
        function scrollToBottom() {
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        }

        // Formatear fecha de envío del mensaje
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
        }

        // Cargar mensajes nuevos
        function loadMessages(conversationId) {
            fetch(`/actions/get_messages.php?conversation_id=${conversationId}&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            appendMessage(message);
                            lastMessageId = Math.max(lastMessageId, message.id);
                        });
                        scrollToBottom();
                    }
                });
        }

        // Añadir mensaje al contenedor
        function appendMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', message.is_sender ? 'message-sent' : 'message-received');
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    <p class="message-text">${message.message}</p>
                    <span class="message-time">${formatDate(message.created_at)}</span>
                </div>
            `;
            
            messageContainer.appendChild(messageDiv);
        }

        // Enviar mensaje
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                const receiverId = this.dataset.receiverId;
                
                if (message) {
                    fetch('/actions/send_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `message=${encodeURIComponent(message)}&receiver_id=${receiverId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            messageInput.value = '';
                            appendMessage({
                                message: message,
                                is_sender: true,
                                created_at: new Date().toISOString()
                            });
                            scrollToBottom();
                        }
                    });
                }
            });
        }

        // Actualizar mensajes cada 3 segundos, la forma más viable que he visto de hacer el chat (No se aprecia que se recargar la página)
        if (messageContainer) {
            const conversationId = messageContainer.dataset.conversationId;
            setInterval(() => loadMessages(conversationId), 3000);
            loadMessages(conversationId);
        }
    });