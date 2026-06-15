<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../models/CandidateSkill.php';

$q = trim($_GET['q'] ?? '');
$data = [];

if (empty($q)) {
    // Jika tidak ada keyword, tampilkan rekomendasi skill
    $data = CandidateSkill::getPopularSkills($conn);
} else {
    // Jika ada keyword, cari yang sesuai
    $data = CandidateSkill::searchSkills($conn, $q);
}

header('Content-Type: application/json');
echo json_encode($data);
