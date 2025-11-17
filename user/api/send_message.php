    <?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }

    require_once __DIR__ . '/../../includes/db.php';
    $userId = $_SESSION['user_id'];
    $receptorId = $_POST['id_receptor'] ?? null;
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!$receptorId || empty($mensaje)) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO mensajes (idEmisor, idReceptor, mensaje) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $receptorId, $mensaje]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje']);
    }