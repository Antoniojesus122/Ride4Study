<!-- Footer -->
    <footer class="relative bg-gradient-to-b from-black to-slate-950 border-t border-white/5">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 items-start">
                <div>
                    <div class="flex items-center gap-3 group">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-cyan-400 rounded-lg flex items-center justify-center text-black font-bold text-sm shadow-lg shadow-emerald-500/20">
                            R
                        </div>
                        <span class="font-semibold text-white text-lg tracking-tight group-hover:text-emerald-400 transition-colors">
                            Ride4Study
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-gray-400 max-w-xs leading-relaxed">
                        Conectamos estudiantes que comparten trayectos.
                        Más ahorro, menos emisiones, más comunidad.
                    </p>
                </div>

                <div class="flex flex-col gap-4 text-sm">
                    <h4 class="text-white font-medium mb-2">Legal</h4>
                        <a href="privacy.php" class="text-gray-400 hover:text-emerald-400 transition-colors duration-200">Privacidad</a>
                        <a href="terms.php" class="text-gray-400 hover:text-emerald-400 transition-colors duration-200">Condiciones</a>
                        <a href="support.php" class="text-gray-400 hover:text-emerald-400 transition-colors duration-200">Soporte</a>
                </div>

                <div class="flex flex-col gap-4 text-sm">
                    <h4 class="text-white font-medium mb-2">Plataforma</h4>
                        <span class="text-gray-500">Hecho para estudiantes</span>
                        <span class="text-gray-500">España 🇪🇸</span>
                        <span class="text-gray-500">Versión 1.0</span>
                </div>
            </div>

            <div class="mt-12 border-t border-white/5 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-600">
                    © <span id="year"></span> Ride4Study. Todos los derechos reservados.
                </p>
                <p class="text-xs text-gray-600 flex items-center gap-1">
                    Hecho por <a aria-owns=""href="https://www.linkedin.com/in/antonio-jesús" class="text-emerald-400 hover:underline">Antonio Jesús González Domingo</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>