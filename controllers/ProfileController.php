<?php

require_once __DIR__ . "/../init.php";

class ProfileController
{
    public static function getCandidateProfile($user_id)
    {
        global $conn;

        $candidate = Candidate::findById($conn, $user_id);

        if (!$candidate) return null;

        $candidate_id = $candidate['id'];

        return [
            "candidate" => $candidate,
            "disabilities" => CandidateDisability::getByCandidateId($conn, $candidate_id),
            // "education" => Pendidikan::getByCandidate($conn, $candidate_id),
            // "experience" => Pengalaman::getByCandidate($conn, $candidate_id),
            // "skills" => Skill::getByCandidate($conn, $candidate_id),
            // "certifications" => Sertifikasi::getByCandidate($conn, $candidate_id),
        ];
    }
}
