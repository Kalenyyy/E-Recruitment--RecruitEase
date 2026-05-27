<?php

class Position
{
    public static function all($conn)
    {
        $sql = "SELECT positions.id, positions.nama_posisi, divisions.nama_divisi
            FROM positions
            JOIN divisions ON divisions.id = positions.divisi_id
        ";

        return mysqli_query($conn, $sql);
    }
}
