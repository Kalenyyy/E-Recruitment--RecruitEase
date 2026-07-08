<?php

class Posisi
{
    // Fungsi baru untuk hitung total data (mendukung search)
    public static function count($conn, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM positions p 
                  JOIN divisions d ON p.divisi_id = d.id";

        if ($search != '') {
            $query .= " WHERE p.nama_posisi LIKE ? OR d.nama_divisi LIKE ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("ss", $searchParam, $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = mysqli_query($conn, $query);
        }

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Fungsi baru untuk ambil data paginasi
    public static function readPaginated($conn, $offset, $perPage, $search = '')
    {
        $query = "SELECT p.*, d.nama_divisi 
                  FROM positions p 
                  JOIN divisions d ON p.divisi_id = d.id";

        if ($search != '') {
            $query .= " WHERE p.nama_posisi LIKE ? OR d.nama_divisi LIKE ?";
            $query .= " ORDER BY p.id DESC LIMIT ?, ?";

            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("ssii", $searchParam, $searchParam, $offset, $perPage);
        } else {
            $query .= " ORDER BY p.id DESC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $offset, $perPage);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    // Fungsi lama tetap dipertahankan jika dibutuhkan di tempat lain
    public static function read($conn)
    {
        $query = "SELECT p.*, d.nama_divisi FROM positions p JOIN divisions d ON p.divisi_id = d.id ORDER BY p.id DESC";
        return mysqli_query($conn, $query);
    }

    public static function find($conn, $id)
    {
        $query = "SELECT * FROM positions WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function isDuplicate($conn, $nama_posisi, $id_divisi, $exclude_id = null)
    {
        $query = "SELECT id FROM positions WHERE nama_posisi = ? AND divisi_id = ?";

        // Jika sedang update, abaikan ID yang sedang diedit
        if ($exclude_id) {
            $query .= " AND id != ?";
        }

        $stmt = $conn->prepare($query);

        if ($exclude_id) {
            $stmt->bind_param("sii", $nama_posisi, $id_divisi, $exclude_id);
        } else {
            $stmt->bind_param("si", $nama_posisi, $id_divisi);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public static function insert($conn, $nama_posisi, $id_divisi)
    {
        // 1. Cek Duplikat
        if (self::isDuplicate($conn, $nama_posisi, $id_divisi)) {
            return "duplicate";
        }

        try {
            $sql = "INSERT INTO positions (nama_posisi, divisi_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nama_posisi, $id_divisi);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            throw $e;
        }
    }

    public static function update($conn, $id, $nama_posisi, $id_divisi)
    {
        // 1. Cek Duplikat (kecuali ID diri sendiri)
        if (self::isDuplicate($conn, $nama_posisi, $id_divisi, $id)) {
            return "duplicate";
        }

        try {
            $sql = "UPDATE positions SET nama_posisi = ?, divisi_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $nama_posisi, $id_divisi, $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            throw $e;
        }
    }

    public static function delete($conn, $id)
    {
        $sql = "DELETE FROM positions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
