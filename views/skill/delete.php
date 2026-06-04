<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "ID skill tidak ditemukan.";
    header("Location: index.php");
    exit;
}

if (SkillController::deleteSkill($conn, $id)) {

    $_SESSION['success'] = "Skill berhasil dihapus!";

} else {

    $_SESSION['error'] = "Gagal menghapus skill.";

}

header("Location: index.php");
exit;