<?php
class JobFormController
{


    public static function getAllJobs($conn)
    {
        return JobForm::getAllJobs($conn);
    }

    public static function store($conn, $postData, $staff_id)
    {
        $errors = [];

        // Validasi sederhana
        if (empty($postData['judul_job'])) $errors['judul_job'] = "Judul wajib diisi";
        if (empty($postData['posisi_id'])) $errors['posisi_id'] = "Posisi wajib dipilih";

        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        // Panggil model untuk menyimpan (Model akan menangani 3 tabel sekaligus)
        $result = JobForm::create($conn, $postData, $staff_id);

        if ($result) {
            return ['status' => true];
        } else {
            return ['status' => false, 'errors' => ['umum' => 'Terjadi kesalahan sistem saat menyimpan data.']];
        }
    }

    public static function show($conn, $id)
    {
        return JobForm::find($conn, $id);
    }

    public static function updateStatus($conn, $id, $status)
    {
        return JobForm::updateStatus($conn, $id, $status);
    }

    public static function delete($conn, $id)
    {
        return JobForm::delete($conn, $id);
    }
}
