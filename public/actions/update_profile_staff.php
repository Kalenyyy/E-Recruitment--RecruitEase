<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID tidak valid.'
    ]);
    exit;
}

$fotoName = null;

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    $fotoName = time() . "_" . basename($_FILES['foto']['name']);

    $uploadPath = __DIR__ . "/../../public/uploads/staff/" . $fotoName;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal upload foto.'
        ]);
        exit;
    }
}

$data = [
    'nama_staff' => $_POST['nama_staff'],
    'username'   => $_POST['username'],
    'email'      => $_POST['email'],
    'no_telp'    => $_POST['no_telp'],
    'alamat'     => $_POST['alamat'],
    'foto'       => $fotoName
];

$success = StaffController::updateProfile($conn, $id, $data);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Profil berhasil disimpan.' : 'Gagal menyimpan profil.'
]);
exit;