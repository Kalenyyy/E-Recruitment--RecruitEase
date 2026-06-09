<?php

class Divisi {
    private static $table = "divisions";

    public static function read($conn)
    {
        $query = "SELECT * FROM divisions ORDER BY id DESC";
        return mysqli_query($conn, $query);
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

    public static function update($conn, $id, $nama_divisi) {
     
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

    public static function delete($conn, $id) {
       $sql = "DELETE FROM divisions WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
