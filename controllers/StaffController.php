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
        if (User::findByUsername($post['username'])) {
            $errors['username'] = "Username sudah digunakan.";
        }
        if (User::findByEmail($post['email'])) {
            $errors['email'] = "Email sudah terdaftar.";
        }

        if (!empty($errors)) return ['status' => false, 'errors' => $errors];

        $user_id = User::insert($conn, $post['username'], $post['email'], password_hash($post['password'], PASSWORD_DEFAULT), 'hr');

        if (!$user_id) return ['status' => false, 'errors' => ['umum' => 'Gagal membuat akun.']];

        $fotoName = null;
        if (!empty($files['foto']['name'])) {
            $fotoName = time() . '_' . $files['foto']['name'];
            $targetDir = __DIR__ . "/../public/uploads/staff/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            move_uploaded_file($files['foto']['tmp_name'], $targetDir . $fotoName);
        }

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

        return $staffCreated ? ['status' => true] : ['status' => false, 'errors' => ['umum' => 'Gagal profil staff.']];
    }

    public static function toggleStatus($conn, $id)
    {
        $staff = Staff::find($conn, $id);
        if (!$staff) return false;
        $newStatus = ($staff['status'] == 'active') ? 'inactive' : 'active';
        return Staff::updateStatus($conn, $id, $newStatus);
    }

    public static function destroy($conn, $id)
    {
        $staffDeleted = Staff::delete($conn, $id);
        $userDeleted = User::delete($conn, $id);
        return $staffDeleted && $userDeleted;
    }

    public static function updateProfile($conn, $id, $data)
{
    return Staff::updateProfile($conn, $id, $data);
}


}
