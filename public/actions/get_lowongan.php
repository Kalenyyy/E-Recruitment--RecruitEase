<?php
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json');

try {
    // Memanggil logic yang sama dengan halaman utama
    $lowonganData = LowonganPekerjaanController::jelajahiLowongan($conn);

    echo json_encode([
        'status' => 'success',
        'jobs' => $lowonganData['jobs'],
        'total' => $lowonganData['total'],
        'page' => $lowonganData['page'],
        'total_pages' => $lowonganData['total_pages']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
