<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $id = $_POST['id_divisi'];
    $nama = trim($_POST['nama_divisi']);

    if (DivisiController::update($id, $nama))
    {
        $_SESSION['success'] =
            "Divisi berhasil diperbarui";
    }
    else
    {
        $_SESSION['error'] =
            "Gagal memperbarui divisi";
    }
}

header("Location: index.php");
exit;