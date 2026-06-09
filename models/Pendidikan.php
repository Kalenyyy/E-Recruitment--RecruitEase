<?php

class Pendidikan
{
    public static function getByCandidateId($conn, $candidateId)
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT *
             FROM pendidikan
             WHERE candidate_id = ?
             ORDER BY tahun_lulus DESC, tahun_masuk DESC"
        );

        mysqli_stmt_bind_param($stmt, "i", $candidateId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    public static function getById($conn, $id)
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT *
             FROM pendidikan
             WHERE id_pendidikan = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        return mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmt)
        );
    }

    public static function create($conn, $data)
    {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO pendidikan
        (
            candidate_id,
            institusi,
            jenjang,
            jurusan,
            tahun_masuk,
            tahun_lulus,
            ipk
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $candidateId = $data['candidate_id'];
        $institusi = $data['institusi'];
        $jenjang = $data['jenjang']; 
        $jurusan = $data['jurusan'] ?? null;
        $tahunMasuk = $data['tahun_masuk'];
        $tahunLulus = $data['tahun_lulus'];
        $ipk = $data['ipk'];

        mysqli_stmt_bind_param(
            $stmt,
            "isssiid",
            $candidateId,
            $institusi,
            $jenjang,
            $jurusan,
            $tahunMasuk,
            $tahunLulus,
            $ipk
        );

        return mysqli_stmt_execute($stmt);
    }

    public static function update($conn, $id, $data)
    {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE pendidikan
         SET
            institusi = ?,
            jenjang = ?,
            jurusan = ?,
            tahun_masuk = ?,
            tahun_lulus = ?,
            ipk = ?
         WHERE id_pendidikan = ?"
        );

        $institusi = $data['institusi'];
        $jenjang = $data['jenjang'];

        $jurusan = $data['jurusan'] ?? null;

        $tahunMasuk = (int)$data['tahun_masuk'];

        $tahunLulus = !empty($data['tahun_lulus'])
            ? (int)$data['tahun_lulus']
            : null;

        $ipk = ($data['ipk'] !== '' && $data['ipk'] !== null)
            ? (float)$data['ipk']
            : null;

        mysqli_stmt_bind_param(
            $stmt,
            "sssiidi",
            $institusi,
            $jenjang,
            $jurusan,
            $tahunMasuk,
            $tahunLulus,
            $ipk,
            $id
        );

        return mysqli_stmt_execute($stmt);
    }

    public static function delete($conn, $id)
    {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM pendidikan
             WHERE id_pendidikan = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        return mysqli_stmt_execute($stmt);
    }
}
