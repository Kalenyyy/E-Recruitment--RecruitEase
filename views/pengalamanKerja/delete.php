<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID tidak ditemukan');
}

$data = PengalamanKerja::findById($conn, $id);

if (!$data) {
    die('Data tidak ditemukan');
}

$deleted = PengalamanKerja::delete($conn, $id);

if ($deleted) {

    $_SESSION['success'] =
        "Pengalaman kerja berhasil dihapus";

    header(
        "Location: "
            . BASE_URL
            . "views/candidate/profile.php?id="
            . $data['candidate_id']
            . "#pengalaman-kerja"
    );
    exit;
}

die("Gagal menghapus data");
