<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

header('Content-Type: application/json');

// 1. Ambil dan bersihkan input
$id             = $_POST['id'] ?? null;
$nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
$email          = trim($_POST['email'] ?? '');
$no_hp          = trim($_POST['no_hp'] ?? '');
$tanggal_lahir  = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
$alamat         = trim($_POST['alamat'] ?? '');

// 2. Validasi ID
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

// 3. Validasi Field Kosong & Format
if (empty($nama_lengkap)) {
    echo json_encode(['success' => false, 'message' => 'Nama lengkap wajib diisi.']);
    exit;
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email wajib diisi.']);
    exit;
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

if (empty($no_hp)) {
    echo json_encode(['success' => false, 'message' => 'Nomor HP wajib diisi.']);
    exit;
} elseif (!preg_match('/^[0-9]+$/', $no_hp)) {
    echo json_encode(['success' => false, 'message' => 'Nomor HP hanya boleh berisi angka.']);
    exit;
}

$today = date('Y-m-d');

if (empty($tanggal_lahir)) {
    echo json_encode(['success' => false, 'message' => 'Tanggal lahir wajib diisi.']);
    exit;
} elseif ($tanggal_lahir > $today) {
    // Jika tanggal yang dipilih lebih besar dari hari ini
    echo json_encode(['success' => false, 'message' => 'Tanggal lahir tidak valid (tidak boleh melebihi hari ini).']);
    exit;
}

if (empty($jenis_kelamin)) {
    echo json_encode(['success' => false, 'message' => 'Jenis kelamin wajib dipilih.']);
    exit;
}

if (empty($alamat)) {
    echo json_encode(['success' => false, 'message' => 'Alamat wajib diisi.']);
    exit;
}

// 4. Handle upload foto (hanya jika ada file baru)
$fotoName = null;
if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Validasi tipe file foto
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format foto harus JPG, PNG, atau WEBP.']);
        exit;
    }

    $fotoName = time() . "_" . basename($_FILES['foto']['name']);
    $uploadPath = __DIR__ . "/../../public/uploads/candidate/" . $fotoName;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto ke server.']);
        exit;
    }
}

// 5. Susun Data
$data = [
    'nama_lengkap'  => $nama_lengkap,
    'email'         => $email,
    'no_hp'         => $no_hp,
    'tanggal_lahir' => $tanggal_lahir,
    'jenis_kelamin' => $jenis_kelamin,
    'alamat'        => $alamat,
    'foto'          => $fotoName // Jika null, biasanya controller menghandle agar tidak menimpa foto lama
];

// 6. Eksekusi Update
$berhasil = CandidateController::updateProfile($id, $data);

if ($berhasil) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan ke database.']);
}
exit;
