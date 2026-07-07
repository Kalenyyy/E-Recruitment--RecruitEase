<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_divisi'];
    $nama = trim($_POST['nama_divisi']);
    $result = DivisiController::update($id, $nama);

    if ($result === "duplicate") {
        $_SESSION['error'] = "Nama Divisi sudah terdaftar.";
    } elseif ($result) {
        $_SESSION['success'] = "Divisi berhasil diperbarui";
    } else {
        $_SESSION['error'] = "Gagal memperbarui divisi";
    }
}

header("Location: index.php");
exit;