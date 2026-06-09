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

        public static function register($full_name, $email, $username, $phone, $password)
        {
            global $conn;

            // Cek apakah username sudah terdaftar
            if (User::findByUsername($username)) {
                return 'username_taken';
            }

            // Cek apakah email sudah terdaftar
            if (User::findByEmail($email)) {
                return 'email_taken';
            }

            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke tabel users dengan role 'candidate'
            $user_id = User::insert($conn, $username, $email, $hashed, 'candidate');

            if (!$user_id) {
                return false;
            }

            // Insert ke tabel candidates
            $candidate_id = Candidate::insert($conn, $user_id, $full_name, $email, $phone);

            if (!$candidate_id) {
                // Jika insert candidate gagal, delete user yang baru dibuat
                User::delete($conn, $user_id);
                return false;
            }

            return true;
        }
    }
