<!-- Footer -->
    <footer class="relative bg-gray-900 border-t border-gray-800">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 items-start">
                <!-- Logo y descripcion -->
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-secondary font-bold text-sm shadow-lg shadow-primary/20">
                            R
                        </div>
                        <span class="font-semibold text-white text-lg tracking-tight">
                            Ride4Study
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-gray-400 max-w-xs leading-relaxed">
                        Conectamos estudiantes que comparten trayectos.
                        Más ahorro, menos emisiones, más comunidad.
                    </p>
                </div>

                <!-- Links a paginas legales -->
                <div class="flex flex-col gap-3 text-sm">
                    <h4 class="text-white font-medium mb-1">Legal</h4>
                    <a href="<?= url('/privacy') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200">Privacidad</a>
                    <a href="<?= url('/terms') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200">Condiciones</a>
                    <a href="<?= url('/support') ?>" class="text-gray-400 hover:text-primary transition-colors duration-200">Soporte</a>
                </div>

                <!-- Info de la web -->
                <div class="flex flex-col gap-3 text-sm">
                    <h4 class="text-white font-medium mb-1">Plataforma</h4>
                    <span class="text-gray-400">Hecho para estudiantes</span>
                    <span class="text-gray-400">España</span>
                    <span class="text-gray-400">Versión 1.0</span>
                </div>
            </div>

            <!-- Separador inferior con copyright -->
            <div class="mt-12 border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-500">
                    © <span id="year"></span> Ride4Study. Todos los derechos reservados.
                </p>
                <p class="text-xs text-gray-500">
                    Hecho por <a href="https://www.linkedin.com/in/antonio-jes%C3%BAs" class="text-primary hover:underline">Antonio Jesús González Domingo</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
