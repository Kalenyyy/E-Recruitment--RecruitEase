<?php

require_once __DIR__ . "/../init.php";

class Candidate
{
    public static function insert($conn, $user_id, $nama_lengkap, $email, $no_hp)
    {
        $sql = "INSERT INTO candidates (user_id, nama_lengkap, email, no_hp)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $nama_lengkap, $email, $no_hp);

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public static function findByUserId($conn, $user_id)
    {
        $sql = "SELECT * FROM candidates WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function findById($conn, $id)
    {
        $sql = "SELECT * FROM candidates WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function updateProfile($conn, $id, $data)
    {
        $sql = "UPDATE candidates SET 
        nama_lengkap = ?,
        email = ?,
        no_hp = ?,
        tanggal_lahir = ?,
        jenis_kelamin = ?,
        alamat = ?,
        foto = COALESCE(?, foto)
        WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssssssi",
            $data['nama_lengkap'],
            $data['email'],
            $data['no_hp'],
            $data['tanggal_lahir'],
            $data['jenis_kelamin'],
            $data['alamat'],
            $data['foto'],
            $id
        );

        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $sql = "DELETE FROM candidates WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function getAll($conn)
    {
        $sql = "SELECT c.*, u.username, u.email as user_email 
                FROM candidates c 
                JOIN users u ON c.user_id = u.id 
                ORDER BY c.created_at DESC";

        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
