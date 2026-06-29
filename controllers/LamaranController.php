<?php
// controllers/LamaranController.php

class LamaranController
{
    public static function isProfileComplete($candidate)
    {
        if (!$candidate) return false;

        $requiredFields = ['alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto', 'cv_file'];
        foreach ($requiredFields as $field) {
            if (empty($candidate[$field])) return false;
        }
        return true;
    }

    public static function getCandidateHistory($conn, $user_id)
    {
        $candidate = LamaranModel::getCandidateByUserId($conn, $user_id);
        if (!$candidate) return null;

        return LamaranModel::getApplicationsByCandidateId($conn, $candidate['id']);
    }

    public static function getAppliedJobIds($conn, $candidate_id)
    {
        return LamaranModel::getAppliedJobIds($conn, $candidate_id);
    }

    public static function getCandidateData($conn, $user_id)
    {
        return LamaranModel::getCandidateByUserId($conn, $user_id);
    }

    public static function checkExistingApply($conn, $candidate_id, $job_id)
    {
        return LamaranModel::checkExistingApply($conn, $candidate_id, $job_id);
    }

    public static function kirimLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang)
    {
        return LamaranModel::insertLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang);
    }

    // ← Ditambahkan parameter $alasan_tolak
    public static function prosesResponOffering($conn, $id_transaksi, $respon, $alasan_tolak = null)
    {
        $validRespon = ['DITERIMA', 'DITOLAK'];
        if (!in_array($respon, $validRespon)) {
            return false;
        }

        return LamaranModel::updateOfferingResponse($conn, $id_transaksi, $respon, $alasan_tolak);
    }

    public static function getInterviewList($conn)
    {
        $user_id     = $_SESSION['user_id'];
        $role        = $_SESSION['role'];
        $id_kandidat = null;

        if ($role === 'candidate') {
            $candidate   = self::getCandidateData($conn, $user_id);
            $id_kandidat = $candidate['id'];
        }

        return [
            'upcoming' => LamaranModel::getInterviews($conn, $role, $id_kandidat, 'upcoming'),
            'past'     => LamaranModel::getInterviews($conn, $role, $id_kandidat, 'past'),
        ];
    }
}