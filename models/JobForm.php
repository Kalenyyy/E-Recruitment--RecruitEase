<?php
class JobForm
{
    public static function create($conn, $data, $staff_id)
    {
        mysqli_begin_transaction($conn);

        try {
            // --- A. INSERT KE TABEL UTAMA (job_posting) ---
            $sqlJob = "INSERT INTO job_posting (
            posisi_id, staff_id, judul_job, deskripsi, lokasi, 
            tipe_pekerjaan, gaji, status, is_disabilitas, 
            is_remote_interview, is_remote_work, additional_support, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sqlJob);

            $is_disabilitas = isset($data['is_disabilitas']) ? (int)$data['is_disabilitas'] : 0;
            $is_remote_interview = isset($data['is_remote_interview']) ? (int)$data['is_remote_interview'] : 0;
            $is_remote_work = isset($data['is_remote_work']) ? (int)$data['is_remote_work'] : 0;

            // Gaji: Jika kosong set NULL
            $gaji = (!empty($data['gaji'])) ? $data['gaji'] : null;

            // Status: Ambil dari POST (di view tadi isinya 'open'), jika tidak ada default 'draft'
            $status = $data['status'] ?? 'draft';

            // Additional Support: Jika kosong set NULL
            $additional_support = !empty($data['additional_support']) ? $data['additional_support'] : null;

            $stmt->bind_param(
                "iissssssiiis",
                $data['posisi_id'],
                $staff_id,
                $data['judul_job'],
                $data['deskripsi'],
                $data['lokasi'],
                $data['tipe_pekerjaan'],
                $gaji,
                $status,
                $is_disabilitas,
                $is_remote_interview,
                $is_remote_work,
                $additional_support
            );

            if (!$stmt->execute()) {
                throw new Exception("Gagal simpan job_posting: " . $stmt->error);
            }

            $job_id = $conn->insert_id;

            // --- B. INSERT KE TABEL job_skills ---
            if (!empty($data['skill_ids']) && is_array($data['skill_ids'])) {
                $sqlSkill = "INSERT INTO job_skills (job_id, skill_id, created_at) VALUES (?, ?, NOW())";
                $stmtSkill = $conn->prepare($sqlSkill);
                foreach ($data['skill_ids'] as $sid) {
                    $stmtSkill->bind_param("ii", $job_id, $sid);
                    $stmtSkill->execute();
                }
            }

            // --- C. INSERT KE TABEL job_disabilitas ---
            if ($is_disabilitas === 1 && !empty($data['disability_types']) && is_array($data['disability_types'])) {
                $sqlDis = "INSERT INTO job_disabilitas (job_id, disability_type, created_at) VALUES (?, ?, NOW())";
                $stmtDis = $conn->prepare($sqlDis);
                foreach ($data['disability_types'] as $type) {
                    $stmtDis->bind_param("is", $job_id, $type);
                    $stmtDis->execute();
                }
            }

            mysqli_commit($conn);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            // Hapus error_log dan ganti dengan die untuk debug:
            die("LOGIC ERROR: " . $e->getMessage());
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
            // 1. Update Tabel Utama
            $sqlJob = "UPDATE job_posting SET 
            posisi_id = ?, judul_job = ?, deskripsi = ?, lokasi = ?, 
            tipe_pekerjaan = ?, gaji = ?, is_disabilitas = ?, 
            is_remote_interview = ?, is_remote_work = ?, additional_support = ?
            WHERE id = ?";

            $stmt = $conn->prepare($sqlJob);
            $is_disabilitas = isset($data['is_disabilitas']) ? (int)$data['is_disabilitas'] : 0;
            $is_remote_interview = isset($data['is_remote_interview']) ? (int)$data['is_remote_interview'] : 0;
            $is_remote_work = isset($data['is_remote_work']) ? (int)$data['is_remote_work'] : 0;
            $gaji = (!empty($data['gaji'])) ? $data['gaji'] : null;
            $additional_support = !empty($data['additional_support']) ? $data['additional_support'] : null;

            $stmt->bind_param(
                "isssssiiisi",
                $data['posisi_id'],
                $data['judul_job'],
                $data['deskripsi'],
                $data['lokasi'],
                $data['tipe_pekerjaan'],
                $gaji,
                $is_disabilitas,
                $is_remote_interview,
                $is_remote_work,
                $additional_support,
                $id
            );
            $stmt->execute();

            // 2. Update Skills (Hapus yang lama, insert yang baru)
            $conn->query("DELETE FROM job_skills WHERE job_id = $id");
            if (!empty($data['skill_ids'])) {
                $stmtSkill = $conn->prepare("INSERT INTO job_skills (job_id, skill_id, created_at) VALUES (?, ?, NOW())");
                foreach ($data['skill_ids'] as $sid) {
                    $stmtSkill->bind_param("ii", $id, $sid);
                    $stmtSkill->execute();
                }
            }

            // 3. Update Jenis Disabilitas (Hapus yang lama, insert yang baru)
            $conn->query("DELETE FROM job_disabilitas WHERE job_id = $id");
            if ($is_disabilitas === 1 && !empty($data['disability_types'])) {
                $stmtDis = $conn->prepare("INSERT INTO job_disabilitas (job_id, disability_type, created_at) VALUES (?, ?, NOW())");
                foreach ($data['disability_types'] as $type) {
                    $stmtDis->bind_param("is", $id, $type);
                    $stmtDis->execute();
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
