<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $id = $_POST['id_skill'];
    $nama_skill = trim($_POST['nama_skill']);

    if (SkillController::updateSkill($conn, $id, $nama_skill))
    {
        $_SESSION['success'] =
            "Skill berhasil diperbarui";
    }
    else
    {
        $_SESSION['error'] =
            "Gagal memperbarui skill";
    }
}

header("Location: index.php");
exit;