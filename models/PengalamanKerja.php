<?php

require_once __DIR__ . "/../init.php";

class PengalamanKerja
{
    public static function insert(
        $conn,
        $candidate_id,
        $nama_perusahaan,
        $posisi,
        $tanggal_mulai,
        $tanggal_selesai,
        $deskripsi
    ) {

        $sql = "
            INSERT INTO pengalaman_kerja
            (
                candidate_id,
                nama_perusahaan,
                posisi,
                tanggal_mulai,
                tanggal_selesai,
                deskripsi_pekerjaan
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssss",
            $candidate_id,
            $nama_perusahaan,
            $posisi,
            $tanggal_mulai,
            $tanggal_selesai,
            $deskripsi
        );

        return $stmt->execute();
    }

    public static function getAll($conn)
    {
        $sql = "
            SELECT
                pk.*,
                c.nama_lengkap
            FROM pengalaman_kerja pk
            INNER JOIN candidates c
                ON c.id = pk.candidate_id
            ORDER BY pk.id DESC
        ";

        return mysqli_query($conn, $sql);
    }

    public static function findById($conn, $id)
    {
        $stmt = $conn->prepare(
            "SELECT * FROM pengalaman_kerja WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // public static function getByCandidateId($conn, $candidate_id)
    // {
    //     $stmt = $conn->prepare(
    //         "
    //         SELECT *
    //         FROM pengalaman_kerja
    //         WHERE candidate_id = ?
    //         ORDER BY tanggal_mulai DESC
    //         "
    //     );

    //     $stmt->bind_param(
    //         "i",
    //         $candidate_id
    //     );

    //     $stmt->execute();

    //     return $stmt->get_result();
    // }

    public static function getByCandidateId($conn, $candidate_id)
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT *
             FROM pengalaman_kerja
             WHERE candidate_id = ?
             ORDER BY tanggal_mulai DESC"
        );

        mysqli_stmt_bind_param($stmt, "i", $candidate_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    public static function update(
        $conn,
        $id,
        $nama_perusahaan,
        $posisi,
        $tanggal_mulai,
        $tanggal_selesai,
        $deskripsi
    ) {

        $stmt = $conn->prepare("
        UPDATE pengalaman_kerja
        SET
            nama_perusahaan = ?,
            posisi = ?,
            tanggal_mulai = ?,
            tanggal_selesai = ?,
            deskripsi_pekerjaan = ?
        WHERE id = ?
    ");

        $stmt->bind_param(
            "sssssi",
            $nama_perusahaan,
            $posisi,
            $tanggal_mulai,
            $tanggal_selesai,
            $deskripsi,
            $id
        );

        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $stmt = $conn->prepare(
            "DELETE FROM pengalaman_kerja WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
