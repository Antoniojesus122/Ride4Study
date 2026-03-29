<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Institution.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminInstitucionController {
    private PDO $db;
    private Institution $institution;
    private AdminLog $adminLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->connect();
        $this->institution = new Institution($this->db);
        $this->adminLog = new AdminLog($this->db);
    }

    public function listAll(): void {
        $instituciones = $this->institution->getAll();
        require_once __DIR__ . '/../../../views/admin/instituciones.view.php';
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'logo' => trim($_POST['logo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if (!$data['nombre'] || !$data['correo']) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'campos_obligatorios');
        }

        $this->institution->create($data);
        $this->adminLog->log((int)$_SESSION['user_id'], 'crear', 'institucion', null, $data['nombre']);
        redirectWithFlash(url('/admin/instituciones'), 'success', 'created');
    }

    public function edit(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $data = [];
        foreach (['nombre', 'correo', 'telefono', 'direccion', 'logo', 'descripcion'] as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
            }
        }

        $this->institution->update($id, $data);
        $this->adminLog->log((int)$_SESSION['user_id'], 'editar', 'institucion', $id, implode(', ', array_keys($data)));
        redirectWithFlash(url('/admin/instituciones'), 'success', 'updated');
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->institution->delete($id);
            $this->adminLog->log((int)$_SESSION['user_id'], 'eliminar', 'institucion', $id, '');
        }

        redirectWithFlash(url('/admin/instituciones'), 'success', 'deleted');
    }

    public function exportCsv(): void {
        $instituciones = $this->institution->getAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="instituciones_ride4study_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Nombre', 'Correo', 'Telefono', 'Direccion', 'Descripcion', 'Fecha'], ';');

        foreach ($instituciones as $inst) {
            fputcsv($output, [
                $inst['idInstitucion'],
                $inst['nombre'],
                $inst['correo'],
                $inst['telefono'] ?? '',
                $inst['direccion'] ?? '',
                $inst['descripcion'] ?? '',
                $inst['creado_en'] ?? '',
            ], ';');
        }
        fclose($output);
        exit;
    }
}
