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

        // Contar estudiantes para cada institucion
        foreach ($instituciones as &$inst) {
            $inst['num_estudiantes'] = $this->institution->countStudents($inst['nombre']);
        }
        unset($inst);

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

        // Verificar que no exista otra institucion con el mismo correo
        $existing = $this->institution->getByEmail($data['correo']);
        if ($existing) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'correo_duplicado');
        }

        // Generar contraseña segura aleatoria (12 caracteres)
        $password = $this->generatePassword(12);
        $data['contrasena'] = password_hash($password, PASSWORD_DEFAULT);

        $newId = $this->institution->create($data);

        if (!$newId) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'error_crear');
        }

        // Enviar email con credenciales a la institucion
        $this->sendCredentialsEmail($data['nombre'], $data['correo'], $password);

        $this->adminLog->log((int)$_SESSION['user_id'], 'crear', 'institucion', $newId, $data['nombre']);
        redirectWithFlash(url('/admin/instituciones'), 'success', 'created');
    }

    // Regenerar contraseña y enviar por email
    public function resetPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'id_invalido');
        }

        $inst = $this->institution->getById($id);
        if (!$inst) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'no_encontrada');
        }

        // Generar nueva contraseña
        $password = $this->generatePassword(12);
        $this->institution->update($id, [
            'contrasena' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // Enviar email con nuevas credenciales
        $this->sendCredentialsEmail($inst['nombre'], $inst['correo'], $password, true);

        $this->adminLog->log((int)$_SESSION['user_id'], 'reset_password', 'institucion', $id, $inst['nombre']);
        redirectWithFlash(url('/admin/instituciones'), 'success', 'password_reset');
    }

    // Activar/desactivar cuenta de institucion
    public function toggleActive(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/instituciones'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'id_invalido');
        }

        $inst = $this->institution->getById($id);
        if (!$inst) {
            redirectWithFlash(url('/admin/instituciones'), 'error', 'no_encontrada');
        }

        $nuevoEstado = ((int)($inst['activo'] ?? 1)) === 1 ? 0 : 1;
        $this->institution->update($id, ['activo' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'activar' : 'desactivar';
        $this->adminLog->log((int)$_SESSION['user_id'], $accion, 'institucion', $id, $inst['nombre']);
        redirectWithFlash(url('/admin/instituciones'), 'success', $nuevoEstado ? 'activated' : 'deactivated');
    }

    // Generar contraseña segura aleatoria
    private function generatePassword(int $length = 12): string {
        $upper  = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $lower  = 'abcdefghijkmnpqrstuvwxyz';
        $digits = '23456789';
        $special = '!@#$%&*';

        // Asegurar al menos uno de cada tipo
        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $upper . $lower . $digits . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mezclar caracteres
        return str_shuffle($password);
    }

    // Enviar email con credenciales de acceso
    private function sendCredentialsEmail(string $nombre, string $correo, string $password, bool $isReset = false): void {
        try {
            require_once __DIR__ . '/../../../services/MailService.php';
            $mail = new MailService();

            $titulo = $isReset
                ? 'Nueva contraseña de acceso'
                : '¡Bienvenido a Ride4Study!';

            $intro = $isReset
                ? '<p>Se ha generado una nueva contraseña para tu cuenta institucional en Ride4Study.</p>'
                : '<p>Tu cuenta institucional en Ride4Study ha sido creada correctamente. A continuacion encontraras tus credenciales de acceso.</p>';

            $contenido = "
                {$intro}

                <div style=\"background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            padding: 24px;
                            border-radius: 12px;
                            margin: 24px 0;
                            color: white;\">
                    <p style=\"margin: 0 0 12px 0; font-size: 14px; opacity: 0.9;\">CREDENCIALES DE ACCESO</p>
                    <p style=\"margin: 0 0 8px 0; font-size: 16px;\"><strong>Email:</strong> " . htmlspecialchars($correo) . "</p>
                    <p style=\"margin: 0; font-size: 16px;\"><strong>Contraseña:</strong> " . htmlspecialchars($password) . "</p>
                </div>

                <p style=\"color: #f87171; font-weight: bold;\">⚠ Por seguridad, te recomendamos cambiar la contraseña una vez accedas al panel.</p>

                <p style=\"color: #94a3b8; font-size: 14px; margin-top: 20px;\">
                    Al iniciar sesion se te enviara un codigo de verificacion a este correo electronico (2FA) para mayor seguridad.
                </p>
            ";

            $html = $mail->generarPlantilla(
                htmlspecialchars($nombre),
                $titulo,
                $contenido,
                null,
                fullUrl('/institution-login'),
                'Acceder al panel'
            );

            $mail->send($correo, $nombre, $titulo . ' - Ride4Study', $html);
        } catch (\Exception $e) {
            error_log('Error enviando credenciales a institucion: ' . $e->getMessage());
        }
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
