<?php

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../models/Institution.php';

    // Controlador principal del panel de instituciones
    class InstitucionController {
        private PDO $db;
        private Institution $institution;

        public function __construct() {
            if (session_status() === PHP_SESSION_NONE) session_start();

            if (!isset($_SESSION['institution_id'])) {
                header('Location: ' . url('/institution-login'));
                exit;
            }

            $database = new Database();
            $this->db = $database->connect();
            $this->institution = new Institution($this->db);
        }

        // Dashboard principal con estadisticas y graficas
        public function dashboard(): void {
            $instId = (int)$_SESSION['institution_id'];
            $instName = $_SESSION['institution_name'] ?? '';

            // Estadisticas principales
            $stats = $this->getStats($instName);

            // Datos para graficas (viajes por mes, ultimos 6 meses)
            $chartData = $this->getMonthlyTripsData($instName);

            // Ultimos estudiantes registrados
            $recentStudents = $this->getRecentStudents($instName, 5);

            // Rutas mas frecuentes
            $topRoutes = $this->getTopRoutes($instName, 5);

            require_once __DIR__ . '/../../views/institucion/dashboard.view.php';
        }

        // Obtener estadisticas generales
        private function getStats(string $instName): array {
            // Total de estudiantes
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE institucion = :nombre");
            $stmt->execute([':nombre' => $instName]);
            $totalStudents = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Viajes totales de estudiantes de esta institucion
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.idAnuncio) as total
                FROM anuncios a
                JOIN usuarios u ON a.idUsuario = u.idUsuario
                WHERE u.institucion = :nombre
            ");
            $stmt->execute([':nombre' => $instName]);
            $totalTrips = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Viajes completados
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT v.idViaje) as total
                FROM viajes v
                JOIN usuarios u ON (v.idPasajero = u.idUsuario OR v.idConductor = u.idUsuario)
                WHERE u.institucion = :nombre AND v.estado = 'completado'
            ");
            $stmt->execute([':nombre' => $instName]);
            $completedTrips = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Valoracion media
            $stmt = $this->db->prepare("
                SELECT AVG(val.puntuacion) as media
                FROM valoraciones val
                JOIN usuarios u ON val.idValorado = u.idUsuario
                WHERE u.institucion = :nombre
            ");
            $stmt->execute([':nombre' => $instName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $avgRating = $row['media'] ? round((float)$row['media'], 1) : 0;

            // CO2 ahorrado (suma de todos los estudiantes)
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(co2_ahorrado), 0) as total
                FROM usuarios WHERE institucion = :nombre
            ");
            $stmt->execute([':nombre' => $instName]);
            $co2Saved = round((float)$stmt->fetch(PDO::FETCH_ASSOC)['total'], 1);

            return [
                'totalStudents' => $totalStudents,
                'totalTrips' => $totalTrips,
                'completedTrips' => $completedTrips,
                'avgRating' => $avgRating,
                'co2Saved' => $co2Saved,
            ];
        }

        // Datos de viajes por mes (ultimos 6 meses)
        private function getMonthlyTripsData(string $instName): array {
            $stmt = $this->db->prepare("
                SELECT DATE_FORMAT(a.fechaSalida, '%Y-%m') as mes, COUNT(*) as total
                FROM anuncios a
                JOIN usuarios u ON a.idUsuario = u.idUsuario
                WHERE u.institucion = :nombre
                AND a.fechaSalida >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY mes
                ORDER BY mes ASC
            ");
            $stmt->execute([':nombre' => $instName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $values = [];
            $months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            for ($i = 5; $i >= 0; $i--) {
                $date = date('Y-m', strtotime("-{$i} months"));
                $monthNum = (int)date('n', strtotime("-{$i} months"));
                $labels[] = $months[$monthNum - 1];
                $found = false;
                foreach ($rows as $row) {
                    if ($row['mes'] === $date) {
                        $values[] = (int)$row['total'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) $values[] = 0;
            }

            return ['labels' => $labels, 'values' => $values];
        }

        // Ultimos estudiantes registrados
        private function getRecentStudents(string $instName, int $limit): array {
            $stmt = $this->db->prepare("
                SELECT idUsuario, nombre, correo, foto_perfil, creado_en
                FROM usuarios
                WHERE institucion = :nombre
                ORDER BY creado_en DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':nombre', $instName);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Rutas mas frecuentes
        private function getTopRoutes(string $instName, int $limit): array {
            $stmt = $this->db->prepare("
                SELECT
                    o.nombreLocalidad as origen,
                    d.nombreLocalidad as destino,
                    COUNT(*) as total
                FROM anuncios a
                JOIN usuarios u ON a.idUsuario = u.idUsuario
                JOIN localidades o ON a.origen = o.idLocalidad
                JOIN localidades d ON a.destino = d.idLocalidad
                WHERE u.institucion = :nombre
                GROUP BY a.origen, a.destino
                ORDER BY total DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':nombre', $instName);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Lista de estudiantes
        public function students(): void {
            $instName = $_SESSION['institution_name'] ?? '';

            $stmt = $this->db->prepare("
                SELECT u.idUsuario, u.nombre, u.correo, u.foto_perfil, u.creado_en,
                    u.estado_verificacion,
                    (SELECT COUNT(*) FROM anuncios a WHERE a.idUsuario = u.idUsuario) as num_viajes,
                    (SELECT AVG(v.puntuacion) FROM valoraciones v WHERE v.idValorado = u.idUsuario) as valoracion_media
                FROM usuarios u
                WHERE u.institucion = :nombre
                ORDER BY u.creado_en DESC
            ");
            $stmt->execute([':nombre' => $instName]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../../views/institucion/students.view.php';
        }
    }
