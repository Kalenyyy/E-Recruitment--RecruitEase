<?php
require_once __DIR__ . "/../init.php";

class Skill
{
    public static function getAll($conn)
    {
        return mysqli_query($conn, "SELECT * FROM skills ORDER BY nama_skill ASC");
    }

    // Menghitung total data untuk pagination (dengan filter search)
    public static function getTotalCount($conn, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM skills";
        if ($search != '') {
            $query .= " WHERE nama_skill LIKE ?";
            $stmt = $conn->prepare($query);
            $searchTerm = "%$search%";
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
        } else {
            $res = mysqli_query($conn, $query)->fetch_assoc();
        }
        return $res['total'];
    }

    // Mengambil data terbatas per halaman (dengan filter search)
    public static function getPaginated($conn, $page, $perPage, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        if ($search != '') {
            $query = "SELECT * FROM skills WHERE nama_skill LIKE ? ORDER BY nama_skill ASC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $searchTerm = "%$search%";
            $stmt->bind_param("sii", $searchTerm, $offset, $perPage);
        } else {
            $query = "SELECT * FROM skills ORDER BY nama_skill ASC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $offset, $perPage);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public static function findById($conn, $id)
    {
        $stmt = $conn->prepare("SELECT * FROM skills WHERE id_skill = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($conn, $nama_skill)
    {
        try {
            $stmt = $conn->prepare("INSERT INTO skills (nama_skill) VALUES (?)");
            $stmt->bind_param("s", $nama_skill);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) return "duplicate";
            return false;
        }
    }

    public static function update($conn, $id, $nama_skill)
    {
        try {
            $stmt = $conn->prepare("UPDATE skills SET nama_skill = ? WHERE id_skill = ?");
            $stmt->bind_param("si", $nama_skill, $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) return "duplicate";
            return false;
        }
    }

    public static function delete($conn, $id)
    {
        try {
            $stmt = $conn->prepare("DELETE FROM skills WHERE id_skill = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            // Error 1451: Data sedang digunakan di tabel lain (Foreign Key)
            if ($e->getCode() == 1451) return "restricted";
            return false;
        }
    }
}
