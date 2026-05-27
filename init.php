<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BASE CONFIG
require_once __DIR__ . '/config/config.php';

// 3. Koneksi Database
require_once __DIR__ . '/config/koneksi.php';

// 4. CONTROLLER 
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/StaffController.php';

// 5. MODEL
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Position.php';
require_once __DIR__ . '/models/Staff.php';

