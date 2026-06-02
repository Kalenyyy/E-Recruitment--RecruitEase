<?php

class DisabilityController
{
    public static function update($candidate_id, $is_disabled, $description, $types)
    {
        global $conn;

        // update flag di candidates
        $stmt = $conn->prepare("
            UPDATE candidates 
            SET is_disabled = ?, disability_description = ?
            WHERE id = ?
        ");

        $stmt->bind_param("isi", $is_disabled, $description, $candidate_id);
        $stmt->execute();

        // reset relasi
        CandidateDisability::deleteByCandidate($conn, $candidate_id);

        // insert ulang
        foreach ($types as $type) {
            CandidateDisability::insert($conn, $candidate_id, $type);
        }

        return true;
    }
}
