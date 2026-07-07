<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

// 1. Ambil dan bersihkan input awal
$data = [
    'nama_staff' => trim($_POST['nama_staff'] ?? ''),
    'username'   => trim($_POST['username'] ?? ''),
    'email'      => trim($_POST['email'] ?? ''),
    'no_telp'    => trim($_POST['no_telp'] ?? ''),
    'alamat'     => trim($_POST['alamat'] ?? ''),
    'foto'       => null // Default null jika tidak ada upload
];

// 2. Validasi Dasar: Tidak boleh ada yang kosong (Basic Integrity)
foreach ($data as $key => $value) {
    if ($key !== 'foto' && empty($value)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']);
        exit;
    }
}

// 3. Proses Upload Foto (Hanya jika ada file baru)
if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fotoName = time() . "_" . basename($_FILES['foto']['name']);
    $uploadPath = __DIR__ . "/../../public/uploads/staff/" . $fotoName;

    // Pastikan folder ada
    if (!is_dir(dirname($uploadPath))) mkdir(dirname($uploadPath), 0777, true);

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        $data['foto'] = $fotoName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto ke server.']);
        exit;
    }
}

// 4. Panggil Controller
// Controller sekarang menangani validasi Regex (angka/spasi) dan Keunikan (Username/Email)
$result = StaffController::updateProfile($conn, $id, $data);

// 5. Kirim respon balik ke AJAX
echo json_encode([
    'success' => $result['status'],
    'message' => $result['message']
]);
exit;
