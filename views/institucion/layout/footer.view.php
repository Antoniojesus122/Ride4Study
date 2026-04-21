    </div>
</main>
</div>

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
                if (!isCustom) { from.value = ''; to.value = ''; }
            }
            select.addEventListener('change', refresh);
        });
    })();
</script>

</body>
</html>
