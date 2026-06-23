<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi = $_POST['id_transaksi'] ?? null;
    $respon = $_POST['respon'] ?? null;

    if (!$id_transaksi || !$respon) {
        $_SESSION['error'] = "Data tidak valid.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Panggil Controller untuk memproses
    $result = LamaranController::prosesResponOffering($conn, $id_transaksi, $respon);

    if ($result) {
        $pesan = ($respon === 'DITERIMA') ? "Selamat! Anda menerima penawaran ini." : "Anda telah menolak penawaran ini.";
        $_SESSION['success'] = $pesan;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat memproses data.";
    }

    // Redirect kembali ke halaman riwayat lamaran
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header("Location: " . BASE_URL . "views/lamaran/index.php");
    exit;
}