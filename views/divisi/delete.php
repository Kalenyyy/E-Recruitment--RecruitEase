<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

if (isset($_GET['id']))
{
    if (DivisiController::delete($_GET['id']))
    {
        $_SESSION['success'] =
            "Divisi berhasil dihapus";
    }
    else
    {
        $_SESSION['error'] =
            "Gagal menghapus divisi";
    }
}

header("Location: index.php");
exit;