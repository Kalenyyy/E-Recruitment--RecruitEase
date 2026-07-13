<?php
require_once __DIR__ . "/../init.php";

class StaffController
{
    public static function getAllStaff($conn)
    {
        return Staff::allStaff($conn);
    }

    // --- TAMBAHAN UNTUK PAGINATION ---
    public static function getPaginatedStaff($conn, $page, $perPage, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        return Staff::getPaginated($conn, $perPage, $offset, $search);
    }

    public static function getTotalCount($conn, $search = '')
    {
        return Staff::countAll($conn, $search);
    }
    // --------------------------------

    public static function show($conn, $id)
    {
        return Staff::find($conn, $id);
    }

    public static function getStaffByUserId($conn, $userId)
    {
        // Memanggil fungsi findByUserId yang sudah ada di Model Staff
        return Staff::findByUserId($conn, $userId);
    }

    public static function store($conn, $post, $files)
    {
        $errors = [];

        // 1. Validasi Duplikasi Akun
        if (User::findByUsername($post['username'])) {
            $errors['username'] = "Username sudah digunakan.";
        }
        if (User::findByEmail($post['email'])) {
            $errors['email'] = "Email sudah terdaftar.";
        }

        // 2. VALIDASI FILE FOTO (Backend Security)
        $fotoName = null;
        if (!empty($files['foto']['name'])) {
            $fileFoto = $files['foto'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $fileExtension = strtolower(pathinfo($fileFoto['name'], PATHINFO_EXTENSION));
            $fileMime = $fileFoto['type'];
            $fileSize = $fileFoto['size'];

            // Cek Ekstensi & MIME Type
            if (!in_array($fileMime, $allowedTypes) || !in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                $errors['foto'] = "Format foto tidak didukung! Gunakan JPG, JPEG, atau PNG.";
            }

            // Cek Ukuran (2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                $errors['foto'] = "Ukuran foto terlalu besar! Maksimal adalah 2MB.";
            }
        }

        // Jika ada error pada input atau file, hentikan proses sebelum insert ke database
        if (!empty($errors)) return ['status' => false, 'errors' => $errors];

        // 3. Insert Akun User (Setelah dipastikan data valid)
        $user_id = User::insert(
            $conn,
            $post['username'],
            $post['email'],
            password_hash($post['password'], PASSWORD_DEFAULT),
            'hr'
        );

        if (!$user_id) return ['status' => false, 'errors' => ['umum' => 'Gagal membuat akun user.']];

        // 4. Proses Upload Foto (Setelah user_id didapat)
        if (!empty($files['foto']['name'])) {
            $fotoName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $files['foto']['name']);
            $targetDir = __DIR__ . "/../public/uploads/staff/";

            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            if (!move_uploaded_file($files['foto']['tmp_name'], $targetDir . $fotoName)) {
                
                return ['status' => false, 'errors' => ['umum' => 'Gagal mengupload foto ke server.']];
            }
        }

        // 5. Insert Data Profil Staff
        $staffCreated = Staff::insert($conn, [
            'user_id' => $user_id,
            'nama_staff' => $post['nama_staff'],
            'email' => $post['email'],
            'alamat' => $post['alamat'],
            'no_telp' => $post['no_telp'],
            'jenis_kelamin' => $post['jenis_kelamin'],
            'tanggal_lahir' => $post['tanggal_lahir'],
            'foto' => $fotoName
        ]);

        return $staffCreated ? ['status' => true] : ['status' => false, 'errors' => ['umum' => 'Gagal membuat profil staff.']];
    }

    public static function toggleStatus($conn, $id)
    {
        $staff = Staff::findByUserId($conn, $id);

        if (!$staff) return false;

        $newStatus = ($staff['status'] == 'active') ? 'inactive' : 'active';

        return Staff::updateStatus($conn, $id, $newStatus);
    }

    public static function updateProfile($conn, $id, $data)
    {
        // 1. Cari data staff saat ini untuk mendapatkan user_id-nya
        $currentStaff = Staff::find($conn, $id);
        if (!$currentStaff) {
            return ['status' => false, 'message' => 'Data staff tidak ditemukan.'];
        }

        $userId = $currentStaff['user_id'];

        // 2. Validasi Format di level Controller (Double Check)

        // Cek spasi di username
        if (preg_match('/\s/', $data['username'])) {
            return ['status' => false, 'message' => 'Username tidak boleh mengandung spasi.'];
        }

        // Cek apakah nomor telepon hanya angka
        if (!preg_match('/^[0-9]+$/', $data['no_telp'])) {
            return ['status' => false, 'message' => 'Nomor telepon harus berupa angka.'];
        }

        // Cek format email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => false, 'message' => 'Format email tidak valid.'];
        }

        // 3. Pengecekan Duplikasi (Username & Email)
        // Kita panggil fungsi isUniqueExceptMe yang tadi kita buat di Model Staff

        if (!Staff::isUniqueExceptMe($conn, 'username', $data['username'], $userId)) {
            return ['status' => false, 'message' => 'Username sudah digunakan oleh orang lain.'];
        }

        if (!Staff::isUniqueExceptMe($conn, 'email', $data['email'], $userId)) {
            return ['status' => false, 'message' => 'Email sudah digunakan oleh orang lain.'];
        }

        // 4. Jika semua validasi lolos, panggil Model untuk eksekusi ke DB
        $update = Staff::updateProfile($conn, $id, $data);

        if ($update) {
            return ['status' => true, 'message' => 'Profil berhasil diperbarui.'];
        } else {
            return ['status' => false, 'message' => 'Gagal memperbarui profil di database.'];
        }
    }
}
