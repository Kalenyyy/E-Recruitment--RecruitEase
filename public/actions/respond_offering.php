<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi = $_POST['id_transaksi']   ?? null;
    $respon       = $_POST['respon']          ?? null;
    $alasan_tolak = $_POST['tolak_candidate'] ?? null; // ← tambahan

    if (!$id_transaksi || !$respon) {
        $_SESSION['error'] = "Data tidak valid.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Teruskan alasan ke controller
    $result = LamaranController::prosesResponOffering($conn, $id_transaksi, $respon, $alasan_tolak);

    if ($result) {
        $pesan = ($respon === 'DITERIMA')
            ? "Selamat! Anda menerima penawaran ini."
            : "Anda telah menolak penawaran ini.";
        $_SESSION['success'] = $pesan;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat memproses data.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header("Location: " . BASE_URL . "views/lamaran/index.php");
    exit;
}