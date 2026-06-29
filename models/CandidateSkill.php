<?php

class CandidateSkill
{
    public static function create(
        $conn,
        $candidateId,
        $skillId
    ) {

        $stmt = $conn->prepare("
            INSERT INTO candidate_skills
            (
                candidate_id,
                skill_id
            )
            VALUES (?,?)
        ");

        $stmt->bind_param(
            "ii",
            $candidateId,
            $skillId
        );

        return $stmt->execute();
    }

    public static function searchSkills(
        $conn,
        $keyword
    ) {

        $stmt = $conn->prepare("
            SELECT
                id_skill,
                nama_skill
            FROM skills
            WHERE nama_skill LIKE ?
            ORDER BY nama_skill ASC
            LIMIT 10
        ");

        $search = "%{$keyword}%";

        $stmt->bind_param("s", $search);

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }

    public static function getPopularSkills($conn, $limit = 15)
    {
        $stmt = $conn->prepare("
        SELECT id_skill, nama_skill 
        FROM skills 
        ORDER BY nama_skill ASC 
        LIMIT ?
    ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByCandidateId($conn, $candidateId)
    {
        $stmt = $conn->prepare("
        SELECT
            cs.id,
            cs.skill_id,
            s.nama_skill
        FROM candidate_skills cs
        JOIN skills s ON s.id_skill = cs.skill_id
        WHERE cs.candidate_id = ?
        ORDER BY s.nama_skill ASC
    ");

        $stmt->bind_param("i", $candidateId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function delete(
        $conn,
        $id
    ) {
        $stmt = $conn->prepare("
        DELETE FROM candidate_skills
        WHERE id = ?
    ");

        $stmt->bind_param(
            "i",
            $id
        );

        return $stmt->execute();
    }
}
