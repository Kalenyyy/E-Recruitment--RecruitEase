<?php

class Dashboard
{
    public static function getHRStats($conn)
    {
        $query = "SELECT 
            (SELECT COUNT(*) FROM job_posting WHERE status = 'open') as active_jobs,
            (SELECT COUNT(*) FROM candidate_apply_job) as total_pelamar,
            (SELECT COUNT(*) FROM candidate_apply_job WHERE status_lamaran = 'INTERVIEW') as total_interview,
            (SELECT COUNT(*) FROM candidate_apply_job WHERE status_lamaran = 'DITERIMA') as total_hired";

        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    }

    // Mengambil tren pelamar & diterima 7 hari terakhir (Anti-Bolong)
    public static function getWeeklyTrend($conn)
    {
        // 1. Ambil data Pelamar Masuk
        $queryPelamar = "SELECT DATE(tanggal_melamar) as tgl, COUNT(*) as jumlah 
                         FROM candidate_apply_job 
                         WHERE tanggal_melamar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY DATE(tanggal_melamar)";
        $resPelamar = mysqli_query($conn, $queryPelamar);
        $dataPelamar = [];
        while ($row = mysqli_fetch_assoc($resPelamar)) {
            $dataPelamar[$row['tgl']] = (int)$row['jumlah'];
        }

        // 2. Ambil data Pelamar yang Diterima (Hired) per hari
        // Asumsi: Kita cek berdasarkan tanggal_melamar atau jika ada kolom tanggal_update_status
        $queryHired = "SELECT DATE(tanggal_melamar) as tgl, COUNT(*) as jumlah 
                       FROM candidate_apply_job 
                       WHERE status_lamaran = 'DITERIMA' 
                       AND tanggal_melamar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                       GROUP BY DATE(tanggal_melamar)";
        $resHired = mysqli_query($conn, $queryHired);
        $dataHired = [];
        while ($row = mysqli_fetch_assoc($resHired)) {
            $dataHired[$row['tgl']] = (int)$row['jumlah'];
        }

        // 3. Gabungkan dan looping 7 hari ke belakang agar urut dan tidak ada hari yang kosong
        $labels = [];
        $seriesPelamar = [];
        $seriesHired = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D', strtotime($date)); // Output: Mon, Tue, Wed...
            $seriesPelamar[] = $dataPelamar[$date] ?? 0;
            $seriesHired[] = $dataHired[$date] ?? 0;
        }

        return [
            'labels' => $labels,
            'pelamar' => $seriesPelamar,
            'diterima' => $seriesHired
        ];
    }

    // Distribusi Status untuk Donut Chart
    public static function getStatusDistribution($conn)
    {
        $query = "SELECT status_lamaran as status, COUNT(*) as jumlah 
                  FROM candidate_apply_job 
                  GROUP BY status_lamaran";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 1. Statistik Lamaran Kandidat
    public static function getCandidateStats($conn, $candidateId)
    {
        $query = "SELECT 
        COUNT(*) as total_apply,
        SUM(CASE WHEN status_lamaran = 'ADMINISTRASI' THEN 1 ELSE 0 END) as review,
        SUM(CASE WHEN status_lamaran = 'INTERVIEW' THEN 1 ELSE 0 END) as interview,
        SUM(CASE WHEN status_lamaran = 'DITERIMA' THEN 1 ELSE 0 END) as diterima
    FROM candidate_apply_job 
    WHERE id_kandidat = $candidateId";

        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    }

    // 2. Riwayat Lamaran (Tabel)
    public static function getCandidateApplications($conn, $candidateId)
    {
        $query = "SELECT caj.*, jp.judul_job 
              FROM candidate_apply_job caj
              JOIN job_posting jp ON caj.id_lowongan = jp.id
              WHERE caj.id_kandidat = $candidateId
              ORDER BY caj.tanggal_melamar DESC 
              LIMIT 5";
        $result = mysqli_query($conn, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 3. Kelengkapan Profil (Untuk Progress Bar)
    public static function getProfileCompletion($conn, $candidateId)
    {
        // Ambil data kandidat
        $qCand = "SELECT foto, cv_file FROM candidates WHERE id = $candidateId";
        $cand = mysqli_fetch_assoc(mysqli_query($conn, $qCand));

        // Cek apakah ada data pendidikan
        $qEdu = "SELECT COUNT(*) as jml FROM pendidikan WHERE candidate_id = $candidateId";
        $edu = mysqli_fetch_assoc(mysqli_query($conn, $qEdu));

        return [
            'has_foto' => !empty($cand['foto']),
            'has_cv'   => !empty($cand['cv_file']),
            'has_edu'  => ($edu['jml'] > 0),
            // Contoh persentase sederhana
            'total_pct' => (!empty($cand['foto']) ? 33 : 0) + (!empty($cand['cv_file']) ? 33 : 0) + ($edu['jml'] > 0 ? 34 : 0)
        ];
    }

    // 4. Rekomendasi Lowongan (Skill Matching & Disabilitas)
    public static function getRecommendedJobs($conn, $candidateId)
    {
        // Ambil info disabilitas kandidat
        $qKandidat = "SELECT is_disabled FROM candidates WHERE id = $candidateId";
        $kandidat = mysqli_fetch_assoc(mysqli_query($conn, $qKandidat));
        $is_disabled = $kandidat['is_disabled'];

        // Query Rekomendasi:
        // 1. Utamakan lowongan yang sesuai status disabilitas
        // 2. Hitung jumlah skill yang match antara candidate_skills dan job_skills
        $query = "
        SELECT jp.*, 
               (SELECT COUNT(*) FROM job_skills js 
                JOIN candidate_skills cs ON js.skill_id = cs.skill_id 
                WHERE js.job_id = jp.id AND cs.candidate_id = $candidateId) as match_count
        FROM job_posting jp
        WHERE jp.status = 'open'
        ORDER BY 
            (CASE WHEN jp.is_disabilitas = $is_disabled THEN 1 ELSE 0 END) DESC,
            match_count DESC
        LIMIT 3";

        $result = mysqli_query($conn, $query);
        $jobs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Hitung persentase kecocokan sederhana (misal max skill job adalah 5)
            $row['match_percent'] = ($row['match_count'] > 0) ? min(100, ($row['match_count'] / 3) * 100) : 0;
            $jobs[] = $row;
        }
        return $jobs;
    }
}
