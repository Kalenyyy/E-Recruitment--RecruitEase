<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

// Handle upload foto
$fotoName = null;

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fotoName = time() . "_" . basename($_FILES['foto']['name']);
    $uploadPath = __DIR__ . "/../../public/uploads/candidate/" . $fotoName;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto.']);
        exit;
    }
}

$data = [
    'nama_lengkap'  => $_POST['nama_lengkap'],
    'email'         => $_POST['email'],
    'no_hp'         => $_POST['no_hp'],
    'tanggal_lahir' => $_POST['tanggal_lahir'],
    'jenis_kelamin' => $_POST['jenis_kelamin'],
    'alamat'        => $_POST['alamat'],
    'foto'          => $fotoName // null = COALESCE pakai foto lama
];

$berhasil = CandidateController::updateProfile($id, $data);

if ($berhasil) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan profil.']);
}
exit;
