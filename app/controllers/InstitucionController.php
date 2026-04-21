<?php

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../models/Institution.php';
    require_once __DIR__ . '/../models/MensajeInstitucion.php';

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

            // Nuevos estudiantes este mes + variacion vs mes anterior
            $stats['newStudentsMonth'] = $this->getNewStudentsCount($instName, 'current');
            $prev = $this->getNewStudentsCount($instName, 'prev');
            $stats['newStudentsPrev']  = $prev;
            $stats['newStudentsDelta'] = $stats['newStudentsMonth'] - $prev;

            // Mensajes sin leer del admin
            $mensajes = new MensajeInstitucion($this->db);
            $stats['unreadMessages'] = $mensajes->totalNoLeidosInstitucion($instId);

            // Periodo para la grafica de viajes
            $periodo = resolvePeriod($_GET);
            $chartData = $this->getTripsChartData($instName, $periodo);

            // Ultimos estudiantes registrados
            $recentStudents = $this->getRecentStudents($instName, 5);

            // Rutas mas frecuentes
            $topRoutes = $this->getTopRoutes($instName, 5);

            require_once __DIR__ . '/../../views/institucion/dashboard.view.php';
        }

        // Nuevos estudiantes registrados en el mes indicado
        private function getNewStudentsCount(string $instName, string $mes): int {
            if ($mes === 'prev') {
                $where = "DATE_FORMAT(creado_en, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
            } else {
                $where = "DATE_FORMAT(creado_en, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            }
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE institucion = :n AND {$where}");
            $stmt->execute([':n' => $instName]);
            return (int)$stmt->fetchColumn();
        }

        // Datos grafica de viajes segun periodo 
        private function getTripsChartData(string $instName, array $periodo): array {
            // Si no hay periodo o es rango corto, usamos dias; si es largo, meses
            $from = $periodo['from'] ?: date('Y-m-d', strtotime('-6 months'));
            $to   = $periodo['to']   ?: date('Y-m-d');

            $daysDiff = (strtotime($to) - strtotime($from)) / 86400;
            $groupByMonth = $daysDiff > 90;

            if ($groupByMonth) {
                $stmt = $this->db->prepare("
                    SELECT DATE_FORMAT(a.fechaSalida, '%Y-%m') as bucket, COUNT(*) as total
                    FROM anuncios a
                    JOIN usuarios u ON a.idUsuario = u.idUsuario
                    WHERE u.institucion = :n
                      AND a.fechaSalida BETWEEN :f AND :t
                    GROUP BY bucket ORDER BY bucket ASC
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT DATE(a.fechaSalida) as bucket, COUNT(*) as total
                    FROM anuncios a
                    JOIN usuarios u ON a.idUsuario = u.idUsuario
                    WHERE u.institucion = :n
                      AND a.fechaSalida BETWEEN :f AND :t
                    GROUP BY bucket ORDER BY bucket ASC
                ");
            }
            $stmt->execute([':n' => $instName, ':f' => $from . ' 00:00:00', ':t' => $to . ' 23:59:59']);
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $labels = []; $values = [];
            $months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            if ($groupByMonth) {
                $cursor = strtotime(date('Y-m-01', strtotime($from)));
                $end    = strtotime(date('Y-m-01', strtotime($to)));
                while ($cursor <= $end) {
                    $key = date('Y-m', $cursor);
                    $labels[] = $months[(int)date('n', $cursor) - 1];
                    $values[] = (int)($rows[$key] ?? 0);
                    $cursor = strtotime('+1 month', $cursor);
                }
            } else {
                $cursor = strtotime($from);
                $end    = strtotime($to);
                while ($cursor <= $end) {
                    $key = date('Y-m-d', $cursor);
                    $labels[] = date('d/m', $cursor);
                    $values[] = (int)($rows[$key] ?? 0);
                    $cursor = strtotime('+1 day', $cursor);
                }
            }
            return ['labels' => $labels, 'values' => $values];
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

        // Construir WHERE de filtros para estudiantes
        private function buildStudentFilters(string $instName, array $get, array &$params): string {
            $where = " WHERE u.institucion = :nombre ";
            $params[':nombre'] = $instName;

            $verif = $get['verificado'] ?? '';
            if ($verif === 'verificado' || $verif === 'no_verificado') {
                $where .= " AND u.estado_verificacion = :verif ";
                $params[':verif'] = $verif;
            } elseif ($verif === 'pendiente') {
                $where .= " AND u.estado_verificacion = 'pendiente' ";
            }

            $anuncios = $get['anuncios'] ?? '';
            if ($anuncios === 'con') {
                $where .= " AND EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario) ";
            } elseif ($anuncios === 'sin') {
                $where .= " AND NOT EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario) ";
            }

            $search = trim($get['search'] ?? '');
            if ($search !== '') {
                $where .= " AND (u.nombre LIKE :s OR u.correo LIKE :s2) ";
                $params[':s']  = '%' . $search . '%';
                $params[':s2'] = '%' . $search . '%';
            }

            $periodo = resolvePeriod($get);
            if ($periodo['from'] !== '') {
                $where .= " AND u.creado_en >= :pfrom ";
                $params[':pfrom'] = $periodo['from'] . ' 00:00:00';
            }
            if ($periodo['to'] !== '') {
                $where .= " AND u.creado_en <= :pto ";
                $params[':pto'] = $periodo['to'] . ' 23:59:59';
            }

            return $where;
        }

        // Lista de estudiantes con filtros server-side
        public function students(): void {
            $instName = $_SESSION['institution_name'] ?? '';

            $params = [];
            $where = $this->buildStudentFilters($instName, $_GET, $params);

            $sql = "SELECT u.idUsuario, u.nombre, u.correo, u.foto_perfil, u.creado_en,
                        u.estado_verificacion,
                        (SELECT COUNT(*) FROM anuncios a WHERE a.idUsuario = u.idUsuario) as num_viajes,
                        (SELECT AVG(v.puntuacion) FROM valoraciones v WHERE v.idValorado = u.idUsuario) as valoracion_media
                    FROM usuarios u
                    {$where}
                    ORDER BY u.creado_en DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../../views/institucion/students.view.php';
        }

        // Exportar a CSV con los mismos filtros
        public function exportStudents(): void {
            $instName = $_SESSION['institution_name'] ?? '';

            $params = [];
            $where = $this->buildStudentFilters($instName, $_GET, $params);

            $sql = "SELECT u.idUsuario, u.nombre, u.correo, u.telefono, u.creado_en,
                        u.estado_verificacion,
                        (SELECT COUNT(*) FROM anuncios a WHERE a.idUsuario = u.idUsuario) as num_viajes,
                        (SELECT AVG(v.puntuacion) FROM valoraciones v WHERE v.idValorado = u.idUsuario) as valoracion_media
                    FROM usuarios u
                    {$where}
                    ORDER BY u.creado_en DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="estudiantes_' . date('Y-m-d') . '.csv"');

            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['ID', 'Nombre', 'Correo', 'Telefono', 'Verificacion', 'Anuncios', 'Valoracion media', 'Fecha registro'], ';');

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, [
                    $row['idUsuario'],
                    $row['nombre'],
                    $row['correo'],
                    $row['telefono'] ?? '',
                    $row['estado_verificacion'] ?? '',
                    (int)$row['num_viajes'],
                    $row['valoracion_media'] ? number_format((float)$row['valoracion_media'], 1) : '',
                    $row['creado_en'],
                ], ';');
            }
            fclose($out);
            exit;
        }
    }
