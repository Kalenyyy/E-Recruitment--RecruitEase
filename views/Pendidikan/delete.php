<?php

require_once __DIR__ . '/../../init.php';

$id = $_GET['id'] ?? null;

$data = PendidikanController::getById(
    $conn,
    $id
);

if (!$data) {
    die("Data tidak ditemukan");
}

PendidikanController::delete(
    $conn,
    $id
);

header(
    "Location: ../candidate/profile.php?id=" .
    $data['candidate_id'] .
    "#pendidikan"
);
exit;