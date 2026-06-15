<?php
// models/lamaran.php

class LamaranModel
{
    // 1. Cek apakah kandidat sudah pernah melamar di lowongan ini
    public static function hasApplied($conn, $candidate_id, $job_id)
    {
        $query = "SELECT COUNT(*) as total FROM candidate_apply_job tl WHERE tl.id_kandidat = ? AND tl.id_lowongan = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param('ii', $candidate_id, $job_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] > 0;
        }
        return false;
    }

    // 2. KOREKSI STRATEGIS: Sinkronisasi parameter sesuai form (catatan, expert, pengalaman)
    public static function insertLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang)
    {
        $query = "INSERT INTO candidate_apply_job (id_kandidat, id_lowongan, catatan, expert_bidang, pengalaman_bidang, status_lamaran, tanggal_melamar) 
                  VALUES (?, ?, ?, ?, ?, 'ADMINISTRASI', NOW())";

        $stmt = $conn->prepare($query);

        if ($stmt) {
            // 'iissss' -> id_kandidat(i), id_lowongan(i), catatan(s), expert_bidang(s), pengalaman_bidang(s)
            $stmt->bind_param('iisss', $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang);
            return $stmt->execute();
        }
        return false;
    }

    // 3. FITUR BARU: Mengambil seluruh riwayat lamaran milik kandidat aktif beserta detail lowongan
    public static function getLamaranByCandidateId($conn, $candidateId)
    {
        $query = "
            SELECT 
                caj.id AS id_lamaran,
                caj.catatan,
                caj.expert_bidang,
                caj.pengalaman_bidang,
                caj.status_lamaran,
                caj.tanggal_melamar,
                jp.id AS job_id,
                jp.judul_job,
                jp.lokasi,
                jp.tipe_pekerjaan,
                jp.gaji
            FROM candidate_apply_job caj
            INNER JOIN job_posting jp ON caj.id_lowongan = jp.id
            WHERE caj.id_kandidat = ?
            ORDER BY caj.tanggal_melamar DESC
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $candidateId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    
}