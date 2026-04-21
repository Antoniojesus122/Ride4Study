    </div>
</main>
</div>

<?php require_once __DIR__ . '/modals.view.php'; ?>

<script>
    // Toggle de fechas personalizadas en filtros de periodo
    (function () {
        document.querySelectorAll('.period-filter').forEach(function (wrapper) {
            const select = wrapper.querySelector('.period-select');
            const from   = wrapper.querySelector('.period-from');
            const to     = wrapper.querySelector('.period-to');
            if (!select || !from || !to) return;

            function refresh() {
                const isCustom = select.value === 'custom';
                from.classList.toggle('hidden', !isCustom);
                to.classList.toggle('hidden', !isCustom);
                // Si el admin cambia a un periodo predefinido vaciamos las fechas para no ensuciar la URL
                if (!isCustom) { from.value = ''; to.value = ''; }
            }
            select.addEventListener('change', refresh);
        });
    })();
</script>

</body>
</html>
