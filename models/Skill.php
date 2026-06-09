<?php

require_once __DIR__ . "/../init.php";

class Skill
{
    public static function getAll($conn)
    {
        return mysqli_query(
            $conn,
            "SELECT * FROM skills ORDER BY nama_skill ASC"
        );
    }

    public static function findById($conn, $id)
    {
        $stmt = $conn->prepare(
            "SELECT * FROM skills WHERE id_skill = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($conn, $nama_skill)
    {
        $stmt = $conn->prepare(
            "INSERT INTO skills (nama_skill)
             VALUES (?)"
        );

        $stmt->bind_param("s", $nama_skill);

        return $stmt->execute();
    }

    public static function update($conn, $id, $nama_skill)
    {
        $stmt = $conn->prepare(
            "UPDATE skills
             SET nama_skill = ?
             WHERE id_skill = ?"
        );

        $stmt->bind_param(
            "si",
            $nama_skill,
            $id
        );

        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $stmt = $conn->prepare(
            "DELETE FROM skills
             WHERE id_skill = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}