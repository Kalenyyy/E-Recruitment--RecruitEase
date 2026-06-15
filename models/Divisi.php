<?php

class Divisi
{
    private static $table = "divisions";

    public static function read($conn)
    {
        $query = "SELECT * FROM divisions ORDER BY id DESC";
        return mysqli_query($conn, $query);
    }

    public static function count($conn, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . self::$table;

        if ($search != '') {
            $query .= " WHERE nama_divisi LIKE ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("s", $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = mysqli_query($conn, $query);
        }

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Fungsi baru: Ambil data dengan limit (Pagination) dan search
    public static function readPaginated($conn, $offset, $perPage, $search = '')
    {
        $query = "SELECT * FROM " . self::$table;

        if ($search != '') {
            $query .= " WHERE nama_divisi LIKE ? ORDER BY id DESC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("sii", $searchParam, $offset, $perPage);
        } else {
            $query .= " ORDER BY id DESC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $offset, $perPage);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public static function insert($conn, $nama_divisi)
    {

        $sql = "INSERT INTO divisions (nama_divisi) VALUES (?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nama_divisi);

        return $stmt->execute();
    }

    // KLO BUTUH FUNGSI CARI DIVISI BERDASARKAN ID, BISA PAKE FUNGSI INI

    public static function find($conn, $id)
    {
        $query = "SELECT * FROM divisions WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function update($conn, $id, $nama_divisi)
    {

        $sql = "UPDATE divisions
                SET nama_divisi = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "si",
            $nama_divisi,
            $id
        );

        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $sql = "DELETE FROM divisions WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
