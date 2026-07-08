<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_posisi = trim($_POST['nama_posisi']);
    $divisi_id   = $_POST['divisi_id'];

    $result = PosisiController::create($nama_posisi, $divisi_id);

    if ($result === "duplicate") {
        $_SESSION['error'] = "Gagal! Nama posisi sudah terdaftar.";
    } elseif ($result) {
        $_SESSION['success'] = "Posisi berhasil ditambahkan";
    } else {
        $_SESSION['error'] = "Gagal menambahkan posisi";
    }
}

header("Location: index.php");
exit;
