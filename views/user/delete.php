<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$id = $_GET['id'] ?? null;
if ($id && StaffController::destroy($conn, $id)) {
    $_SESSION['success'] = "Data HRD dan akun login berhasil dihapus selamanya!";
} else {
    $_SESSION['error'] = "Gagal menghapus data.";
}

header("Location: index.php");
exit;
