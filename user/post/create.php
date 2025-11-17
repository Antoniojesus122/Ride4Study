    <?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../public/login.php');
        exit;
    }

    require_once __DIR__ . '/../../includes/db.php';

    // Obtener el ID del usuario de la sesión
    $userId = $_SESSION['user_id'];

    // Obtener las localidades para los selectores
    try {
        $stmt = $pdo->query("SELECT idLocalidad, nombreLocalidad FROM localidades ORDER BY nombreLocalidad");
        $localidades = $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Error al cargar las localidades: " . htmlspecialchars($e->getMessage()));
    }

    $error = '';
    $success = '';

    // Procesar el formulario cuando se envía
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_anuncio'])) {
        $tipo = $_POST['tipo'] ?? '';
        $origen = $_POST['origen'] ?? '';
        $destino = $_POST['destino'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $horaRegreso = $_POST['horaRegreso'] ?? '';
        $plazas = $_POST['plazas'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');

        // Validaciones
        if (empty($origen) || empty($destino) || empty($fecha) || empty($hora) || empty($tipo)) {
            $error = 'Por favor, completa todos los campos obligatorios.';
        } elseif ($origen === $destino) {
            $error = 'El origen y destino no pueden ser el mismo.';
        } elseif (strtotime($fecha) < strtotime('today')) {
            $error = 'La fecha no puede ser anterior a hoy.';
        } elseif ($tipo === 'ofrezco' && empty($plazas)) {
            $error = 'Al ofrecer un viaje, debes indicar el número de plazas disponibles.';
        } elseif (!empty($plazas) && (!is_numeric($plazas) || $plazas < 1)) {
            $error = 'El número de plazas debe ser un número positivo.';
        } elseif (!empty($precio) && (!is_numeric($precio) || $precio < 0)) {
            $error = 'El precio debe ser un número positivo.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO anuncios (
                        idUsuario, 
                        tipo,
                        origen, 
                        destino, 
                        fechaSalida,
                        horaSalida,
                        horaRegreso,
                        plazasDisponibles, 
                        precio, 
                        descripcion, 
                        fechaPublicacion
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        NOW()
                    )
                ");
                
                $stmt->execute([
                    $userId,
                    $tipo,
                    $origen,
                    $destino,
                    $fecha,
                    $hora,
                    empty($horaRegreso) ? null : $horaRegreso,
                    empty($plazas) ? null : $plazas,
                    empty($precio) ? null : $precio,
                    empty($descripcion) ? null : $descripcion
                ]);

                $success = '¡Anuncio creado exitosamente!';
                
                // Redireccionar al dashboard después de 2 segundos
                header("refresh:1;url=../dashboard.php");
            } catch (PDOException $e) {
                $error = 'Error al crear el anuncio. Por favor, inténtalo de nuevo.';
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Crear Anuncio - Ride4Study</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#6EE7B7',
                            secondary: '#374151',
                            background: '#F9FAF5',
                            hover: '#10B981',
                            text: '#1F2937'
                        },
                        animation: {
                            'bounce-slow': 'bounce 2s infinite'
                        }
                    }
                }
            }
        </script>
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            .loader-overlay {
                animation: fadeIn 0.3s ease-in-out;
                backdrop-filter: blur(5px);
            }

            .loader-spin {
                animation: spin 1s linear infinite;
            }

            .success-animation {
                animation: fadeIn 0.5s ease-in-out;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body class="min-h-screen bg-background">
        <!-- Menú de Navegación -->
        <nav class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white">
            <div class="container mx-auto flex h-16 items-center justify-between px-4">
                <a href="../../public/index.php" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white shadow-md">
                        <i class="fas fa-car-side text-lg" aria-hidden="true"></i>
                        <span class="sr-only">Logo Ride4Study</span>
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-lg font-bold tracking-tight text-text">RIDE4STUDY</span>
                        <span class="text-[10px] leading-none text-text/70">Viajes compartidos para estudiantes</span>
                    </div>
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    <a href="../dashboard.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Dashboard</a>
                    <a href="../my-rides.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Mis Viajes</a>
                    <a href="../messages.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Mensajes</a>
                    <a href="../profile.php" class="px-3 py-2 text-sm font-medium text-text hover:text-hover transition-colors">Perfil</a>
                    <a href="../../actions/logout_action.php" class="px-3 py-2 text-sm font-medium text-text hover:text-red-500 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> Salir
                    </a>
                </div>
            </div>
        </nav>

        <!-- Contenido -->
        <main class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto">
                <div class="mb-8 space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-text">Crear nuevo anuncio</h1>
                    <p class="text-text/70">Completa el formulario para publicar tu viaje</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl text-sm">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-6">
                    <!-- Tipo de anuncio -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative">
                            <input type="radio" name="tipo" value="ofrezco" class="peer sr-only" required>
                            <div class="p-4 border rounded-xl text-center cursor-pointer transition-colors peer-checked:bg-primary/10 peer-checked:border-primary hover:border-primary/20">
                                <i class="fas fa-car text-2xl mb-2 text-text/70"></i>
                                <p class="font-medium text-text">Ofrezco viaje</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="tipo" value="busco" class="peer sr-only" required>
                            <div class="p-4 border rounded-xl text-center cursor-pointer transition-colors peer-checked:bg-primary/10 peer-checked:border-primary hover:border-primary/20">
                                <i class="fas fa-search text-2xl mb-2 text-text/70"></i>
                                <p class="font-medium text-text">Busco viaje</p>
                            </div>
                        </label>
                    </div>

                    <!-- Origen y Destino -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="origen" class="block text-sm font-medium text-text/70 mb-2">Origen *</label>
                            <select name="origen" id="origen" required class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                                <option value="">Selecciona origen</option>
                                <?php foreach ($localidades as $localidad): ?>
                                    <option value="<?= $localidad['idLocalidad'] ?>">
                                        <?= htmlspecialchars($localidad['nombreLocalidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="destino" class="block text-sm font-medium text-text/70 mb-2">Destino *</label>
                            <select name="destino" id="destino" required class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                                <option value="">Selecciona destino</option>
                                <?php foreach ($localidades as $localidad): ?>
                                    <option value="<?= $localidad['idLocalidad'] ?>">
                                        <?= htmlspecialchars($localidad['nombreLocalidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fecha y Horas -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <label for="fecha" class="block text-sm font-medium text-text/70 mb-2">Fecha de salida *</label>
                            <input type="date" name="fecha" id="fecha" required 
                                min="<?= date('Y-m-d') ?>"
                                class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                        </div>
                        <div class="md:col-span-1">
                            <label for="hora" class="block text-sm font-medium text-text/70 mb-2">Hora de salida *</label>
                            <input type="time" name="hora" id="hora" required
                                class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                        </div>
                        <div class="md:col-span-1">
                            <label for="horaRegreso" class="block text-sm font-medium text-text/70 mb-2">
                                Hora de regreso
                                <span class="text-text/40">(opcional)</span>
                            </label>
                            <input type="time" name="horaRegreso" id="horaRegreso"
                                class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                        </div>
                    </div>

                    <!-- Plazas y Precio -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="plazas" class="block text-sm font-medium text-text/70 mb-2">
                                Plazas disponibles
                                <span class="text-text/40 plazas-opcional">(opcional)</span>
                            </label>
                            <input type="number" name="plazas" id="plazas" min="1" 
                                class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                        </div>
                        <div>
                            <label for="precio" class="block text-sm font-medium text-text/70 mb-2">
                                Precio por persona
                                <span class="text-text/40">(opcional)</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="precio" id="precio" min="0" step="0.01"
                                    class="w-full pl-8 pr-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text/70">€</span>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-text/70 mb-2">
                            Descripción adicional
                            <span class="text-text/40">(opcional)</span>
                        </label>
                        <textarea name="descripcion" id="descripcion" rows="4"
                                class="w-full px-3 py-2 bg-background border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-text"
                                placeholder="Añade detalles sobre el viaje, punto de encuentro, etc."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" name="crear_anuncio" class="flex-1 bg-primary hover:bg-hover text-text font-medium py-2.5 px-4 rounded-lg transition-colors">
                            Publicar anuncio
                        </button>
                        <a href="../dashboard.php" class="px-4 py-2.5 bg-background text-text font-medium rounded-lg border border-gray-200 hover:border-primary/20 transition-colors text-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Establecer la fecha mínima para el input de fecha
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('fecha').setAttribute('min', today);

                // Validar que la hora de regreso sea posterior a la hora de salida
                const horaSalida = document.getElementById('hora');
                const horaRegreso = document.getElementById('horaRegreso');

                function validarHoras() {
                    if (horaRegreso.value && horaSalida.value) {
                        if (horaRegreso.value <= horaSalida.value) {
                            horaRegreso.setCustomValidity('La hora de regreso debe ser posterior a la hora de salida');
                        } else {
                            horaRegreso.setCustomValidity('');
                        }
                    }
                }

                horaSalida.addEventListener('change', validarHoras);
                horaRegreso.addEventListener('change', validarHoras);

                // Manejar la visibilidad del campo de plazas según el tipo de anuncio
                const tipoInputs = document.querySelectorAll('input[name="tipo"]');
                const plazasLabel = document.querySelector('.plazas-opcional');
                const plazasInput = document.getElementById('plazas');

                tipoInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        if (this.value === 'ofrezco') {
                            plazasLabel.style.display = 'none';
                            plazasInput.required = true;
                            plazasInput.setAttribute('placeholder', 'Número de plazas disponibles *');
                        } else {
                            plazasLabel.style.display = 'inline';
                            plazasInput.required = false;
                            plazasInput.removeAttribute('placeholder');
                        }
                    });
                });
            });
        </script>
    </body>
    </html>
