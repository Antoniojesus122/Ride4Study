<main class="flex-1 p-8">
    <header class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold">Dashboard</h1>
        <div class="text-sm text-gray-400">
            Admin: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?>
        </div>
    </header>

    <!-- Estadísticas -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-gray-800 p-6 rounded-2xl">
            <p class="text-sm text-gray-400">Usuarios</p>
            <p class="text-3xl font-bold">1.245</p>
        </div>

        <div class="bg-gray-800 p-6 rounded-2xl">
            <p class="text-sm text-gray-400">Anuncios activos</p>
            <p class="text-3xl font-bold">342</p>
        </div>

        <div class="bg-gray-800 p-6 rounded-2xl">
            <p class="text-sm text-gray-400">Reportes pendientes</p>
            <p class="text-3xl font-bold text-red-400">18</p>
        </div>

        <div class="bg-gray-800 p-6 rounded-2xl">
            <p class="text-sm text-gray-400">Instituciones</p>
            <p class="text-3xl font-bold">27</p>
        </div>
    </section>

    <!-- Acciones rápidas -->
    <section>
        <h2 class="text-xl font-semibold mb-4">Acciones rápidas</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="admin_users.php" class="bg-primary text-secondary p-6 rounded-2xl font-bold hover:opacity-90">
                Crear usuario
            </a>

            <a href="admin_reports.php" class="bg-gray-800 p-6 rounded-2xl hover:bg-gray-700">
                Revisar reportes
            </a>

            <a href="admin_ads.php" class="bg-gray-800 p-6 rounded-2xl hover:bg-gray-700">
                Moderar anuncios
            </a>
        </div>
    </section>
</main>
