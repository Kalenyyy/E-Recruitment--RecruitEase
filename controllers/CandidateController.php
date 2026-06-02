<?php

require_once __DIR__ . "/../init.php";

class CandidateController
{
    public static function getCandidateByUserId($user_id)
    {
        global $conn;
        return Candidate::findByUserId($conn, $user_id);
    }

    public static function getCandidateById($id)
    {
        global $conn;
        return Candidate::findById($conn, $id);
    }

    public static function updateCandidate($id, $data)
    {
        global $conn;
        return Candidate::update($conn, $id, $data);
    }

    public static function deleteCandidate($id)
    {
        global $conn;
        return Candidate::delete($conn, $id);
    }

    public static function getAllCandidates()
    {
        global $conn;
        return Candidate::getAll($conn);
    }

    public static function updateProfile($id, $data)
    {
        global $conn;
        return Candidate::updateProfile($conn, $id, $data);
    }
}
