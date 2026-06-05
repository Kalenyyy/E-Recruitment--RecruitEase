<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_divisi = trim($_POST['nama_divisi']);

    if (DivisiController::create($nama_divisi)) {

        $_SESSION['success'] =
            "Data divisi berhasil ditambahkan";

    } else {

        $_SESSION['error'] =
            "Data divisi gagal ditambahkan";
    }

}

header("Location: index.php");
exit;