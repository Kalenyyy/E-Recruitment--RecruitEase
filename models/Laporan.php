<?php
class Laporan
{
    // LAPORAN 1: Data Kandidat yang minimal sudah melamar 1x
    public static function getKandidatPernahApply($conn)
    {
        $sql = "SELECT DISTINCT c.* 
                FROM candidates c
                INNER JOIN candidate_apply_job caj ON c.id = caj.id_kandidat
                ORDER BY c.nama_lengkap ASC";
        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // LAPORAN 2: Rekap Lowongan Kerja
    public static function getRekapLowongan($conn)
    {
        $sql = "SELECT jp.*, p.nama_posisi, d.nama_divisi, 
                (SELECT COUNT(*) FROM candidate_apply_job WHERE id_lowongan = jp.id) as total_pelamar
                FROM job_posting jp 
                JOIN positions p ON jp.posisi_id = p.id
                JOIN divisions d ON p.divisi_id = d.id
                ORDER BY jp.created_at DESC";
        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Tambahkan di dalam class Laporan
    public static function getPelamarByJob($conn, $job_id)
    {
        $job_id = mysqli_real_escape_string($conn, $job_id);
        $sql = "SELECT 
                caj.id as id_transaksi, caj.status_lamaran, caj.tanggal_melamar, 
                caj.expert_bidang, caj.pengalaman_bidang, caj.catatan as catatan_lamaran,
                caj.tolak_HR, caj.tolak_candidate,
                c.nama_lengkap, c.email, c.no_hp, c.jenis_kelamin, c.alamat, 
                c.is_disabled, c.disability_description,
                -- Subquery untuk mengambil skill pelamar
                (SELECT GROUP_CONCAT(s.nama_skill SEPARATOR ', ') 
                 FROM candidate_skills cs 
                 JOIN skills s ON cs.skill_id = s.id_skill 
                 WHERE cs.candidate_id = c.id) as daftar_skill,
                -- Subquery untuk mengambil jenis disabilitas pelamar
                (SELECT GROUP_CONCAT(cd.disability_type SEPARATOR ', ') 
                 FROM candidate_disabilities cd 
                 WHERE cd.candidate_id = c.id) as jenis_disabilitas
            FROM candidate_apply_job caj
            JOIN candidates c ON caj.id_kandidat = c.id
            WHERE caj.id_lowongan = '$job_id'
            ORDER BY caj.tanggal_melamar DESC";

        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Tambahan untuk mendapatkan info judul lowongan (untuk nama file excel)
    public static function getJobTitle($conn, $job_id)
    {
        $job_id = mysqli_real_escape_string($conn, $job_id);
        $result = mysqli_query($conn, "SELECT judul_job FROM job_posting WHERE id = '$job_id'");
        $data = mysqli_fetch_assoc($result);
        return $data['judul_job'] ?? 'Lowongan';
    }

    public static function getRekapStatusPerJob($conn)
    {
        $sql = "SELECT 
                jp.judul_job,
                COUNT(caj.id) as total_pelamar,
                COUNT(CASE WHEN caj.status_lamaran = 'ADMINISTRASI' THEN 1 END) as jml_administrasi,
                COUNT(CASE WHEN caj.status_lamaran = 'INTERVIEW' THEN 1 END) as jml_interview,
                COUNT(CASE WHEN caj.status_lamaran = 'OFFERING' THEN 1 END) as jml_offering,
                COUNT(CASE WHEN caj.status_lamaran = 'DITERIMA' THEN 1 END) as jml_diterima,
                COUNT(CASE WHEN caj.status_lamaran = 'DITOLAK' THEN 1 END) as jml_ditolak
            FROM job_posting jp
            LEFT JOIN candidate_apply_job caj ON jp.id = caj.id_lowongan
            GROUP BY jp.id, jp.judul_job
            ORDER BY jp.created_at DESC";

        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
