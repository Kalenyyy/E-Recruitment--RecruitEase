<?php
require_once __DIR__ . '/../../init.php';

$data = json_decode(file_get_contents("php://input"), true);

DisabilityController::update(
    $data['candidate_id'],
    $data['is_disabled'],
    $data['description'],
    $data['types']
);

echo json_encode(['success' => true]);