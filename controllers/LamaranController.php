<?php
// controllers/LamaranController.php

class LamaranController
{

    /**
     * Memeriksa apakah kandidat sudah pernah melamar di lowongan tertentu
     * Disesuaikan dengan tabel candidate_apply_job (id_kandidat, id_lowongan)
     */
    public static function checkExistingApply($conn, $candidate_id, $job_id)
    {
        $query = "SELECT id FROM candidate_apply_job WHERE id_kandidat = ? AND id_lowongan = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $candidate_id, $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Menyimpan data transaksi lamaran baru ke tabel candidate_apply_job
     */
    public static function kirimLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang)
    {
        // Default status_lamaran sesuai tipe ENUM di database lu: 'ADMINISTRASI'
        $status_lamaran = 'ADMINISTRASI';

        $query = "INSERT INTO candidate_apply_job (id_kandidat, id_lowongan, catatan, expert_bidang, pengalaman_bidang, status_lamaran, tanggal_melamar) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        // 'iissss' -> id_kandidat(i), id_lowongan(i), catatan(s), expert_bidang(s), pengalaman_bidang(s), status_lamaran(s)
        $stmt->bind_param('iissss', $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang, $status_lamaran);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public static function getAppliedJobIds($conn, $candidate_id)
    {
        $query = "SELECT id_lowongan FROM candidate_apply_job WHERE id_kandidat = ?";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $candidate_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $ids = [];

        while ($row = $result->fetch_assoc()) {
            $ids[] = (int)$row['id_lowongan'];
        }

        return $ids;
    }

    public static function getLamaranSaya($conn, $candidateId)
    {
        $query = "
        SELECT
            caj.*,
            jp.judul_job,
            jp.lokasi,
            jp.tipe_pekerjaan,
            jp.gaji

        FROM candidate_apply_job caj

        JOIN job_posting jp
            ON caj.id_lowongan = jp.id

        WHERE caj.id_kandidat = ?

        ORDER BY caj.tanggal_melamar DESC
    ";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $candidateId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
