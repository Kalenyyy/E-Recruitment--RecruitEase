<?php
class LowonganPekerjaanController
{
    public static function jelajahiLowongan($conn)
    {
        $filters = [
            'search'              => trim($_GET['search'] ?? ''),
            'tipe_pekerjaan'      => trim($_GET['tipe_pekerjaan'] ?? ''),
            'lokasi'              => trim($_GET['lokasi'] ?? ''),
            'is_disabilitas'      => $_GET['is_disabilitas'] ?? '',
            'is_remote_work'      => $_GET['is_remote_work'] ?? '',
            'is_remote_interview' => $_GET['is_remote_interview'] ?? '',
            'disability_types'    => $_GET['disability_types'] ?? [], 
        ];

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

        $result          = LowonganPekerjaan::getJobs($conn, $filters, $page, $perPage);
        $lokasiList      = LowonganPekerjaan::getLokasiList($conn);

        return [
            'jobs'                     => $result['data'],
            'total'                    => $result['total'],
            'page'                     => $result['page'],
            'per_page'                 => $result['per_page'],
            'total_pages'              => $result['total_pages'],
            'filters'                  => $filters,
            'lokasi_list'              => $lokasiList,
        ];
    }

    public static function detailLowongan($conn, $id)
    {
        // 1. Ambil data utama lowongan beserta skill-nya dari Model
        $job = LowonganPekerjaan::getJobById($conn, $id);

        if (!$job) {
            return null;
        }

        // Master mapping untuk menerjemahkan key DB ke teks readable candidate
        $jenisDisabilitasOptions = [
            'visual'         => ['label' => 'Disabilitas Visual',        'desc' => 'Tunanetra, low vision'],
            'hearing'        => ['label' => 'Disabilitas Pendengaran',   'desc' => 'Tunarungu, hard of hearing'],
            'physical'       => ['label' => 'Disabilitas Fisik/Motorik', 'desc' => 'Keterbatasan gerak atau mobilitas'],
            'intellectual'   => ['label' => 'Disabilitas Intelektual',   'desc' => 'Tunagrahita dan sejenisnya'],
            'mental'         => ['label' => 'Disabilitas Mental',        'desc' => 'Gangguan jiwa/psikososial'],
            'speech'         => ['label' => 'Disabilitas Wicara',        'desc' => 'Tunawicara'],
        ];

        // 2. Ambil tipe disabilitas spesifik berupa array (Contoh isi: ['visual', 'physical'])
        $supportedDisabilities = LowonganPekerjaan::getDisabilityTypesByJobId($conn, $id);

        // 3. Gabungkan data ke array utama untuk dikirim ke View
        $job['supported_disabilities'] = $supportedDisabilities ?? [];
        $job['disability_options']     = $jenisDisabilitasOptions;

        return $job;
    }

    public static function getById($conn, $id) {
        $query = "SELECT * FROM job_posting WHERE id = ?"; 
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }
}