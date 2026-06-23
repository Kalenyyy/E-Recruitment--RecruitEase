<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

// Cek Login & Role
AuthController::requireLogin();
if (!AuthController::isHRD()) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi  = $_POST['id_transaksi'] ?? null;
    $gaji_offering = $_POST['gaji_offering'] ?? null;
    $file_offering = $_FILES['file_offering'] ?? null;

    // 1. Validasi Input Dasar
    if (!$id_transaksi || !$gaji_offering) {
        $_SESSION['error'] = "Data tidak lengkap.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $fileNameDB = null;

    // 2. Proses Upload PDF Offering
    if (!empty($file_offering['name']) && $file_offering['error'] === UPLOAD_ERR_OK) {

        // Folder Tujuan: public/uploads/offering/
        $targetDir = __DIR__ . "/../uploads/offering/";

        // Buat folder jika belum ada
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($file_offering['name'], PATHINFO_EXTENSION));

        // Validasi harus PDF
        if ($fileExtension !== 'pdf') {
            $_SESSION['error'] = "Format file harus PDF.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Penamaan file: offering_idtransaksi_waktu.pdf
        $fileNameDB = "offering_" . $id_transaksi . "_" . time() . ".pdf";
        $uploadPath = $targetDir . $fileNameDB;

        if (!move_uploaded_file($file_offering['tmp_name'], $uploadPath)) {
            $_SESSION['error'] = "Gagal mengupload file PDF ke server.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } else {
        $_SESSION['error'] = "File Offering Letter wajib diunggah.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // 3. Simpan ke Database melalui Controller
    $result = PelamarPekerjaanController::kirimOffering(
        $conn,
        $id_transaksi,
        $gaji_offering,
        $fileNameDB
    );

    if ($result) {
        $_SESSION['success'] = "Berhasil mengirim Offering Letter.";
    } else {
        // Jika DB gagal, hapus file yang sudah terlanjur diupload
        if (file_exists($targetDir . $fileNameDB)) {
            unlink($targetDir . $fileNameDB);
        }
        $_SESSION['error'] = "Gagal memperbarui status di database.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header("Location: " . BASE_URL . "views/pelamarPekerjaan/index.php");
    exit;
}
