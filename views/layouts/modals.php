<!-- Contenedor de modales globales (confirmar, alertar, prompt) -->
<!-- Sustituye alert/confirm/prompt nativos del navegador en el lado usuario -->
<div id="app-modal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4" role="dialog" aria-modal="true">
    <!-- Fondo oscuro -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-modal-close></div>

    <!-- Tarjeta -->
    <div class="relative w-full max-w-md bg-gray-800 border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Cabecera -->
        <div class="flex items-start gap-4 px-6 pt-6">
            <div id="app-modal-icon" class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 bg-primary/10 text-primary">
                <i class="fas fa-question text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 id="app-modal-title" class="text-lg font-semibold text-white">Confirmar accion</h3>
                <p id="app-modal-message" class="mt-1 text-sm text-gray-400 whitespace-pre-line">Mensaje</p>
            </div>
        </div>

        <!-- Input (solo para prompt) -->
        <div id="app-modal-input-wrap" class="hidden px-6 pt-4">
            <input id="app-modal-input" type="text"
                   class="w-full px-3 py-2.5 bg-gray-900 border border-white/10 rounded-lg text-sm text-gray-100 placeholder-gray-500 focus:outline-none focus:border-primary">
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end gap-3 px-6 py-5 mt-4 bg-gray-900/40">
            <button id="app-modal-cancel" type="button"
                    class="px-4 py-2 text-sm font-medium bg-white/5 text-gray-200 rounded-lg hover:bg-white/10 transition border border-white/5">
                Cancelar
            </button>
            <button id="app-modal-confirm" type="button"
                    class="px-4 py-2 text-sm font-semibold bg-primary text-secondary rounded-lg hover:bg-primary-dark hover:text-white transition">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal   = document.getElementById('app-modal');
        const iconEl  = document.getElementById('app-modal-icon');
        const titleEl = document.getElementById('app-modal-title');
        const msgEl   = document.getElementById('app-modal-message');
        const inpWrap = document.getElementById('app-modal-input-wrap');
        const inpEl   = document.getElementById('app-modal-input');
        const btnOk   = document.getElementById('app-modal-confirm');
        const btnNo   = document.getElementById('app-modal-cancel');

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

        // Estilos por tipo (question, danger, info, success, warning)
        function applyStyle(type) {
            const styles = {
                question: { bg: 'bg-primary/10',   color: 'text-primary',      icon: 'fa-question' },
                danger:   { bg: 'bg-red-500/10',   color: 'text-red-400',      icon: 'fa-triangle-exclamation' },
                info:     { bg: 'bg-blue-500/10',  color: 'text-blue-400',     icon: 'fa-circle-info' },
                success:  { bg: 'bg-emerald-500/10', color: 'text-emerald-400', icon: 'fa-circle-check' },
                warning:  { bg: 'bg-amber-500/10', color: 'text-amber-400',    icon: 'fa-triangle-exclamation' }
            };
            const s = styles[type] || styles.question;
            iconEl.className = 'w-11 h-11 rounded-full flex items-center justify-center shrink-0 ' + s.bg + ' ' + s.color;
            iconEl.innerHTML = '<i class="fas ' + s.icon + ' text-lg"></i>';

            if (type === 'danger') {
                btnOk.className = 'px-4 py-2 text-sm font-semibold bg-red-500 text-white rounded-lg hover:bg-red-600 transition';
            } else {
                btnOk.className = 'px-4 py-2 text-sm font-semibold bg-primary text-secondary rounded-lg hover:bg-primary-dark hover:text-white transition';
            }
        }

        // API publica: confirmar accion
        window.userConfirm = function (opts) {
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

        // API publica: aviso (un solo boton)
        window.userAlert = function (opts) {
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

        // API publica: pedir texto al usuario
        window.userPrompt = function (opts) {
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

        // Eventos de botones
        btnOk.addEventListener('click', function () {
            if (mode === 'prompt') closeModal(inpEl.value);
            else                   closeModal(true);
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

        // Interceptor automatico: forms con [data-confirm]
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm') || form.dataset._confirmed === '1') return;

            e.preventDefault();
            const danger = form.hasAttribute('data-danger');
            window.userConfirm({
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

        // Interceptor automatico: botones con [data-confirm] fuera de submit directo
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-confirm]');
            if (!btn || btn.dataset._confirmed === '1') return;
            if (btn.type === 'submit' && btn.form && btn.form.hasAttribute('data-confirm')) return;

            e.preventDefault();
            const danger = btn.hasAttribute('data-danger');
            window.userConfirm({
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
