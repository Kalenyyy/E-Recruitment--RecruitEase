<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_skill'];
    $nama_skill = trim($_POST['nama_skill']);

    if (empty($nama_skill)) {
        $_SESSION['error'] = "Nama skill tidak boleh kosong";
    } else {
        $result = SkillController::updateSkill($conn, $id, $nama_skill);

        if ($result === "duplicate") {
            $_SESSION['error'] = "Nama skill '$nama_skill' sudah terdaftar.";
        } elseif ($result) {
            $_SESSION['success'] = "Skill berhasil diperbarui";
        } else {
            $_SESSION['error'] = "Gagal memperbarui skill";
        }
    }
}

header("Location: index.php");
exit;
