<?php

require_once __DIR__ . "/../init.php";

class SertifikasiController
{
    public static function store($conn, $data, $files)
    {
        $errors = [];

        $candidate_id = trim($data['id_candidate'] ?? '');
        $nama_sertifikasi = trim($data['nama_sertifikasi'] ?? '');
        $penyelenggara = trim($data['penyelenggara'] ?? '');
        $tanggal_terbit = trim($data['tanggal_terbit'] ?? '');

        $file_sertifikasi = null;

        // Validasi Input Dasar
        if ($candidate_id === '' || !ctype_digit($candidate_id)) $errors['id_candidate'] = "Kandidat tidak valid";
        if ($nama_sertifikasi === '') $errors['nama_sertifikasi'] = "Nama sertifikasi wajib diisi";
        if ($penyelenggara === '') $errors['penyelenggara'] = "Penyelenggara wajib diisi";
        if ($tanggal_terbit === '' || !strtotime($tanggal_terbit)) $errors['tanggal_terbit'] = "Tanggal terbit tidak valid";

        // =====================
        // VALIDASI FILE (Store)
        // =====================
        if (isset($files['file_sertifikasi']) && $files['file_sertifikasi']['error'] === 0) {
            $file = $files['file_sertifikasi'];
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
            $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Cek Ekstensi & MIME
            if (!in_array($extension, $allowedExt) || !in_array($file['type'], $allowedMime)) {
                $errors['file_sertifikasi'] = "Format harus PDF, JPG, atau PNG.";
            }
            // Cek Size (2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                $errors['file_sertifikasi'] = "Ukuran file maksimal 2MB.";
            }
        }

        if (!empty($errors)) return ['status' => false, 'errors' => $errors];

        // =====================
        // PROSES UPLOAD
        // =====================
        if (isset($files['file_sertifikasi']) && $files['file_sertifikasi']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/sertifikasi/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Sanitasi Nama File
            $cleanName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($files['file_sertifikasi']['name']));
            $fileName = time() . '_' . $cleanName;

            if (move_uploaded_file($files['file_sertifikasi']['tmp_name'], $uploadDir . $fileName)) {
                $file_sertifikasi = $fileName;
            }
        }

        $insert = Sertifikasi::insert($conn, (int)$candidate_id, $nama_sertifikasi, $penyelenggara, $tanggal_terbit, $file_sertifikasi);

        return $insert ? ['status' => true] : ['status' => false, 'errors' => ['umum' => 'Gagal simpan ke database']];
    }

    public static function update($conn, $data, $files)
    {
        $errors = [];
        $id = trim($data['id'] ?? '');
        $nama_sertifikasi = trim($data['nama_sertifikasi'] ?? '');
        $penyelenggara = trim($data['penyelenggara'] ?? '');
        $tanggal_terbit = trim($data['tanggal_terbit'] ?? '');

        if ($id === '' || !ctype_digit($id)) $errors['umum'] = "ID tidak valid";
        if ($nama_sertifikasi === '') $errors['nama_sertifikasi'] = "Nama sertifikasi wajib diisi";
        if ($penyelenggara === '') $errors['penyelenggara'] = "Penyelenggara wajib diisi";
        if ($tanggal_terbit === '') $errors['tanggal_terbit'] = "Tanggal terbit wajib diisi";

        // =====================
        // VALIDASI FILE (Update)
        // =====================
        if (isset($files['file_sertifikasi']) && $files['file_sertifikasi']['error'] === 0) {
            $file = $files['file_sertifikasi'];
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
            $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExt) || !in_array($file['type'], $allowedMime)) {
                $errors['file_sertifikasi'] = "Format harus PDF, JPG, atau PNG.";
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                $errors['file_sertifikasi'] = "Ukuran file maksimal 2MB.";
            }
        }

        if (!empty($errors)) return ['status' => false, 'errors' => $errors];

        // Ambil data lama
        $sertifikasi = Sertifikasi::findById($conn, $id);
        $file_sertifikasi = $sertifikasi['file_sertifikasi'];

        if (isset($files['file_sertifikasi']) && $files['file_sertifikasi']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/sertifikasi/';

            $cleanName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($files['file_sertifikasi']['name']));
            $fileName = time() . '_' . $cleanName;

            if (move_uploaded_file($files['file_sertifikasi']['tmp_name'], $uploadDir . $fileName)) {
                // HAPUS FILE LAMA jika upload berhasil
                if (!empty($sertifikasi['file_sertifikasi'])) {
                    $oldFile = $uploadDir . $sertifikasi['file_sertifikasi'];
                    if (file_exists($oldFile)) unlink($oldFile);
                }
                $file_sertifikasi = $fileName;
            }
        }

        $update = Sertifikasi::update($conn, (int)$id, $nama_sertifikasi, $penyelenggara, $tanggal_terbit, $file_sertifikasi);

        return $update ? ['status' => true] : ['status' => false, 'errors' => ['umum' => 'Gagal memperbarui data']];
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
            !empty($sertifikasi['file_sertifikasi'])
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
