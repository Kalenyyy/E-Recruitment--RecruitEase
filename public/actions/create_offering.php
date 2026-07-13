<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

AuthController::requireLogin();
if (!AuthController::isHRD()) { die("Akses ditolak."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi  = $_POST['id_transaksi'] ?? null;
    $gaji_raw      = $_POST['gaji_offering'] ?? null;
    $gaji_offering = preg_replace('/[^0-9]/', '', $gaji_raw);
    $file_offering = $_FILES['file_offering'] ?? null;

    // 1. Validasi Input Dasar
    if (!$id_transaksi || !$gaji_offering) {
        $_SESSION['error'] = "Data tidak lengkap.";
        header("Location: " . $_SERVER['HTTP_REFERER']); exit;
    }

    // 2. VALIDASI RANGE GAJI (Panggil Controller)
    $validasi = PelamarPekerjaanController::validasiGaji($conn, $id_transaksi, $gaji_offering);
    if (!$validasi['status']) {
        $_SESSION['error'] = $validasi['message'];
        header("Location: " . $_SERVER['HTTP_REFERER']); exit;
    }

    $fileNameDB = null;

    // 3. Proses Upload PDF (Hanya PDF)
    if (!empty($file_offering['name']) && $file_offering['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . "/../uploads/offering/";
        if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

        $fileExtension = strtolower(pathinfo($file_offering['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'pdf') {
            $_SESSION['error'] = "Hanya file PDF yang diperbolehkan.";
            header("Location: " . $_SERVER['HTTP_REFERER']); exit;
        }

        $fileNameDB = "offering_" . $id_transaksi . "_" . time() . ".pdf";
        if (!move_uploaded_file($file_offering['tmp_name'], $targetDir . $fileNameDB)) {
            $_SESSION['error'] = "Gagal upload file.";
            header("Location: " . $_SERVER['HTTP_REFERER']); exit;
        }
    } else {
        $_SESSION['error'] = "File PDF wajib diunggah.";
        header("Location: " . $_SERVER['HTTP_REFERER']); exit;
    }

    // 4. Simpan ke Database
    $result = PelamarPekerjaanController::kirimOffering($conn, $id_transaksi, $gaji_offering, $fileNameDB);

    if ($result) {
        $_SESSION['success'] = "Berhasil mengirim Offering Letter.";
    } else {
        if (file_exists($targetDir . $fileNameDB)) { unlink($targetDir . $fileNameDB); }
        $_SESSION['error'] = "Gagal memperbarui database.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}