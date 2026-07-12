<?php

require_once __DIR__ . "/../init.php";

class User
{
    public static function findByUsername($username)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function findByEmail($email)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function insert($conn, $username, $email, $password, $role)
    {
        $sql = "INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        $stmt->execute();

        return $stmt->insert_id;
    }
}
