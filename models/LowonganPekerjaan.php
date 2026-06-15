<?php
class LowonganPekerjaan
{
    public static function getJobs($conn, $filters, $page, $perPage)
    {
        $where = ["jp.status = 'open'"];
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $where[] = "(jp.judul_job LIKE ? OR jp.deskripsi LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        if (!empty($filters['tipe_pekerjaan'])) {
            $where[] = "jp.tipe_pekerjaan = ?";
            $params[] = $filters['tipe_pekerjaan'];
            $types .= "s";
        }
        if (!empty($filters['lokasi'])) {
            $where[] = "jp.lokasi = ?";
            $params[] = $filters['lokasi'];
            $types .= "s";
        }
        if ($filters['is_disabilitas'] === '1') {
            $where[] = "jp.is_disabilitas = 1";
        }
        if ($filters['is_remote_work'] === '1') {
            $where[] = "jp.is_remote_work = 1";
        }
        if ($filters['is_remote_interview'] === '1') {
            $where[] = "jp.is_remote_interview = 1";
        }

        // --- PERUBAHAN UTAMA UNTUK DISABILITY_TYPES ---
        if (!empty($filters['disability_types']) && is_array($filters['disability_types'])) {
            $disabilityPlaceholders = implode(',', array_fill(0, count($filters['disability_types']), '?'));
            $where[] = "EXISTS (
                SELECT 1 FROM job_disabilitas jd 
                WHERE jd.job_id = jp.id AND jd.disability_type IN ($disabilityPlaceholders)
            )";
            $params = array_merge($params, $filters['disability_types']);
            $types .= str_repeat('s', count($filters['disability_types']));
        }
        // --- AKHIR PERUBAHAN DISABILITY_TYPES ---

        $whereSQL = implode(" AND ", $where);

        // Count Total
        $countSql = "SELECT COUNT(*) as total FROM job_posting jp WHERE $whereSQL";
        $stmt = $conn->prepare($countSql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];

        // Get Data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT jp.*, 
                GROUP_CONCAT(DISTINCT s.nama_skill SEPARATOR ', ') as skills
                FROM job_posting jp
                LEFT JOIN job_skills js ON jp.id = js.job_id
                LEFT JOIN skills s ON js.skill_id = s.id_skill
                WHERE $whereSQL
                GROUP BY jp.id
                ORDER BY jp.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        $allParams = array_merge($params, [$perPage, $offset]);
        $allTypes = $types . "ii";
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();

        return [
            'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    public static function getLokasiList($conn)
    {
        return $conn->query("SELECT DISTINCT lokasi FROM job_posting WHERE status='open'")->fetch_all(MYSQLI_ASSOC);
    }

    public static function getJobById($conn, $id)
    {
        $sql = "SELECT jp.*, 
                GROUP_CONCAT(DISTINCT s.nama_skill SEPARATOR ', ') as skills
                FROM job_posting jp
                LEFT JOIN job_skills js ON jp.id = js.job_id
                LEFT JOIN skills s ON js.skill_id = s.id_skill
                WHERE jp.id = ? AND jp.status = 'open'
                GROUP BY jp.id";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public static function getDisabilityTypesByJobId($conn, $jobId)
    {
        $sql = "SELECT disability_type FROM job_disabilitas WHERE job_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jobId);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Sederhanakan format array agar mudah dibaca di View: ['visual', 'hearing']
        return array_column($result, 'disability_type');
    }
}
