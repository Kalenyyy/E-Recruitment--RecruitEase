<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $nama_skill =
        trim($_POST['nama_skill']);

    if (
        SkillController::createSkill(
            $conn,
            $nama_skill
        )
    )
    {
        $_SESSION['success'] =
            "Skill berhasil ditambahkan";
    }
    else
    {
        $_SESSION['error'] =
            "Gagal menambahkan skill";
    }
}

header("Location: index.php");
exit;