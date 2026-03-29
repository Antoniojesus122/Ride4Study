<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Config.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminConfigController
{
    private PDO $db;
    private Config $config;
    private AdminLog $adminLog;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->config = new Config($this->db);
        $this->adminLog = new AdminLog($this->db);

        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public function index(): void
    {
        $grouped = $this->config->getGrouped();
        $flash = getFlash();
        $successMsg = ($flash && $flash['type'] === 'success') ? $flash['message'] : null;
        $errorMsg = ($flash && $flash['type'] === 'error') ? $flash['message'] : null;

        require_once __DIR__ . '/../../../views/admin/config.view.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/config'));
            exit;
        }

        $configs = $_POST['config'] ?? [];

        if (empty($configs) || !is_array($configs)) {
            redirectWithFlash(url('/admin/config'), 'error', 'No se recibieron datos de configuracion.');
        }

        // Obtener valores actuales para detectar cambios
        $allConfigs = $this->config->getAll();
        $currentValues = [];
        foreach ($allConfigs as $row) {
            $currentValues[$row['clave']] = $row['valor'];
        }

        $changes = [];

        foreach ($configs as $clave => $valor) {
            $valor = trim($valor);

            // Solo actualizar si el valor cambio
            if (isset($currentValues[$clave]) && $currentValues[$clave] !== $valor) {
                $this->config->set($clave, $valor);
                $changes[] = $clave . ': ' . $currentValues[$clave] . ' -> ' . $valor;
            }
        }

        // Registrar cambios en el log
        if (!empty($changes)) {
            $adminId = (int)$_SESSION['user_id'];
            $detalles = implode('; ', $changes);
            $this->adminLog->log($adminId, 'actualizar', 'configuracion', null, $detalles);
        }

        redirectWithFlash(url('/admin/config'), 'success', 'Configuracion actualizada correctamente.');
    }
}
