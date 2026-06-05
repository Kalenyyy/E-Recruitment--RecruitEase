<?php

require_once __DIR__ . '/../../init.php';

$id = $_GET['id'] ?? 0;

if (PosisiController::delete($id)) {

    $_SESSION['success'] = "Posisi berhasil dihapus";

} else {

    $_SESSION['error'] = "Gagal menghapus posisi";

}

header("Location: index.php");
exit;