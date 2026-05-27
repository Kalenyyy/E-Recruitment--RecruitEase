<?php
class Staff
{
    public static function allStaff($conn)
    {
        $query = "SELECT staff.*, users.email, users.username 
              FROM staff 
              JOIN users ON staff.user_id = users.id 
              ORDER BY staff.id DESC";
        return mysqli_query($conn, $query);
    }

    public static function find($conn, $id)
    {
        $query = "SELECT staff.*, users.email, users.username 
              FROM staff 
              JOIN users ON staff.user_id = users.id 
              WHERE users.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function findByUserId($conn, $user_id)
    {
        $sql = "SELECT * FROM staff WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function insert($conn, $data)
    {
        $sql = "INSERT INTO staff 
        (user_id, nama_staff, email, alamat, no_telp, jenis_kelamin, tanggal_lahir, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssss",
            $data['user_id'],
            $data['nama_staff'],
            $data['email'],
            $data['alamat'],
            $data['no_telp'],
            $data['jenis_kelamin'],
            $data['tanggal_lahir'],
            $data['foto']
        );

        return $stmt->execute();
    }

    public static function updateStatus($conn, $id, $status)
    {
        $sql = "UPDATE staff SET status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        // 1. Cari staff berdasarkan user_id
        $staff = self::find($conn, $id);
        if (!$staff) return false;

        // 2. Hapus foto jika ada
        if (!empty($staff['foto'])) {
            $fotoPath = __DIR__ . "/../public/uploads/staff/" . $staff['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        // 3. Hapus data staff
        $sql = "DELETE FROM staff WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
