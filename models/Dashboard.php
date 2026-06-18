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
}
