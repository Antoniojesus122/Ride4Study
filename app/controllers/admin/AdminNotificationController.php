<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminNotificationController {
    private PDO $db;
    private Notification $notification;
    private AdminLog $adminLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->notification = new Notification($this->db);
        $this->adminLog = new AdminLog($this->db);
    }

    public function index(): void {
        $flash = getFlash();
        $successMsg = ($flash && $flash['type'] === 'success') ? $flash['message'] : null;
        $errorMsg = ($flash && $flash['type'] === 'error') ? $flash['message'] : null;

        // Obtener historial de notificaciones masivas
        $stmt = $this->db->prepare(
            "SELECT nm.*, u.nombre AS admin_nombre
             FROM notificaciones_masivas nm
             LEFT JOIN usuarios u ON nm.idAdmin = u.idUsuario
             ORDER BY nm.creado_en DESC
             LIMIT 20"
        );
        $stmt->execute();
        $broadcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../../views/admin/notifications.view.php';
    }

    public function send(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/notifications'));
            exit;
        }

        $mensaje = trim($_POST['mensaje'] ?? '');
        $icono = trim($_POST['icono'] ?? 'fas fa-bell');
        $urlNotif = trim($_POST['url'] ?? '');
        $filtroTipo = $_POST['filtro_tipo'] ?? 'todos';
        $filtroValor = $_POST['filtro_valor'] ?? '';

        if (empty($mensaje)) {
            redirectWithFlash(url('/admin/notifications'), 'error', 'El mensaje no puede estar vacio');
        }

        // Obtener IDs de usuarios segun filtro
        $userIds = $this->getUserIdsByFilter($filtroTipo);

        if (empty($userIds)) {
            redirectWithFlash(url('/admin/notifications'), 'error', 'No se encontraron usuarios con el filtro seleccionado');
        }

        // Insertar notificaciones en batch (multi-row INSERT)
        $totalEnviados = $this->batchCreateNotifications($userIds, $mensaje, $icono, $urlNotif);

        // Registrar en notificaciones_masivas
        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $stmtMasiva = $this->db->prepare(
            "INSERT INTO notificaciones_masivas (idAdmin, mensaje, icono, url, filtro_tipo, filtro_valor, total_enviados)
             VALUES (:idAdmin, :mensaje, :icono, :url, :filtro_tipo, :filtro_valor, :total)"
        );
        $stmtMasiva->execute([
            ':idAdmin'      => $adminId,
            ':mensaje'      => $mensaje,
            ':icono'        => $icono,
            ':url'          => $urlNotif,
            ':filtro_tipo'  => $filtroTipo,
            ':filtro_valor' => $filtroValor,
            ':total'        => $totalEnviados,
        ]);

        // Log de administracion
        $this->adminLog->log(
            $adminId,
            'enviar_notificacion_masiva',
            'notificacion',
            null,
            "Filtro: {$filtroTipo}, Enviadas: {$totalEnviados}"
        );

        redirectWithFlash(url('/admin/notifications'), 'success', "Notificacion enviada a {$totalEnviados} usuarios");
    }

    public function preview(): void {
        $filtroTipo = $_GET['filtro_tipo'] ?? 'todos';
        $userIds = $this->getUserIdsByFilter($filtroTipo);

        header('Content-Type: application/json');
        echo json_encode(['count' => count($userIds)]);
        exit;
    }

    private function getUserIdsByFilter(string $filtro): array {
        switch ($filtro) {
            case 'premium':
                $sql = "SELECT idUsuario FROM usuarios WHERE premium = 1 AND idRol != 1";
                break;
            case 'verificados':
                $sql = "SELECT idUsuario FROM usuarios WHERE estado_verificacion = 2 AND idRol != 1";
                break;
            case 'no_verificados':
                $sql = "SELECT idUsuario FROM usuarios WHERE estado_verificacion != 2 AND idRol != 1";
                break;
            case 'todos':
            default:
                $sql = "SELECT idUsuario FROM usuarios WHERE idRol != 1";
                break;
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function batchCreateNotifications(array $userIds, string $mensaje, string $icono, string $url): int {
        if (empty($userIds)) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        $i = 0;

        foreach ($userIds as $userId) {
            $placeholders[] = "(:uid{$i}, 'sistema', :msg{$i}, :ico{$i}, :url{$i})";
            $params[":uid{$i}"] = $userId;
            $params[":msg{$i}"] = $mensaje;
            $params[":ico{$i}"] = $icono;
            $params[":url{$i}"] = $url;
            $i++;
        }
        
        $chunks = array_chunk($placeholders, 500, true);
        $paramKeys = array_keys($params);
        $paramChunks = array_chunk($paramKeys, 500 * 4, true);
        $total = 0;

        $allPlaceholders = $placeholders;
        $batchSize = 500;
        $totalUsers = count($userIds);

        for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
            $batchIds = array_slice($userIds, $offset, $batchSize);
            $batchPlaceholders = [];
            $batchParams = [];
            $j = 0;

            foreach ($batchIds as $userId) {
                $batchPlaceholders[] = "(:uid{$j}, 'sistema', :msg{$j}, :ico{$j}, :url{$j})";
                $batchParams[":uid{$j}"] = $userId;
                $batchParams[":msg{$j}"] = $mensaje;
                $batchParams[":ico{$j}"] = $icono;
                $batchParams[":url{$j}"] = $url;
                $j++;
            }

            $sql = "INSERT INTO notificaciones (idUsuario, tipoNotificacion, mensaje, icono, url) VALUES "
                 . implode(', ', $batchPlaceholders);

            $stmt = $this->db->prepare($sql);
            $stmt->execute($batchParams);
            $total += $stmt->rowCount();
        }

        return $total;
    }
}
