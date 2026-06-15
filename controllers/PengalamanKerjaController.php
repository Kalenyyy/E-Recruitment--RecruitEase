<?php

require_once __DIR__ . "/../init.php";

class PengalamanKerjaController
{
    
    public static function store($conn, $data)
    {
        $errors = [];

        // =========================
        // INPUT SANITIZATION
        // =========================
        $candidate_id    = trim($data['id_candidate'] ?? '');
        $nama_perusahaan = trim($data['nama_perusahaan'] ?? '');
        $posisi          = trim($data['posisi'] ?? '');
        $tanggal_mulai   = trim($data['tanggal_mulai'] ?? '');
        $tanggal_selesai = trim($data['tanggal_selesai'] ?? '');
        $deskripsi       = trim($data['deskripsi_pekerjaan'] ?? '');

        // =========================
        // VALIDASI CANDIDATE
        // =========================
        if ($candidate_id === '') {
            $errors['id_candidate'] = "Candidate wajib diisi";
        } elseif (!ctype_digit($candidate_id)) {
            $errors['id_candidate'] = "Candidate tidak valid";
        }

        // =========================
        // VALIDASI PERUSAHAAN
        // =========================
        if ($nama_perusahaan === '') {
            $errors['nama_perusahaan'] = "Nama perusahaan wajib diisi";
        } elseif (strlen($nama_perusahaan) < 3) {
            $errors['nama_perusahaan'] = "Nama perusahaan terlalu pendek";
        } elseif (strlen($nama_perusahaan) > 255) {
            $errors['nama_perusahaan'] = "Nama perusahaan terlalu panjang";
        }

        // =========================
        // VALIDASI POSISI
        // =========================
        if ($posisi === '') {
            $errors['posisi'] = "Posisi wajib diisi";
        } elseif (strlen($posisi) < 2) {
            $errors['posisi'] = "Posisi terlalu pendek";
        } elseif (strlen($posisi) > 255) {
            $errors['posisi'] = "Posisi terlalu panjang";
        }

        // =========================
        // VALIDASI TANGGAL MULAI
        // =========================
        if ($tanggal_mulai === '') {
            $errors['tanggal_mulai'] = "Tanggal mulai wajib diisi";
        } elseif (!strtotime($tanggal_mulai)) {
            $errors['tanggal_mulai'] = "Format tanggal mulai tidak valid";
        }

        // =========================
        // VALIDASI TANGGAL SELESAI
        // =========================
        if ($tanggal_selesai !== '' && !strtotime($tanggal_selesai)) {
            $errors['tanggal_selesai'] = "Format tanggal selesai tidak valid";
        }

        // LOGIC CHECK
        if (
            $tanggal_mulai &&
            $tanggal_selesai &&
            strtotime($tanggal_selesai) < strtotime($tanggal_mulai)
        ) {
            $errors['tanggal_selesai'] =
                "Tanggal selesai tidak boleh lebih kecil dari tanggal mulai";
        }

        // =========================
        // VALIDASI DESKRIPSI
        // =========================
        if ($deskripsi !== '' && strlen($deskripsi) > 2000) {
            $errors['deskripsi_pekerjaan'] =
                "Deskripsi maksimal 2000 karakter";
        }

        // =========================
        // RETURN ERROR
        // =========================
        if (!empty($errors)) {
            return [
                'status' => false,
                'errors' => $errors
            ];
        }

        // =========================
        // INSERT DB
        // =========================
        $insert = PengalamanKerja::insert(
            $conn,
            (int)$candidate_id,
            $nama_perusahaan,
            $posisi,
            $tanggal_mulai,
            $tanggal_selesai ?: null,
            $deskripsi
        );

        if (!$insert) {
            return [
                'status' => false,
                'errors' => [
                    'umum' => 'Gagal menyimpan data ke database'
                ]
            ];
        }

        return [
            'status' => true
        ];
    }

    public static function update($conn, $data)
{
    $errors = [];

    $id               = trim($data['id'] ?? '');
    $candidate_id     = trim($data['id_candidate'] ?? '');
    $nama_perusahaan  = trim($data['nama_perusahaan'] ?? '');
    $posisi           = trim($data['posisi'] ?? '');
    $tanggal_mulai    = trim($data['tanggal_mulai'] ?? '');
    $tanggal_selesai  = trim($data['tanggal_selesai'] ?? '');
    $deskripsi        = trim($data['deskripsi_pekerjaan'] ?? '');

    // =====================
    // VALIDASI ID
    // =====================
    if ($id === '' || !ctype_digit($id)) {
        $errors['umum'] = "ID pengalaman kerja tidak valid";
    }

    // =====================
    // VALIDASI CANDIDATE
    // =====================
    if ($candidate_id === '') {
        $errors['id_candidate'] = "Candidate wajib diisi";
    }

    // =====================
    // VALIDASI PERUSAHAAN
    // =====================
    if ($nama_perusahaan === '') {
        $errors['nama_perusahaan'] = "Nama perusahaan wajib diisi";
    }

    // =====================
    // VALIDASI POSISI
    // =====================
    if ($posisi === '') {
        $errors['posisi'] = "Posisi wajib diisi";
    }

    // =====================
    // VALIDASI TANGGAL
    // =====================
    if ($tanggal_mulai === '') {
        $errors['tanggal_mulai'] = "Tanggal mulai wajib diisi";
    }

    if (
        $tanggal_mulai &&
        $tanggal_selesai &&
        strtotime($tanggal_selesai) < strtotime($tanggal_mulai)
    ) {
        $errors['tanggal_selesai'] =
            "Tanggal selesai tidak boleh lebih kecil dari tanggal mulai";
    }

    if (!empty($errors)) {
        return [
            'status' => false,
            'errors' => $errors
        ];
    }

    // =====================
    // UPDATE DATABASE
    // =====================
    $update = PengalamanKerja::update(
        $conn,
        (int)$id,
        $nama_perusahaan,
        $posisi,
        $tanggal_mulai,
        $tanggal_selesai ?: null,
        $deskripsi
    );

    if (!$update) {
        return [
            'status' => false,
            'errors' => [
                'umum' => 'Gagal memperbarui data'
            ]
        ];
    }

    return [
        'status' => true
    ];
}
}