<?php

class CandidateDisability
{
    public static function getByCandidateId($conn, $candidate_id)
    {
        $stmt = $conn->prepare("
            SELECT disability_type 
            FROM candidate_disabilities 
            WHERE candidate_id = ?
        ");

        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['disability_type'];
        }

        return $data;
    }

    public static function deleteByCandidate($conn, $candidate_id)
    {
        $stmt = $conn->prepare("DELETE FROM candidate_disabilities WHERE candidate_id = ?");
        $stmt->bind_param("i", $candidate_id);
        return $stmt->execute();
    }

    public static function insert($conn, $candidate_id, $type)
    {
        $stmt = $conn->prepare("
            INSERT INTO candidate_disabilities (candidate_id, disability_type)
            VALUES (?, ?)
        ");

        $stmt->bind_param("is", $candidate_id, $type);
        return $stmt->execute();
    }
}
