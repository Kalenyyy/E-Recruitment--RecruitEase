<?php
require_once __DIR__ . "/../init.php";

class StaffController
{
    // public static function getAllPosition($conn)
    // {
    //     $positions = Position::all($conn);
    //     return $positions;
    // }

    public static function getAllStaff($conn)
    {
        return Staff::allStaff($conn);
    }

    public static function show($conn, $id)
    {
        return Staff::find($conn, $id);
    }

    public static function store($conn, $post, $files)
    {
        $errors = [];

        // Cek apakah Username sudah ada
        if (User::findByUsername($post['username'])) {
            $errors['username'] = "Username sudah digunakan oleh akun lain.";
        }

        // Cek apakah Email sudah ada
        if (User::findByEmail($post['email'])) {
            $errors['email'] = "Email sudah terdaftar dalam sistem.";
        }

        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        $user_id = User::insert(
            $conn,
            $post['username'],
            $post['email'],
            password_hash($post['password'], PASSWORD_DEFAULT),
            'hr'
        );

        if (!$user_id) {
            return ['status' => false, 'errors' => ['umum' => 'Gagal membuat akun user.']];
        }

        // 4. Upload foto
        $fotoName = null;
        if (!empty($files['foto']['name'])) {
            $fotoName = time() . '_' . $files['foto']['name'];
            $targetDir = __DIR__ . "/../public/uploads/staff/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            move_uploaded_file($files['foto']['tmp_name'], $targetDir . $fotoName);
        }

        // 5. Staff insert
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

        if ($staffCreated) {
            return ['status' => true];
        } else {
            return ['status' => false, 'errors' => ['umum' => 'Gagal menyimpan data profil staff.']];
        }
    }

    public static function toggleStatus($conn, $id)
    {
        $staff = Staff::find($conn, $id);
        if (!$staff) return false;

        // Active / inactive
        if ($staff['status'] == 'active') {
            $newStatus = 'inactive';
        } else {
            $newStatus = 'active';
        }
        return Staff::updateStatus($conn, $id, $newStatus);
    }

    public static function destroy($conn, $id)
    {
        // 1. Hapus data staff
        $staffDeleted = Staff::delete($conn, $id);

        // 2. Hapus data user
        $userDeleted = User::delete($conn, $id);

        return $staffDeleted && $userDeleted;
    }
}
