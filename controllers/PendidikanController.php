<?php

class PendidikanController
{
    public static function getByCandidateId($conn, $candidateId)
    {
        return Pendidikan::getByCandidateId(
            $conn,
            $candidateId
        );
    }

    public static function getById($conn, $id)
    {
        return Pendidikan::getById(
            $conn,
            $id
        );
    }

    public static function create($conn, $data)
    {
        $errors = [];
        $listTanpaJurusan = ['SD', 'SMP', 'SMA'];
        $listSekolah = ['SD', 'SMP', 'SMA', 'SMK'];

        // Validasi Field Wajib
        if (empty($data['institusi'])) $errors[] = "Nama Institusi wajib diisi.";
        if (empty($data['jenjang'])) $errors[] = "Jenjang pendidikan wajib dipilih.";
        if (empty($data['tahun_masuk'])) $errors[] = "Tahun masuk wajib diisi.";

        // Validasi Jurusan
        if (in_array($data['jenjang'], $listTanpaJurusan)) {
            $data['jurusan'] = null;
        } else {
            if (empty($data['jurusan'])) {
                $errors[] = "Jurusan wajib diisi.";
            }
        }

        // Validasi NILAI / IPK
        $nilai = $data['ipk'];
        if ($nilai !== '' && $nilai !== null) {
            if (in_array($data['jenjang'], $listSekolah)) {
                // Validasi skala 100
                if ($nilai < 0 || $nilai > 100) {
                    $errors[] = "Nilai harus antara 0 - 100.";
                }
            } else {
                // Validasi skala 4.00
                if ($nilai < 0 || $nilai > 4) {
                    $errors[] = "IPK harus antara 0.00 - 4.00.";
                }
            }
        }

        // Validasi Tahun (seperti yang dibahas sebelumnya)
        if (!empty($data['tahun_lulus']) && $data['tahun_lulus'] < $data['tahun_masuk']) {
            $errors[] = "Tahun lulus tidak boleh lebih kecil dari tahun masuk.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'messages' => $errors];
        }

        $result = Pendidikan::create($conn, $data);
        return $result ? ['success' => true] : ['success' => false, 'messages' => ['Gagal simpan database']];
    }


    public static function update($conn, $id, $data)
    {
        $errors = [];
        $listTanpaJurusan = ['SD', 'SMP', 'SMA'];
        $listSekolah = ['SD', 'SMP', 'SMA', 'SMK'];

        // Validasi Field Wajib
        if (empty($data['institusi'])) $errors[] = "Nama Institusi wajib diisi.";
        if (empty($data['jenjang'])) $errors[] = "Jenjang wajib dipilih.";
        if (empty($data['tahun_masuk'])) $errors[] = "Tahun masuk wajib diisi.";

        // Validasi Jurusan
        if (in_array($data['jenjang'], $listTanpaJurusan)) {
            $data['jurusan'] = null;
        } else {
            if (empty($data['jurusan'])) {
                $errors[] = "Jurusan wajib diisi untuk jenjang " . $data['jenjang'];
            }
        }

        // Validasi Tahun
        if (!empty($data['tahun_lulus']) && (int)$data['tahun_lulus'] < (int)$data['tahun_masuk']) {
            $errors[] = "Tahun lulus tidak boleh lebih kecil dari tahun masuk.";
        }

        // Validasi Nilai / IPK
        $nilai = $data['ipk'];
        if ($nilai !== '' && $nilai !== null) {
            if (in_array($data['jenjang'], $listSekolah)) {
                if ($nilai < 0 || $nilai > 100) $errors[] = "Nilai harus antara 0 - 100.";
            } else {
                if ($nilai < 0 || $nilai > 4) $errors[] = "IPK harus antara 0.00 - 4.00.";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'messages' => $errors];
        }

        $result = Pendidikan::update($conn, $id, $data);
        return $result ? ['success' => true] : ['success' => false, 'messages' => ['Gagal memperbarui database.']];
    }

    public static function delete($conn, $id)
    {
        return Pendidikan::delete(
            $conn,
            $id
        );
    }
}
