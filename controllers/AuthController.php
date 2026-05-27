<?php
require_once __DIR__ . "/../init.php";

class AuthController
{
    public static function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "views/login.php");
            exit;
        }
    }

    public static function isHRD()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'hr';
    }

    public static function isCandidate()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'candidate';
    }

    public static function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function login($username, $password)
{
    global $conn; 
    $user = User::findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        
        if ($user['role'] === 'hr') {
            $staff = Staff::findByUserId($conn, $user['id']);
            
            if ($staff && $staff['status'] === 'inactive') {
                return false;
            }
        }

        return $user;
    }

    return false;
}

    // public static function register($full_name, $email, $username, $phone, $password)
    // {
    //     if (User::findByUsername($username)) {
    //         return 'username_taken';
    //     }

    //     if (User::findByEmail($email)) {
    //         return 'email_taken';
    //     }

    //     $hashed = password_hash($password, PASSWORD_DEFAULT);

    //     return User::create(
    //         $full_name,
    //         $email,
    //         $username,
    //         $phone,
    //         $hashed
    //     );
    // }
}
