<!DOCTYPE html>
    <html lang="es" class="h-full bg-gray-900">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Consejos de Seguridad - Ride4Study</title>

            <script src="https://cdn.tailwindcss.com"></script>
            <script src="public/js/tailwind-config.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

            <style>
                body { font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class="h-full text-white flex flex-col bg-gray-900">
            <?php include __DIR__ . '/../layouts/header.php'; ?>
            
            <!-- Encabezado -->
            <header class="pt-32 pb-16 bg-gradient-to-b from-gray-900 via-gray-900 to-surface">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 mb-6">
                        <i class="fas fa-shield-alt text-2xl text-primary"></i>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Viaja con Seguridad</h1>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                        Consejos esenciales para que cada trayecto sea una experiencia segura, cómoda y de confianza para toda la comunidad.
                    </p>
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="flex-grow bg-surface">
                <div class="mx-auto max-w-4xl px-6 py-12 space-y-6">

                    <!-- Bloque 1: Antes de reservar -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-search text-primary"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">Antes de reservar</h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-star text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Revisa siempre las valoraciones</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Antes de confirmar cualquier viaje, consulta el historial de valoraciones del conductor o pasajero. Una puntuación alta con comentarios positivos es la mejor señal de confianza. Si un perfil no tiene valoraciones aún, lee los comentarios del anuncio con atención y, si tienes dudas, contácta antes de reservar.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-shield-alt text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Prioriza perfiles verificados</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Los usuarios con la insignia de <span class="text-green-400 font-medium">Verificado</span> han enviado documentación que confirma su identidad como estudiantes. Compartir trayecto con usuarios verificados añade una capa extra de seguridad y confianza para todos.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-comments text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Habla con el conductor antes</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Usa el chat integrado para confirmar los detalles del viaje: punto de encuentro exacto, hora de salida, precio y normas del vehículo. Una breve conversación previa evita malentendidos y te da una mejor idea de con quién vas a viajar.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-user-circle text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Consulta el perfil completo</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Visita el perfil del usuario antes de confirmar. Fíjate en su biografía, antigüedad en la plataforma, número de viajes realizados y comentarios recibidos. Un perfil completo y cuidado refleja compromiso con la comunidad.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 2: Durante el viaje -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-cyan-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-car text-cyan-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">Durante el viaje</h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-map-marker-alt text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Comparte tu ubicación con alguien de confianza</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Antes de subir al vehículo, envía a un amigo o familiar los datos del viaje: nombre del conductor, matrícula del coche y hora estimada de llegada. Es un gesto sencillo que añade una seguridad importante.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-id-card text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Verifica la identidad del conductor</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Al encontrarte con el conductor, comprueba que coincide con la foto del perfil. Si algo no encaja, no tienes obligación de subir al vehículo. Tu seguridad siempre va primero.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-mobile-alt text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Mantén el móvil cargado y accesible</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Asegúrate de salir con batería suficiente. Durante el trayecto, tener el teléfono accesible te permite comunicarte con el conductor, con otros pasajeros o llamar en caso de imprevisto.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-cyan-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-route text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Confirma la ruta antes de salir</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Si la ruta que toma el conductor se desvía significativamente de lo acordado sin explicación, no dudes en preguntarlo. Si te sientes incómodo, puedes pedir que te dejen en un lugar seguro.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 3: Para conductores -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-green-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-steering-wheel text-green-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">Consejos para conductores</h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-check-circle text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Acepta solo pasajeros con perfil completo</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Antes de confirmar una solicitud, revisa el perfil del pasajero. Prioriza a quienes tienen foto, valoraciones previas y su correo institucional verificado. Tienes todo el derecho a rechazar solicitudes si no te genera confianza.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-euro-sign text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Establece un precio justo y transparente</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Ride4Study es una plataforma de compartición de gastos entre estudiantes, no un servicio de transporte remunerado. Fija un precio que cubra razonablemente el combustible y los peajes, y comunícalo claramente en el anuncio para evitar malentendidos.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-tools text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Mantén el vehículo en buen estado</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Comprueba antes de cada viaje que el vehículo está en condiciones óptimas: neumáticos, luces, frenos y documentación en regla. La seguridad de tus pasajeros es tu responsabilidad desde el momento en que suben al coche.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-phone text-green-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Comunica cualquier imprevisto</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Si surge un imprevisto (retraso, cambio de ruta, avería), avisa a tus pasajeros cuanto antes a través del chat. La comunicación proactiva genera confianza y te ayuda a mantener una buena reputación en la plataforma.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 4: Después del viaje -->
                    <section class="bg-gray-900/50 rounded-3xl border border-white/5 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-yellow-400/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-star text-yellow-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">Después del viaje</h2>
                        </div>
                        <div class="px-8 py-6 space-y-5">

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-pen text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Valora tu experiencia con honestidad</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Las valoraciones son el corazón de la confianza en Ride4Study. Después de cada viaje, dedica un momento a puntuar y comentar tu experiencia de forma justa. Tus valoraciones ayudan al resto de la comunidad a tomar mejores decisiones.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-flag text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Reporta conductas inapropiadas</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Si durante el viaje o la comunicación previa experimentas un comportamiento irrespetuoso, acoso o cualquier situación que te haga sentir incómodo, usa el sistema de reportes de la plataforma. Nuestro equipo revisará cada caso para mantener la comunidad segura para todos.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-yellow-400/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-lock text-yellow-400 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-1">Cuida tu información personal</h3>
                                    <p class="text-gray-400 text-sm leading-relaxed">
                                        Nunca compartas datos sensibles fuera de la plataforma, como tu número de cuenta bancaria, contraseñas o dirección exacta de casa. Todo el intercambio de información necesario puede hacerse a través del chat integrado de Ride4Study.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- Bloque 5: Emergencias -->
                    <section class="bg-red-950/30 rounded-3xl border border-red-500/20 overflow-hidden">
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-red-500/10">
                            <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">En caso de emergencia</h2>
                        </div>
                        <div class="px-8 py-6">
                            <p class="text-gray-400 text-sm leading-relaxed mb-5">
                                Si en algún momento te sientes en peligro real durante un viaje, actúa de inmediato:
                            </p>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-phone-alt text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-lg">112</p>
                                    <p class="text-gray-400 text-xs mt-1">Emergencias</p>
                                </div>
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-map-pin text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-sm leading-tight">Comparte tu ubicación en tiempo real</p>
                                    <p class="text-gray-400 text-xs mt-1">Con alguien de confianza</p>
                                </div>
                                <div class="bg-gray-900/60 rounded-2xl p-5 border border-red-500/10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-flag text-red-400 text-lg"></i>
                                    </div>
                                    <p class="text-white font-bold text-sm leading-tight">Reporta el incidente</p>
                                    <p class="text-gray-400 text-xs mt-1">Desde tu cuenta en Ride4Study</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CTA bottom -->
                    <div class="text-center py-6">
                        <p class="text-gray-500 text-sm mb-4">¿Tienes alguna duda o quieres reportar algo?</p>
                        <a href="support.php" class="inline-flex items-center gap-2 bg-primary text-secondary font-bold px-6 py-3 rounded-full hover:bg-primary-dark transition-all transform hover:scale-105 shadow-lg shadow-primary/20">
                            <i class="fas fa-headset"></i> Contactar con soporte
                        </a>
                    </div>

                </div>
            </main>

            <!-- Footer -->
            <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
        </body>
    </html>