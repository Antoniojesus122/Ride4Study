<!-- Contenedor de modales admin (confirmar, alertar, prompt) -->
<div id="admin-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" role="dialog" aria-modal="true">
    <!-- Fondo oscuro -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-modal-close></div>

    <!-- Tarjeta -->
    <div class="relative w-full max-w-md bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
        <!-- Cabecera -->
        <div class="flex items-start gap-4 px-6 pt-6">
            <div id="admin-modal-icon" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-primary/10 text-primary">
                <i class="fas fa-question text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 id="admin-modal-title" class="text-lg font-semibold text-gray-100">Confirmar accion</h3>
                <p id="admin-modal-message" class="mt-1 text-sm text-gray-400 whitespace-pre-line">Mensaje</p>
            </div>
        </div>

        <!-- Input (solo para prompt) -->
        <div id="admin-modal-input-wrap" class="hidden px-6 pt-4">
            <input id="admin-modal-input" type="text"
                   class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-primary">
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 px-6 py-5 mt-4 bg-gray-800/60">
            <button id="admin-modal-cancel" type="button"
                    class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition">
                Cancelar
            </button>
            <button id="admin-modal-confirm" type="button"
                    class="px-4 py-2 text-sm font-semibold bg-primary text-gray-900 rounded-lg hover:bg-primary-dark hover:text-white transition">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script>
    // Sistema de modales admin
    (function () {
        const modal   = document.getElementById('admin-modal');
        const iconEl  = document.getElementById('admin-modal-icon');
        const titleEl = document.getElementById('admin-modal-title');
        const msgEl   = document.getElementById('admin-modal-message');
        const inpWrap = document.getElementById('admin-modal-input-wrap');
        const inpEl   = document.getElementById('admin-modal-input');
        const btnOk   = document.getElementById('admin-modal-confirm');
        const btnNo   = document.getElementById('admin-modal-cancel');

        let resolver = null;
        let mode = 'confirm'; // confirm | alert | prompt

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(result) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            if (resolver) {
                const r = resolver;
                resolver = null;
                r(result);
            }
        }

        // Estilos por tipo (question, danger, info, success)
        function applyStyle(type) {
            const styles = {
                question: { bg: 'bg-primary/10',  color: 'text-primary',     icon: 'fa-question' },
                danger:   { bg: 'bg-red-500/10',  color: 'text-red-400',     icon: 'fa-triangle-exclamation' },
                info:     { bg: 'bg-blue-500/10', color: 'text-blue-400',    icon: 'fa-circle-info' },
                success:  { bg: 'bg-green-500/10',color: 'text-green-400',   icon: 'fa-circle-check' }
            };
            const s = styles[type] || styles.question;
            iconEl.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 ' + s.bg + ' ' + s.color;
            iconEl.innerHTML = '<i class="fas ' + s.icon + ' text-lg"></i>';

            // Botón de confirmar rojo si es peligroso
            if (type === 'danger') {
                btnOk.className = 'px-4 py-2 text-sm font-semibold bg-red-500 text-white rounded-lg hover:bg-red-600 transition';
            } else {
                btnOk.className = 'px-4 py-2 text-sm font-semibold bg-primary text-gray-900 rounded-lg hover:bg-primary-dark hover:text-white transition';
            }
        }

        // API publica
        window.adminConfirm = function (opts) {
            opts = typeof opts === 'string' ? { message: opts } : (opts || {});
            mode = 'confirm';
            titleEl.textContent = opts.title   || 'Confirmar accion';
            msgEl.textContent   = opts.message || '';
            btnOk.textContent   = opts.confirmText || 'Aceptar';
            btnNo.textContent   = opts.cancelText  || 'Cancelar';
            btnNo.classList.remove('hidden');
            inpWrap.classList.add('hidden');
            applyStyle(opts.danger ? 'danger' : 'question');
            openModal();
            return new Promise(function (resolve) { resolver = resolve; });
        };

        window.adminAlert = function (opts) {
            opts = typeof opts === 'string' ? { message: opts } : (opts || {});
            mode = 'alert';
            titleEl.textContent = opts.title   || 'Aviso';
            msgEl.textContent   = opts.message || '';
            btnOk.textContent   = opts.confirmText || 'Entendido';
            btnNo.classList.add('hidden');
            inpWrap.classList.add('hidden');
            applyStyle(opts.type || 'info');
            openModal();
            return new Promise(function (resolve) { resolver = resolve; });
        };

        window.adminPrompt = function (opts) {
            opts = typeof opts === 'string' ? { message: opts } : (opts || {});
            mode = 'prompt';
            titleEl.textContent = opts.title   || 'Introduce un valor';
            msgEl.textContent   = opts.message || '';
            btnOk.textContent   = opts.confirmText || 'Aceptar';
            btnNo.textContent   = opts.cancelText  || 'Cancelar';
            btnNo.classList.remove('hidden');
            inpWrap.classList.remove('hidden');
            inpEl.value       = opts.defaultValue || '';
            inpEl.placeholder = opts.placeholder  || '';
            applyStyle('info');
            openModal();
            setTimeout(function () { inpEl.focus(); }, 50);
            return new Promise(function (resolve) { resolver = resolve; });
        };

        // Eventos
        btnOk.addEventListener('click', function () {
            if (mode === 'prompt')      closeModal(inpEl.value);
            else if (mode === 'alert')  closeModal(true);
            else                        closeModal(true);
        });

        btnNo.addEventListener('click', function () {
            closeModal(mode === 'prompt' ? null : false);
        });

        // Cerrar al pulsar el fondo
        modal.addEventListener('click', function (e) {
            if (e.target.hasAttribute('data-modal-close')) {
                closeModal(mode === 'prompt' ? null : false);
            }
        });

        // Escape y Enter
        document.addEventListener('keydown', function (e) {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'Escape') {
                closeModal(mode === 'prompt' ? null : false);
            } else if (e.key === 'Enter' && (mode === 'prompt' || mode === 'confirm' || mode === 'alert')) {
                if (document.activeElement === btnNo) return;
                e.preventDefault();
                btnOk.click();
            }
        });

        // Interceptor automatico: cualquier form con [data-confirm]
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm') || form.dataset._confirmed === '1') return;

            e.preventDefault();
            const danger = form.hasAttribute('data-danger');
            window.adminConfirm({
                title:   form.getAttribute('data-confirm-title') || (danger ? 'Confirmar eliminacion' : 'Confirmar accion'),
                message: form.getAttribute('data-confirm'),
                danger:  danger
            }).then(function (ok) {
                if (ok) {
                    form.dataset._confirmed = '1';
                    form.submit();
                }
            });
        }, true);

        // Interceptor automatico: cualquier button con [data-confirm] fuera de submit directo
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-confirm]');
            if (!btn || btn.dataset._confirmed === '1') return;
            if (btn.type === 'submit' && btn.form && btn.form.hasAttribute('data-confirm')) return; // ya lo maneja el submit

            e.preventDefault();
            const danger = btn.hasAttribute('data-danger');
            window.adminConfirm({
                title:   btn.getAttribute('data-confirm-title') || (danger ? 'Confirmar eliminacion' : 'Confirmar accion'),
                message: btn.getAttribute('data-confirm'),
                danger:  danger
            }).then(function (ok) {
                if (ok) {
                    btn.dataset._confirmed = '1';
                    if (btn.type === 'submit' && btn.form) btn.form.submit();
                    else btn.click();
                }
            });
        }, true);
    })();
</script>
