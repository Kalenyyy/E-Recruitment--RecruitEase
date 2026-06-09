<?php

require_once __DIR__ . "/../init.php";

class Sertifikasi
{
    public static function insert(
        $conn,
        $candidate_id,
        $nama_sertifikasi,
        $penyelenggara,
        $tanggal_terbit,
        $file_sertifikasi
    ) {

        $sql = "
            INSERT INTO sertifikasi
            (
                candidate_id,
                nama_sertifikasi,
                penyelenggara,
                tanggal_terbit,
                file_sertifikasi
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "issss",
            $candidate_id,
            $nama_sertifikasi,
            $penyelenggara,
            $tanggal_terbit,
            $file_sertifikasi
        );

        return $stmt->execute();
    }

    public static function getAll($conn)
    {
        $sql = "
            SELECT
                s.*,
                c.nama_lengkap
            FROM sertifikasi s
            INNER JOIN candidates c
                ON c.id = s.candidate_id
            ORDER BY s.id_sertifikasi ASC
        ";

        return mysqli_query($conn, $sql);
    }

    public static function findById(
        $conn,
        $id_sertifikasi
    ) {

        $stmt = $conn->prepare(
            "
            SELECT *
            FROM sertifikasi
            WHERE id_sertifikasi = ?
            "
        );

        $stmt->bind_param(
            "i",
            $id_sertifikasi
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }

    public static function getByCandidateId(
        $conn,
        $candidate_id
    ) {

        $stmt = $conn->prepare(
            "
            SELECT *
            FROM sertifikasi
            WHERE candidate_id = ?
            ORDER BY id_sertifikasi ASC
            "
        );

        $stmt->bind_param(
            "i",
            $candidate_id
        );

        $stmt->execute();

        return $stmt->get_result();
    }

    public static function update(
        $conn,
        $id_sertifikasi,
        $nama_sertifikasi,
        $penyelenggara,
        $tanggal_terbit,
        $file_sertifikasi
    ) {

        $stmt = $conn->prepare(
            "
            UPDATE sertifikasi
            SET
                nama_sertifikasi = ?,
                penyelenggara = ?,
                tanggal_terbit = ?,
                file_sertifikasi = ?
            WHERE id_sertifikasi = ?
            "
        );

        $stmt->bind_param(
            "ssssi",
            $nama_sertifikasi,
            $penyelenggara,
            $tanggal_terbit,
            $file_sertifikasi,
            $id_sertifikasi
        );

        return $stmt->execute();
    }

    public static function delete(
        $conn,
        $id_sertifikasi
    ) {

        $stmt = $conn->prepare(
            "
            DELETE FROM sertifikasi
            WHERE id_sertifikasi = ?
            "
        );

        $stmt->bind_param(
            "i",
            $id_sertifikasi
        );

        return $stmt->execute();
    }
}