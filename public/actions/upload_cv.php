<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isCandidate() or die(json_encode(['success' => false, 'message' => 'Access denied']));

header('Content-Type: application/json');

$candidateId = (int) ($_POST['candidate_id'] ?? 0);
if (!$candidateId) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

if (empty($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File tidak diterima.']);
    exit;
}

$file    = $_FILES['cv_file'];
$allowed = ['pdf', 'doc', 'docx'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak diizinkan.']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 5 MB.']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/cv/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Ambil CV lama
$stmt = mysqli_prepare($conn, "SELECT cv_file FROM candidates WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $candidateId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row    = mysqli_fetch_assoc($result);
$old    = $row['cv_file'] ?? null;
mysqli_stmt_close($stmt);

// Generate nama file unik
$newFilename = 'cv_' . $candidateId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $newFilename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file.']);
    exit;
}

// Update DB
$stmt = mysqli_prepare($conn, "UPDATE candidates SET cv_file = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $newFilename, $candidateId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Hapus file lama
if ($old && file_exists($uploadDir . $old)) {
    unlink($uploadDir . $old);
}

echo json_encode(['success' => true, 'filename' => $newFilename]);
