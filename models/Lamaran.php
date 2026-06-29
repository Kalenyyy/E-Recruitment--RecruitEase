<?php
// models/lamaran.php

class LamaranModel
{
    public static function getCandidateByUserId($conn, $user_id)
    {
        $query = "SELECT * FROM candidates WHERE user_id = ?";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getApplicationsByCandidateId($conn, $candidate_id)
    {
        // ← Eksplisit ambil tolak_hr dan tolak_candidate
        $query = "
            SELECT
                caj.id,
                caj.id_lowongan,
                caj.id_kandidat,
                caj.status_lamaran,
                caj.tanggal_melamar,
                caj.catatan,
                caj.expert_bidang,
                caj.pengalaman_bidang,
                caj.tolak_hr,
                caj.tolak_candidate,
                jp.judul_job,
                jp.lokasi,
                jp.tipe_pekerjaan,
                jp.gaji_min,
                jp.gaji_max,
                ol.gaji_offering,
                ol.file_offering,
                ol.status AS status_respon_offering
            FROM candidate_apply_job caj
            JOIN job_posting jp ON caj.id_lowongan = jp.id
            LEFT JOIN offering_letter ol ON caj.id    = ol.id_candidate_apply_job
            WHERE caj.id_kandidat = ?
            ORDER BY caj.tanggal_melamar DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $candidate_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function checkExistingApply($conn, $candidate_id, $job_id)
    {
        $query = "SELECT id FROM candidate_apply_job WHERE id_kandidat = ? AND id_lowongan = ?";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param('ii', $candidate_id, $job_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public static function insertLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang)
    {
        $query = "INSERT INTO candidate_apply_job
                    (id_kandidat, id_lowongan, catatan, expert_bidang, pengalaman_bidang, status_lamaran, tanggal_melamar)
                  VALUES (?, ?, ?, ?, ?, 'ADMINISTRASI', NOW())";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param('iisss', $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang);
        return $stmt->execute();
    }

    public static function getAppliedJobIds($conn, $candidate_id)
    {
        $query = "SELECT id_lowongan FROM candidate_apply_job WHERE id_kandidat = ?";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param('i', $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int) $row['id_lowongan'];
        }
        return $ids;
    }

    public static function hasApplied($conn, $candidate_id, $job_id)
    {
        $query = "SELECT id FROM candidate_apply_job WHERE id_kandidat = ? AND id_lowongan = ?";
        $stmt  = $conn->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param('ii', $candidate_id, $job_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // ← Diperbaiki: simpan tolak_candidate saat kandidat menolak offering
    public static function updateOfferingResponse($conn, $id_transaksi, $respon, $alasan_tolak = null)
    {
        mysqli_begin_transaction($conn);

        try {
            // 1. Update status di tabel offering_letter
            $sql1  = "UPDATE offering_letter SET status = ? WHERE id_candidate_apply_job = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("si", $respon, $id_transaksi);
            $stmt1->execute();

            // 2. Update status utama + simpan alasan jika ditolak
            if ($respon === 'DITOLAK' && $alasan_tolak) {
                $sql2  = "UPDATE candidate_apply_job
                          SET status_lamaran = ?, tolak_candidate = ?
                          WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ssi", $respon, $alasan_tolak, $id_transaksi);
            } else {
                $sql2  = "UPDATE candidate_apply_job SET status_lamaran = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("si", $respon, $id_transaksi);
            }
            $stmt2->execute();

            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            return false;
        }
    }

    public static function getInterviews($conn, $role, $id, $type = 'upcoming')
    {
        if ($type === 'upcoming') {
            $filterCondition = "ji.status_interview = 'JADWAL' AND ji.tanggal_interview >= NOW()";
        } else {
            $filterCondition = "(ji.status_interview IN ('SELESAI', 'BATAL') OR ji.tanggal_interview < NOW())";
        }

        $roleCondition = ($role === 'candidate') ? "AND ji.id_kandidat = ?" : "";

        $query = "
            SELECT
                ji.*,
                c.nama_lengkap AS nama_kandidat,
                jp.judul_job,
                jp.lokasi,
                caj.status_lamaran
            FROM jadwal_interview ji
            JOIN candidates c             ON ji.id_kandidat              = c.id
            JOIN candidate_apply_job caj  ON ji.id_candidate_apply_job   = caj.id
            JOIN job_posting jp           ON caj.id_lowongan              = jp.id
            WHERE $filterCondition $roleCondition
            ORDER BY ji.tanggal_interview " . ($type === 'upcoming' ? 'ASC' : 'DESC');

        $stmt = $conn->prepare($query);
        if ($role === 'candidate') {
            $stmt->bind_param('i', $id);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}