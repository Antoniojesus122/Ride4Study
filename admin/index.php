session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: /login.php');
    exit;
}

$controller = new AdminDashboardController();
$controller->index();
