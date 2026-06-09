<?php

class CandidateSkillController
{
    public static function store($conn, $data)
    {
        $errors = [];

        $candidateId = $data['candidate_id'] ?? null;
        $skills      = $data['skills'] ?? [];

        if (!$candidateId) {
            $errors['umum'] = 'Candidate tidak ditemukan';
        }

        if (empty($skills)) {
            $errors['skills'] = 'Minimal pilih 1 skill';
        }

        if (!empty($errors)) {
            return [
                'status' => false,
                'errors' => $errors
            ];
        }

        foreach ($skills as $skillId) {

            CandidateSkill::create(
                $conn,
                $candidateId,
                $skillId
            );
        }

        return [
            'status' => true
        ];
    }

    public static function delete(
        $conn,
        $id
    ) {
        return CandidateSkill::delete(
            $conn,
            $id
        );
    }
}
