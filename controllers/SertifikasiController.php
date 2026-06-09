<?php

require_once __DIR__ . "/../init.php";

class SertifikasiController
{
    public static function store(
        $conn,
        $data,
        $files
    ) {

        $errors = [];

        $candidate_id = trim($data['id_candidate'] ?? '');
        $nama_sertifikasi = trim($data['nama_sertifikasi'] ?? '');
        $penyelenggara = trim($data['penyelenggara'] ?? '');
        $tanggal_terbit = trim($data['tanggal_terbit'] ?? '');

        $file_sertifikasi = null;

        // =====================
        // VALIDASI CANDIDATE
        // =====================
        if ($candidate_id === '') {
            $errors['id_candidate'] =
                "Candidate wajib diisi";
        } elseif (!ctype_digit($candidate_id)) {
            $errors['id_candidate'] =
                "Candidate tidak valid";
        }

        // =====================
        // VALIDASI NAMA
        // =====================
        if ($nama_sertifikasi === '') {
            $errors['nama_sertifikasi'] =
                "Nama sertifikasi wajib diisi";
        }

        // =====================
        // VALIDASI PENYELENGGARA
        // =====================
        if ($penyelenggara === '') {
            $errors['penyelenggara'] =
                "Penyelenggara wajib diisi";
        }

        // =====================
        // VALIDASI TANGGAL
        // =====================
        if ($tanggal_terbit === '') {
            $errors['tanggal_terbit'] =
                "Tanggal terbit wajib diisi";
        } elseif (!strtotime($tanggal_terbit)) {
            $errors['tanggal_terbit'] =
                "Format tanggal tidak valid";
        }

        // =====================
        // VALIDASI FILE
        // =====================
        if (
            isset($files['file_sertifikasi']) &&
            $files['file_sertifikasi']['error'] === 0
        ) {

            $allowed = [
                'pdf',
                'jpg',
                'jpeg',
                'png'
            ];

            $extension = strtolower(
                pathinfo(
                    $files['file_sertifikasi']['name'],
                    PATHINFO_EXTENSION
                )
            );

            if (!in_array($extension, $allowed)) {
                $errors['file_sertifikasi'] =
                    "File harus PDF/JPG/JPEG/PNG";
            }
        }

        if (!empty($errors)) {
            return [
                'status' => false,
                'errors' => $errors
            ];
        }

        // =====================
        // UPLOAD FILE
        // =====================
        if (
            isset($files['file_sertifikasi']) &&
            $files['file_sertifikasi']['error'] === 0
        ) {

            $uploadDir =
                __DIR__ .
                '/../uploads/sertifikasi/';

            if (!is_dir($uploadDir)) {
                mkdir(
                    $uploadDir,
                    0777,
                    true
                );
            }

            $fileName =
                time() .
                '_' .
                basename(
                    $files['file_sertifikasi']['name']
                );

            move_uploaded_file(
                $files['file_sertifikasi']['tmp_name'],
                $uploadDir . $fileName
            );

            $file_sertifikasi = $fileName;
        }

        // =====================
        // INSERT DATABASE
        // =====================
        $insert = Sertifikasi::insert(
            $conn,
            (int) $candidate_id,
            $nama_sertifikasi,
            $penyelenggara,
            $tanggal_terbit,
            $file_sertifikasi
        );

        if (!$insert) {
            return [
                'status' => false,
                'errors' => [
                    'umum' =>
                        'Gagal menyimpan data ke database'
                ]
            ];
        }

        return [
            'status' => true
        ];
    }

    public static function update(
        $conn,
        $data,
        $files
    ) {

        $errors = [];

        $id = trim($data['id'] ?? '');
        $nama_sertifikasi = trim($data['nama_sertifikasi'] ?? '');
        $penyelenggara = trim($data['penyelenggara'] ?? '');
        $tanggal_terbit = trim($data['tanggal_terbit'] ?? '');

        if ($id === '' || !ctype_digit($id)) {
            $errors['umum'] = "ID tidak valid";
        }

        if ($nama_sertifikasi === '') {
            $errors['nama_sertifikasi'] =
                "Nama sertifikasi wajib diisi";
        }

        if ($penyelenggara === '') {
            $errors['penyelenggara'] =
                "Penyelenggara wajib diisi";
        }

        if ($tanggal_terbit === '') {
            $errors['tanggal_terbit'] =
                "Tanggal terbit wajib diisi";
        }

        if (!empty($errors)) {
            return [
                'status' => false,
                'errors' => $errors
            ];
        }

        $sertifikasi =
            Sertifikasi::findById(
                $conn,
                (int) $id
            );

        $file_sertifikasi =
            $sertifikasi['file_sertifikasi'];

        if (
            isset($files['file_sertifikasi']) &&
            $files['file_sertifikasi']['error'] === 0
        ) {

            $uploadDir =
                __DIR__ .
                '/../uploads/sertifikasi/';

            $fileName =
                time() .
                '_' .
                basename(
                    $files['file_sertifikasi']['name']
                );

            move_uploaded_file(
                $files['file_sertifikasi']['tmp_name'],
                $uploadDir . $fileName
            );

            $file_sertifikasi = $fileName;
        }

        $update = Sertifikasi::update(
            $conn,
            (int) $id,
            $nama_sertifikasi,
            $penyelenggara,
            $tanggal_terbit,
            $file_sertifikasi
        );

        if (!$update) {
            return [
                'status' => false,
                'errors' => [
                    'umum' =>
                        'Gagal memperbarui data'
                ]
            ];
        }

        return [
            'status' => true
        ];
    }

    public static function getAllSertifikasi($conn)
    {
        return Sertifikasi::getAll($conn);
    }


    public static function getByCandidateId(
    $conn,
    $candidate_id
) {

    return Sertifikasi::getByCandidateId(
        $conn,
        $candidate_id
    );
}

public static function findById(
    $conn,
    $id_sertifikasi
) {
    return Sertifikasi::findById(
        $conn,
        $id_sertifikasi
    );
}

public static function delete(
    $conn,
    $id_sertifikasi
) {

    $sertifikasi =
        Sertifikasi::findById(
            $conn,
            $id_sertifikasi
        );

    if (!$sertifikasi) {
        return false;
    }

    if (
        !empty(
            $sertifikasi['file_sertifikasi']
        )
    ) {

        $file =
            __DIR__ .
            '/../uploads/sertifikasi/' .
            $sertifikasi['file_sertifikasi'];

        if (file_exists($file)) {
            unlink($file);
        }
    }

    return Sertifikasi::delete(
        $conn,
        $id_sertifikasi
    );
}
}