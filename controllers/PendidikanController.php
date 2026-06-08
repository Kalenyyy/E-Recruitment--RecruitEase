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
        $tanpaJurusan = ['SD', 'SMP', 'SMA'];
        if (
            empty($data['candidate_id']) ||
            empty($data['institusi']) ||
            empty($data['jenjang']) ||
            empty($data['tahun_masuk'])
        ) {
            die("Data wajib belum lengkap");
        }

        /*
    |--------------------------------------------------------------------------
    | Jurusan
    |--------------------------------------------------------------------------
    */
        if (in_array($data['jenjang'], $tanpaJurusan)) {

            $data['jurusan'] = null;
        } else {

            if (empty($data['jurusan'])) {
                die("Jurusan wajib diisi");
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Tahun
    |--------------------------------------------------------------------------
    */
        if (
            !empty($data['tahun_lulus']) &&
            $data['tahun_lulus'] < $data['tahun_masuk']
        ) {
            die("Tahun lulus tidak boleh lebih kecil dari tahun masuk");
        }

        /*
    |--------------------------------------------------------------------------
    | Nilai / IPK
    |--------------------------------------------------------------------------
    */
        $nilai = $data['ipk'];

        if ($nilai !== '' && $nilai !== null) {

            if (in_array($data['jenjang'], $tanpaJurusan)) {

                if ($nilai < 0 || $nilai > 100) {
                    die("Nilai harus antara 0 - 100");
                }
            } else {

                if ($nilai < 0 || $nilai > 4) {
                    die("IPK harus antara 0.00 - 4.00");
                }
            }
        }

        return Pendidikan::create(
            $conn,
            $data
        );
    }

    public static function update($conn, $id, $data)
    {
        $tanpaJurusan = ['SD', 'SMP', 'SMA'];

        if (in_array($data['jenjang'], $tanpaJurusan)) {

            $data['jurusan'] = null;
        } else {

            if (empty($data['jurusan'])) {
                die("Jurusan wajib diisi");
            }
        }

        if (
            !empty($data['tahun_lulus']) &&
            $data['tahun_lulus'] < $data['tahun_masuk']
        ) {
            die("Tahun lulus tidak boleh lebih kecil dari tahun masuk");
        }

        $nilai = $data['ipk'];

        if ($nilai !== '' && $nilai !== null) {

            if (in_array($data['jenjang'], $tanpaJurusan)) {

                if ($nilai < 0 || $nilai > 100) {
                    die("Nilai harus antara 0 - 100");
                }
            } else {

                if ($nilai < 0 || $nilai > 4) {
                    die("IPK harus antara 0.00 - 4.00");
                }
            }
        }

        return Pendidikan::update(
            $conn,
            $id,
            $data
        );
    }

    public static function delete($conn, $id)
    {
        return Pendidikan::delete(
            $conn,
            $id
        );
    }
}
