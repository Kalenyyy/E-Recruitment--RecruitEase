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

        if (empty($postData['skill_ids'])) {
            $errors['skill_ids'] = "Minimal pilih satu skill yang dibutuhkan";
        }

        // Validasi Gaji
        if ($gmin > 999999999 || $gmax > 999999999) {
            $errors['gaji'] = "Gaji tidak boleh mencapai 1 Miliar";
        }

        if ($gmin > 0 && $gmax > 0) {
            if ($gmax < $gmin) {
                $errors['gaji'] = "Gaji maksimal harus lebih besar dari minimal";
            }
        }

        if (JobForm::isDuplicate($conn, $postData['judul_job'], $postData['posisi_id'])) {
            $errors['judul_job'] = "Lowongan dengan judul dan posisi ini sudah ada dan masih aktif!";
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

        // 1. Validasi Field Wajib
        if (empty($postData['judul_job'])) $errors['judul_job'] = "Judul wajib diisi";
        if (empty($postData['posisi_id'])) $errors['posisi_id'] = "Posisi wajib dipilih";

        // 2. Bersihkan format titik untuk validasi angka gaji
        $gmin = (int)str_replace('.', '', $postData['gaji_min'] ?? 0);
        $gmax = (int)str_replace('.', '', $postData['gaji_max'] ?? 0);

        // 3. Validasi Batas & Rentang Gaji
        if ($gmin > 999999999 || $gmax > 999999999) {
            $errors['gaji'] = "Gaji tidak boleh mencapai 1 Miliar";
        }

        if ($gmin > 0 && $gmax > 0) {
            if ($gmax < $gmin) {
                $errors['gaji'] = "Gaji maksimal harus lebih besar dari minimal";
            }
        }

        if (JobForm::isDuplicate($conn, $postData['judul_job'], $postData['posisi_id'], $id)) {
            $errors['judul_job'] = "Lowongan dengan judul dan posisi ini sudah ada dan masih aktif!";
        }

        // Jika ada error validasi, langsung kembalikan
        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        // Jika lolos validasi, panggil model update
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
