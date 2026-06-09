<?php
// Sesuaikan path ini dengan struktur project kamu
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../models/CandidateSkill.php';

$q = trim($_GET['q'] ?? '');
$data = [];

if (strlen($q) >= 2) {
    $data = CandidateSkill::searchSkills($conn, $q);
}

header('Content-Type: application/json');
echo json_encode($data);
