<?php
class JobFormController
{
    public static function getAllJobs($conn)
    {
        return JobForm::getAllJobs($conn);
    }

    public static function getTotalCount($conn, $search = '')
    {
        return JobForm::count($conn, $search);
    }

    public static function getPaginated($conn, $page, $perPage, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        return JobForm::readPaginated($conn, $offset, $perPage, $search);
    }

    public static function store($conn, $postData, $staff_id)
    {
        $errors = [];

        // Bersihkan format titik untuk validasi angka
        $gmin = (int)str_replace('.', '', $postData['gaji_min'] ?? 0);
        $gmax = (int)str_replace('.', '', $postData['gaji_max'] ?? 0);

        if (empty($postData['judul_job'])) $errors['judul_job'] = "Judul wajib diisi";

        // Validasi Gaji
        if ($gmin > 999999999 || $gmax > 999999999) {
            $errors['gaji'] = "Gaji tidak boleh mencapai 1 Miliar";
        }
        if ($gmin > 0 && $gmax > 0 && $gmax < $gmin) {
            $errors['gaji'] = "Gaji maksimal harus lebih besar dari minimal";
        }

        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        return JobForm::create($conn, $postData, $staff_id)
            ? ['status' => true]
            : ['status' => false, 'errors' => ['umum' => 'Terjadi kesalahan sistem.']];
    }

    public static function show($conn, $id)
    {
        return JobForm::find($conn, $id);
    }

    public static function update($conn, $id, $postData)
    {
        $errors = [];
        if (empty($postData['judul_job'])) $errors['judul_job'] = "Judul wajib diisi";
        if (empty($postData['posisi_id'])) $errors['posisi_id'] = "Posisi wajib dipilih";

        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        $result = JobForm::update($conn, $id, $postData);
        return $result ? ['status' => true] : ['status' => false, 'errors' => ['umum' => 'Gagal memperbarui data.']];
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
