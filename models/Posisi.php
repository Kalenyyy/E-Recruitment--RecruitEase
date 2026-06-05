<?php

class Posisi
{
    public static function read($conn)
    {
        $query = "SELECT p.*, d.nama_divisi
                  FROM positions p
                  JOIN divisions d ON p.divisi_id = d.id
                  ORDER BY p.id DESC";

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

    public static function insert($conn, $nama_posisi, $id_divisi)
    {
        $sql = "INSERT INTO positions (nama_posisi, divisi_id)
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "si",
            $nama_posisi,
            $id_divisi
        );

        return $stmt->execute();
    }

    public static function update($conn, $id, $nama_posisi, $id_divisi)
    {
        $sql = "UPDATE positions
                SET nama_posisi = ?, divisi_id = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sii",
            $nama_posisi,
            $id_divisi,
            $id
        );

        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $sql = "DELETE FROM positions WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}


?>