<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id           = $_POST['id'];
    $nama_posisi  = trim($_POST['nama_posisi']);
    $id_divisi    = $_POST['divisi_id'];

    $result = PosisiController::update($id, $nama_posisi, $id_divisi);

    if ($result === "duplicate") {
        $_SESSION['error'] = "Gagal! Nama posisi sudah terdaftar.";
    } elseif ($result) {
        $_SESSION['success'] = "Posisi berhasil diubah";
    } else {
        $_SESSION['error'] = "Gagal mengubah posisi";
    }
}

header("Location: index.php");
exit;
