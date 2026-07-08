<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_divisi = trim($_POST['nama_divisi']);
    $result = DivisiController::create($nama_divisi);

    if ($result === "duplicate") {
        $_SESSION['error'] = "Nama Divisi sudah terdaftar.";
    } elseif ($result) {
        $_SESSION['success'] = "Data divisi berhasil ditambahkan";
    } else {
        $_SESSION['error'] = "Data divisi gagal ditambahkan";
    }
}

header("Location: index.php");
exit;