<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied"); // Tambahkan keamanan extra

if (isset($_GET['id'])) {
    if (DivisiController::delete($_GET['id'])) {
        $_SESSION['success'] = "Divisi berhasil dihapus";
    } else {
        // Pesan error jika gagal karena masih ada posisi
        $_SESSION['error'] = "Gagal menghapus! Divisi ini masih memiliki data Posisi/Jabatan. Hapus data posisi terlebih dahulu.";
    }
}

header("Location: index.php");
exit;
