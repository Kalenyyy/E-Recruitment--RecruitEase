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
        
        -- Tambahkan GROUP_CONCAT untuk disabilitas di sini --
        GROUP_CONCAT(DISTINCT cd.disability_type SEPARATOR ', ') as disability_types,

        GROUP_CONCAT(DISTINCT s.nama_skill ORDER BY s.nama_skill SEPARATOR ', ') as skills,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' | ', pk.nama_perusahaan, pk.posisi, pk.tanggal_mulai, pk.tanggal_selesai, pk.deskripsi_pekerjaan) ORDER BY pk.tanggal_mulai DESC SEPARATOR ' | ') as experiences,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' - ', ser.nama_sertifikasi, ser.penyelenggara, DATE_FORMAT(ser.tanggal_terbit, '%d-%m-%Y')) ORDER BY ser.tanggal_terbit DESC SEPARATOR ' | ') as achievements

    FROM candidate_apply_job tl
    JOIN candidates c ON tl.id_kandidat = c.id
    JOIN job_posting jp ON tl.id_lowongan = jp.id
    
    -- Tambahkan LEFT JOIN ke tabel disabilitas --
    LEFT JOIN candidate_disabilities cd ON c.id = cd.candidate_id
    
    LEFT JOIN candidate_skills cs ON c.id = cs.candidate_id
    LEFT JOIN skills s ON cs.skill_id = s.id_skill
    LEFT JOIN pengalaman_kerja pk ON c.id = pk.candidate_id
    LEFT JOIN sertifikasi ser ON c.id = ser.candidate_id

    WHERE tl.id = ?
    GROUP BY tl.id
    ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_transaksi);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function moveKandidatKeInterview($conn, $id_transaksi, $id_kandidat, $tanggal_interview, $catatan)
    {
        mysqli_begin_transaction($conn);
        try {
            // 1. Update status lamaran
            $queryUpdate = "UPDATE candidate_apply_job SET status_lamaran = 'INTERVIEW' WHERE id = ?";
            $stmt1 = $conn->prepare($queryUpdate);
            $stmt1->bind_param('i', $id_transaksi);
            $stmt1->execute();

            // 2. Insert ke jadwal_interview (tambahkan kolom catatan)
            $queryInsert = "INSERT INTO jadwal_interview (id_kandidat, id_candidate_apply_job, tanggal_interview, status_interview, catatan) 
                        VALUES (?, ?, ?, 'JADWAL', ?)";
            $stmt2 = $conn->prepare($queryInsert);
            $stmt2->bind_param('iiss', $id_kandidat, $id_transaksi, $tanggal_interview, $catatan);
            $stmt2->execute();

            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            return false;
        }
    }

    public static function moveKandidatKeOffering(
        $conn,
        $id_transaksi,
        $id_kandidat,
        $gaji_offering,
        $file_offering
    ) {
        mysqli_begin_transaction($conn);

        try {
            // 1. Update status utama di candidate_apply_job menjadi 'OFFERING'
            $query1 = "
            UPDATE candidate_apply_job
            SET status_lamaran = 'OFFERING'
            WHERE id = ?
        ";
            $stmt1 = $conn->prepare($query1);
            $stmt1->bind_param("i", $id_transaksi);
            $stmt1->execute();

            // 2. Tambahkan Data ke tabel offering_letter
            $query2 = "
            INSERT INTO offering_letter (
                id_kandidat,
                id_candidate_apply_job,
                gaji_offering,
                tanggal_offering,
                file_offering
            )
            VALUES (?, ?, ?, NOW(), ?)
        ";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param(
                "iiis",
                $id_kandidat,
                $id_transaksi,
                $gaji_offering,
                $file_offering
            );
            $stmt2->execute();

            // 3. UPDATE JADWAL INTERVIEW MENJADI 'SELESAI'
            // Kita update record yang berhubungan dengan transaksi ini 
            // yang statusnya masih 'JADWAL'
            $query3 = "
            UPDATE jadwal_interview 
            SET status_interview = 'SELESAI' 
            WHERE id_candidate_apply_job = ? 
            AND status_interview = 'JADWAL'
        ";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("i", $id_transaksi);
            $stmt3->execute();

            // Jika semua query berhasil, simpan perubahan
            mysqli_commit($conn);

            return true;
        } catch (Exception $e) {
            // Jika ada salah satu query yang gagal, batalkan semua (rollback)
            mysqli_rollback($conn);
            return false;
        }
    }

    // 2. Fungsi update status biasa (untuk DITOLAK atau status lain tanpa jadwal)
    public static function updateApplicationStatus(
        $conn,
        $id_transaksi,
        $status_baru,
        $alasan = null
    ) {

        if ($status_baru == "DITOLAK") {

            $query = "
        UPDATE candidate_apply_job
        SET
            status_lamaran = ?,
            tolak_hr = ?
        WHERE id = ?
        ";

            $stmt = $conn->prepare($query);

            $stmt->bind_param(
                "ssi",
                $status_baru,
                $alasan,
                $id_transaksi
            );
        } else {

            $query = "
        UPDATE candidate_apply_job
        SET status_lamaran = ?
        WHERE id = ?
        ";

            $stmt = $conn->prepare($query);

            $stmt->bind_param(
                "si",
                $status_baru,
                $id_transaksi
            );
        }

        return $stmt->execute();
    }
}
