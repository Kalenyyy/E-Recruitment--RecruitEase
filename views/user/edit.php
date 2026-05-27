<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$id = $_GET['id'] ?? null;
if ($id && StaffController::toggleStatus($conn, $id)) {
    $_SESSION['success'] = "Status HRD berhasil diperbarui!";
}

header("Location: index.php");
exit;