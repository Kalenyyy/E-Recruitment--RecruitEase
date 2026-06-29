<?php

class ProfileHelper
{
    public static function isComplete($conn, int $candidateId): bool
    {
        $candidate = Candidate::findById($conn, $candidateId);

        if (!$candidate) {
            return false;
        }

        $pendidikan = Pendidikan::getByCandidateId($conn, $candidateId);
        $skill = CandidateSkill::getByCandidateId($conn, $candidateId);

        // FIX: tetap pakai struktur kamu, cuma dibenerin aman
        $requiredFields = [
            'nama_lengkap',
            'email',
            'no_hp',
            'alamat'
    ];

        foreach ($requiredFields as $field) {
            if (empty($candidate[$field])) {
                return false;
            }
        }

        if (
            // empty($candidate['foto']) || 
            empty($candidate['cv_file'])) {
            return false;
        }

        if (count($pendidikan) < 1) {
            return false;
        }

        if (count($skill) < 1) {
            return false;
        }

        return true;
    }

    public static function getProfileStatus($conn, int $candidateId): array
    {
        $candidate = Candidate::findById($conn, $candidateId);

        if (!$candidate) {
            return [
                'complete' => false,
                'reason' => 'Candidate not found'
            ];
        }

        return [
            'complete' => self::isComplete($conn, $candidateId),
            'has_pendidikan' => count(Pendidikan::getByCandidateId($conn, $candidateId)) > 0,
            'has_skill' => count(CandidateSkill::getByCandidateId($conn, $candidateId)) > 0,
        ];
    }

    public static function getMissingFields($conn, int $candidateId): array
{
    $candidate = Candidate::findById($conn, $candidateId);

    if (!$candidate) {
        return ['profile' => ['Candidate tidak ditemukan']];
    }

    $missing = [];

    // FIELD WAJIB (sesuai sistem kamu sekarang)
    $fields = [
        'nama_lengkap' => 'Nama lengkap',
        'email' => 'Email',
        'no_hp' => 'Nomor HP',
        'alamat' => 'Alamat',
        // 'foto' => 'Foto profil',
        'cv_file' => 'CV'
    ];

    foreach ($fields as $key => $label) {
        if (empty($candidate[$key])) {
            $missing['profile'][] = $label;
        }
    }

    // pendidikan
    $pendidikan = Pendidikan::getByCandidateId($conn, $candidateId);
    if (count($pendidikan) < 1) {
        $missing['pendidikan'][] = 'Minimal 1 data pendidikan';
    }

    // skill
    $skill = CandidateSkill::getByCandidateId($conn, $candidateId);
    if (count($skill) < 1) {
        $missing['skill'][] = 'Minimal 1 skill';
    }

    return $missing;
}
}