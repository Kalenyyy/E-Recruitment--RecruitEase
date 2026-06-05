<?php

require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_posisi = trim($_POST['nama_posisi']);
    $divisi_id   = $_POST['divisi_id'];

    if (PosisiController::create($nama_posisi, $divisi_id)) {

        $_SESSION['success'] = "Posisi berhasil ditambahkan";

    } else {

        $_SESSION['error'] = "Gagal menambahkan posisi";

    }

}

header("Location: index.php");
exit;