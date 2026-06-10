<?php
class LowonganPekerjaanController
{
    public static function jelajahiLowongan($conn)
    {
        // Daftar jenis disabilitas hardcoded
        $jenisDisabilitasOptions = [
            'visual'         => ['label' => 'Disabilitas Visual',        'desc' => 'Tunanetra, low vision'],
            'hearing'        => ['label' => 'Disabilitas Pendengaran',   'desc' => 'Tunarungu, hard of hearing'],
            'physical'       => ['label' => 'Disabilitas Fisik/Motorik', 'desc' => 'Keterbatasan gerak atau mobilitas'],
            'intellectual'   => ['label' => 'Disabilitas Intelektual',   'desc' => 'Tunagrahita dan sejenisnya'],
            'mental'         => ['label' => 'Disabilitas Mental',        'desc' => 'Gangguan jiwa/psikososial'],
            'speech'         => ['label' => 'Disabilitas Wicara',        'desc' => 'Tunawicara'],
        ];

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
        $perPage = 9;

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
            'jenis_disabilitas_options' => $jenisDisabilitasOptions,
        ];
    }
}
