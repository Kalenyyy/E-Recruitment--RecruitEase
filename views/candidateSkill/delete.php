<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$id = $_GET['id'] ?? null;
$candidateId = $_GET['candidate_id'] ?? null;

if (!$id || !$candidateId) {
    die('Data tidak valid');
}

CandidateSkill::delete(
    $conn,
    $id
);

$_SESSION['success'] =
    'Skill berhasil dihapus';

header(
    "Location: " .
        BASE_URL .
        "views/candidate/profile.php?id=" .
        $candidateId .
        "&status=success_delete#pengalaman-kerja"
);

exit;
