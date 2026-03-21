<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Institution.php';

class AdminInstitucionController {
    private PDO $db;
    private Institution $institution;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->institution = new Institution($this->db);
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
            header('Location: ' . url('/admin/instituciones') . '?error=campos_obligatorios');
            exit;
        }

        $this->institution->create($data);
        header('Location: ' . url('/admin/instituciones') . '?success=created');
        exit;
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
        header('Location: ' . url('/admin/instituciones') . '?success=updated');
        exit;
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->institution->delete($id);
        }

        header('Location: ' . url('/admin/instituciones') . '?success=deleted');
        exit;
    }
}
