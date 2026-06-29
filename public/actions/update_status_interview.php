<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi      = $_GET['id_transaksi']       ?? null;
    $status_baru       = $_POST['status_lamaran']    ?? '';
    $tanggal_interview = $_POST['tanggal_interview'] ?? null;
    $catatan           = $_POST['catatan']            ?? null;
    $alasan            = $_POST['alasan_tolak']       ?? null; // ← diperbaiki dari 'alasan'

    $appDetails = PelamarPekerjaanController::getApplication($conn, $id_transaksi);

    $allowedStatus = ['ADMINISTRASI', 'INTERVIEW', 'OFFERING', 'DITERIMA', 'DITOLAK'];

    if (in_array($status_baru, $allowedStatus)) {
        $sukses = PelamarPekerjaanController::ubahStatus(
            $conn,
            $id_transaksi,
            $status_baru,
            $tanggal_interview,
            $catatan,
            $alasan
        );
        if ($sukses) {
            header("Location: " . BASE_URL . "views/pelamarPekerjaan/detail.php?job_id=" . $appDetails['id_lowongan'] . "&msg=success");
            exit;
        } else {
            die("Gagal memperbarui status.");
        }
    } else {
        die("Status tidak valid.");
    }
}