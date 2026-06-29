<?php
require_once __DIR__ . '/../models/JobPosting.php';

class PelamarPekerjaanController
{
    public static function index($conn)
    {
        // Ambil data lowongan yang statusnya open saja
        return JobPostingModel::getOpenJobsWithApplicantCount($conn);
    }

    public static function getDetailJob($conn, $job_id)
    {
        return JobPostingModel::getJobDetails($conn, $job_id);
    }

    public static function getApplicants($conn, $job_id)
    {
        // REVISI: Query didefinisikan DAN langsung dieksekusi di sini
        $query = "SELECT 
                    c.*, 
                    caj.id as id_transaksi, 
                    caj.status_lamaran, 
                    caj.tanggal_melamar, 
                    caj.catatan, 
                    c.cv_file,              -- Pastikan cv_file juga ditarik jika ada di tabel ini
                    caj.expert_bidang, 
                    caj.pengalaman_bidang
                  FROM candidate_apply_job caj
                  JOIN candidates c ON caj.id_kandidat = c.id
                  WHERE caj.id_lowongan = ?
                  ORDER BY caj.tanggal_melamar DESC";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Query bermasalah: " . $conn->error);
        }

        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $applicants = [];
        while ($row = $result->fetch_assoc()) {
            // Mapping alias field agar sesuai dengan yang dipanggil di detail.php
            // Karena di detail.php lu memanggil $app['nama_lengkap'], pastikan nama kolom di tabel 'candidates' sesuai.
            // Jika di tabel namanya 'nama', kita mapping atau sesuaikan di query-nya.
            $applicants[] = $row;
        }

        return $applicants;
    }

    public static function getApplication($conn, $id_transaksi)
    {
        return JobPostingModel::getApplicationById($conn, $id_transaksi);
    }

    public static function ubahStatus(
        $conn,
        $id_transaksi,
        $status_baru,
        $tanggal_interview = null,
        $catatan = null,
        $alasan = null
    ) {
        $app = self::getApplication($conn, $id_transaksi);
        $id_kandidat = $app['id_kandidat'];

        if ($status_baru === 'INTERVIEW') {
            // Kirim $catatan ke model
            return JobPostingModel::moveKandidatKeInterview($conn, $id_transaksi, $id_kandidat, $tanggal_interview, $catatan);
        } else {
            return JobPostingModel::updateApplicationStatus(
                $conn,
                $id_transaksi,
                $status_baru,
                $alasan
            );
        }
    }

    public static function kirimOffering($conn, $id_transaksi, $gaji, $file_name)
    {
        // 1. Ambil data aplikasi untuk mendapatkan id_kandidat
        $app = self::getApplication($conn, $id_transaksi);
        if (!$app) return false;

        $id_kandidat = $app['id_kandidat'];

        // 2. Panggil fungsi di model yang sudah Anda buat
        return JobPostingModel::moveKandidatKeOffering(
            $conn,
            $id_transaksi,
            $id_kandidat,
            $gaji,
            $file_name
        );
    }
}
