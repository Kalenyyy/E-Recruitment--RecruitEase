<?php

class JobPostingModel
{

    // Ambil semua lowongan yang berstatus OPEN beserta jumlah pelamarnya
    public static function getOpenJobsWithApplicantCount($conn)
    {
        $query = "SELECT jp.id, jp.judul_job, jp.tipe_pekerjaan, jp.lokasi, jp.created_at, p.nama_posisi,
                  (SELECT COUNT(*) FROM candidate_apply_job tl WHERE tl.id_lowongan = jp.id) as total_pelamar
                  FROM job_posting jp
                  JOIN positions p ON jp.posisi_id = p.id
                  WHERE jp.status = 'open'
                  ORDER BY jp.created_at DESC";

        $result = mysqli_query($conn, $query);
        $data = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public static function getJobDetails($conn, $job_id)
    {
        $query = "SELECT jp.*, p.nama_posisi 
              FROM job_posting jp
              JOIN positions p ON jp.posisi_id = p.id
              WHERE jp.id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $job_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    // Ambil daftar pelamar yang melamar pada lowongan tertentu
    public static function getApplicantsByJob($conn, $job_id)
    {
        $query = "SELECT tl.id as id_transaksi, tl.status_lamaran, tl.tanggal_melamar, tl.catatan,
                     c.id as id_kandidat, c.nama_lengkap, c.email, c.no_hp, c.cv_file
              FROM candidate_apply_job tl
              JOIN candidates c ON tl.id_kandidat = c.id
              WHERE tl.id_lowongan = ?
              ORDER BY tl.tanggal_melamar DESC";

        $stmt = $conn->prepare($query);
        $data = [];
        if ($stmt) {
            $stmt->bind_param('i', $job_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Ambil data transaksi lamaran spesifik berdasarkan ID Transaksi
    public static function getApplicationById($conn, $id_transaksi)
{
    $query = "
    SELECT
        tl.*,

        c.nama_lengkap,
        c.email,
        c.no_hp,
        c.alamat,
        c.tanggal_lahir,
        c.jenis_kelamin,

        c.is_disabled,
        c.disability_description,

        c.foto,
        c.cv_file,

        jp.judul_job,
        
        GROUP_CONCAT(DISTINCT s.nama_skill ORDER BY s.nama_skill SEPARATOR ', ') as skills,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' | ', pk.nama_perusahaan, pk.posisi, pk.tanggal_mulai, pk.tanggal_selesai, pk.deskripsi_pekerjaan) ORDER BY pk.tanggal_mulai DESC SEPARATOR ' | ') as experiences,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' - ', ser.nama_sertifikasi, ser.penyelenggara, DATE_FORMAT(ser.tanggal_terbit, '%d-%m-%Y')) ORDER BY ser.tanggal_terbit DESC SEPARATOR ' | ') as achievements

    FROM candidate_apply_job tl

    JOIN candidates c
        ON tl.id_kandidat = c.id

    JOIN job_posting jp
        ON tl.id_lowongan = jp.id
    
    LEFT JOIN candidate_skills cs
        ON c.id = cs.candidate_id
    
    LEFT JOIN skills s
        ON cs.skill_id = s.id_skill
    
    LEFT JOIN pengalaman_kerja pk
        ON c.id = pk.candidate_id
    
    LEFT JOIN sertifikasi ser
        ON c.id = ser.candidate_id

    WHERE tl.id = ?
    GROUP BY tl.id
    ";

    $stmt = $conn->prepare($query);

    $stmt->bind_param("i", $id_transaksi);

    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

    // Update status lamaran kerja kandidat
    public static function updateApplicationStatus($conn, $id_transaksi, $status_baru)
    {
        $query = "UPDATE candidate_apply_job SET status_lamaran = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('si', $status_baru, $id_transaksi);
            return $stmt->execute();
        }
        return false;
    }
}
