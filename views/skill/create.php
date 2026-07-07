<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_skill = trim($_POST['nama_skill']);

    if (empty($nama_skill)) {
        $_SESSION['error'] = "Nama skill tidak boleh kosong";
    } else {
        $result = SkillController::createSkill($conn, $nama_skill);

        if ($result === "duplicate") {
            $_SESSION['error'] = "Skill '$nama_skill' sudah ada di dalam daftar.";
        } elseif ($result) {
            $_SESSION['success'] = "Skill berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan skill";
        }
    }
}

header("Location: index.php");
exit;
