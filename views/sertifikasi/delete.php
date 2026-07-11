<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID Sertifikasi tidak ditemukan');
}

$data = SertifikasiController::findById(
    $conn,
    $id
);

if (!$data) {
    die('Data sertifikasi tidak ditemukan');
}

$deleted = SertifikasiController::delete(
    $conn,
    $id
);

if ($deleted) {

    $_SESSION['success'] =
        "Sertifikasi berhasil dihapus";

    header(
        "Location: "
        . BASE_URL
        . "views/candidate/profile.php?id="
        . $data['candidate_id']
        . "&status=success_delete#sertifikasi"
    );
    exit;
}

die("Gagal menghapus data");