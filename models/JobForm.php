<?php
class JobForm
{
    public static function create($conn, $data, $staff_id)
    {
        mysqli_begin_transaction($conn);
        try {
            $sqlJob = "INSERT INTO job_posting (
            posisi_id,          -- 1
            staff_id,           -- 2
            judul_job,          -- 3
            deskripsi,          -- 4
            lokasi,             -- 5
            tipe_pekerjaan,     -- 6
            gaji_min,           -- 7
            gaji_max,           -- 8
            status,             -- 9
            is_disabilitas,     -- 10
            is_remote_interview,-- 11
            is_remote_work,     -- 12
            additional_support, -- 13
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sqlJob);

            // Bersihkan angka dari titik format ribuan
            $gmin = (!empty($data['gaji_min'])) ? (float)str_replace('.', '', $data['gaji_min']) : null;
            $gmax = (!empty($data['gaji_max'])) ? (float)str_replace('.', '', $data['gaji_max']) : null;

            $is_dis = isset($data['is_disabilitas']) ? (int)$data['is_disabilitas'] : 0;
            $is_int = isset($data['is_remote_interview']) ? (int)$data['is_remote_interview'] : 0;
            $is_work = isset($data['is_remote_work']) ? (int)$data['is_remote_work'] : 0;

            $status = $data['status'] ?? 'draft';
            $support = !empty($data['additional_support']) ? $data['additional_support'] : null;

            // BIND PARAM SESUAI URUTAN KOLOM DI ATAS
            // Pattern: i(1) i(2) s(3) s(4) s(5) s(6) d(7) d(8) s(9) i(10) i(11) i(12) s(13)
            $stmt->bind_param(
                "iissssddsiiis",
                $data['posisi_id'],     // 1
                $staff_id,              // 2
                $data['judul_job'],     // 3
                $data['deskripsi'],     // 4
                $data['lokasi'],        // 5
                $data['tipe_pekerjaan'], // 6
                $gmin,                  // 7
                $gmax,                  // 8
                $status,                // 9
                $is_dis,                // 10
                $is_int,                // 11
                $is_work,               // 12
                $support                // 13
            );

            if (!$stmt->execute()) {
                throw new Exception("Gagal simpan job_posting: " . $stmt->error);
            }

            $job_id = $conn->insert_id;

            // Simpan Skills
            if (!empty($data['skill_ids'])) {
                $stmtS = $conn->prepare("INSERT INTO job_skills (job_id, skill_id, created_at) VALUES (?, ?, NOW())");
                foreach ($data['skill_ids'] as $sid) {
                    $stmtS->bind_param("ii", $job_id, $sid);
                    $stmtS->execute();
                }
            }

            // Simpan Tipe Disabilitas (Jika is_disabilitas aktif)
            if ($is_dis === 1 && !empty($data['disability_types'])) {
                $stmtD = $conn->prepare("INSERT INTO job_disabilitas (job_id, disability_type, created_at) VALUES (?, ?, NOW())");
                foreach ($data['disability_types'] as $type) {
                    $stmtD->bind_param("is", $job_id, $type);
                    $stmtD->execute();
                }
            }

            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            return false;
        }
    }

    public static function getAllJobs($conn)
    {
        $query = "SELECT jp.*, p.nama_posisi 
                  FROM job_posting jp 
                  LEFT JOIN positions p ON jp.posisi_id = p.id 
                  ORDER BY jp.id DESC";
        return mysqli_query($conn, $query);
    }

    // --- FUNGSI BARU UNTUK PAGINATION & SEARCH (BY NAME) ---
    public static function count($conn, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM job_posting";

        if ($search != '') {
            $query .= " WHERE judul_job LIKE ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("s", $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = mysqli_query($conn, $query);
        }

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public static function readPaginated($conn, $offset, $perPage, $search = '')
    {
        $query = "SELECT jp.*, p.nama_posisi 
                  FROM job_posting jp 
                  LEFT JOIN positions p ON jp.posisi_id = p.id";

        if ($search != '') {
            $query .= " WHERE jp.judul_job LIKE ? ORDER BY jp.id DESC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("sii", $searchParam, $offset, $perPage);
        } else {
            $query .= " ORDER BY jp.id DESC LIMIT ?, ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $offset, $perPage);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public static function find($conn, $id)
    {
        // 1. Ambil data utama job
        $sql = "SELECT jp.*, p.nama_posisi FROM job_posting jp 
            LEFT JOIN positions p ON jp.posisi_id = p.id 
            WHERE jp.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();

        if ($job) {
            // --- PERBAIKAN DI SINI ---
            $job['skills'] = [];    // Untuk simpan NAMA (buat view.php)
            $job['skill_ids'] = []; // Untuk simpan ID (buat edit.php)

            // Kita JOIN ke tabel skills supaya dapet namanya juga
            $sqlSkills = "SELECT js.skill_id, s.nama_skill 
                      FROM job_skills js 
                      JOIN skills s ON js.skill_id = s.id_skill
                      WHERE js.job_id = ?";

            $stmtS = $conn->prepare($sqlSkills);
            $stmtS->bind_param("i", $id);
            $stmtS->execute();
            $resS = $stmtS->get_result();

            while ($row = $resS->fetch_assoc()) {
                $job['skills'][] = $row['nama_skill']; // Ini yang dipake di view.php
                $job['skill_ids'][] = $row['skill_id'];  // Ini yang dipake di edit.php
            }
            // -------------------------

            // 3. Ambil Jenis Disabilitas
            $job['disability_types'] = [];
            $sqlDis = "SELECT disability_type FROM job_disabilitas WHERE job_id = ?";
            $stmtD = $conn->prepare($sqlDis);
            $stmtD->bind_param("i", $id);
            $stmtD->execute();
            $resD = $stmtD->get_result();
            while ($row = $resD->fetch_assoc()) {
                $job['disability_types'][] = $row['disability_type'];
            }
        }
        return $job;
    }

    public static function updateStatus($conn, $id, $status)
    {
        $stmt = $conn->prepare("UPDATE job_posting SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public static function update($conn, $id, $data)
{
    mysqli_begin_transaction($conn);
    try {
        $sqlJob = "UPDATE job_posting SET 
            posisi_id = ?,          -- 1
            judul_job = ?,          -- 2
            deskripsi = ?,          -- 3
            lokasi = ?,             -- 4
            tipe_pekerjaan = ?,     -- 5
            gaji_min = ?,           -- 6
            gaji_max = ?,           -- 7
            is_disabilitas = ?,     -- 8
            is_remote_interview = ?,-- 9
            is_remote_work = ?,     -- 10
            additional_support = ?  -- 11
            WHERE id = ?"; 

        $stmt = $conn->prepare($sqlJob);
        
        $gmin = (!empty($data['gaji_min'])) ? (float)str_replace('.', '', $data['gaji_min']) : null;
        $gmax = (!empty($data['gaji_max'])) ? (float)str_replace('.', '', $data['gaji_max']) : null;
        $is_dis = isset($data['is_disabilitas']) ? (int)$data['is_disabilitas'] : 0;
        $is_int = isset($data['is_remote_interview']) ? (int)$data['is_remote_interview'] : 0;
        $is_work = isset($data['is_remote_work']) ? (int)$data['is_remote_work'] : 0;
        $support = !empty($data['additional_support']) ? $data['additional_support'] : null;

        // Pattern: i(1) s(2) s(3) s(4) s(5) d(6) d(7) i(8) i(9) i(10) s(11) i(12)
        $stmt->bind_param(
            "issssddiiisi", 
            $data['posisi_id'],     // 1
            $data['judul_job'],     // 2
            $data['deskripsi'],     // 3
            $data['lokasi'],        // 4
            $data['tipe_pekerjaan'],// 5
            $gmin,                  // 6
            $gmax,                  // 7
            $is_dis,                // 8
            $is_int,                // 9
            $is_work,               // 10
            $support,               // 11
            $id                     // 12
        );
        $stmt->execute();

        // Sync Skills & Disabilitas (Hapus lama, Insert baru)
        $conn->query("DELETE FROM job_skills WHERE job_id = $id");
        if (!empty($data['skill_ids'])) {
            $stmtS = $conn->prepare("INSERT INTO job_skills (job_id, skill_id, created_at) VALUES (?, ?, NOW())");
            foreach ($data['skill_ids'] as $sid) {
                $stmtS->bind_param("ii", $id, $sid);
                $stmtS->execute();
            }
        }

        $conn->query("DELETE FROM job_disabilitas WHERE job_id = $id");
        if ($is_dis === 1 && !empty($data['disability_types'])) {
            $stmtD = $conn->prepare("INSERT INTO job_disabilitas (job_id, disability_type, created_at) VALUES (?, ?, NOW())");
            foreach ($data['disability_types'] as $type) {
                $stmtD->bind_param("is", $id, $type);
                $stmtD->execute();
            }
        }

        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

    // Fungsi delete juga harus menghapus anak-anaknya jika tidak pakai ON DELETE CASCADE di database
    public static function delete($conn, $id)
    {
        mysqli_begin_transaction($conn);
        try {
            // Hapus di tabel anak dulu (Opsional jika DB sudah CASCADE)
            $conn->query("DELETE FROM job_skills WHERE job_id = $id");
            $conn->query("DELETE FROM job_disabilitas WHERE job_id = $id");

            // Hapus tabel utama
            $stmt = $conn->prepare("DELETE FROM job_posting WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            return false;
        }
    }
}
