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
require_once __DIR__ . '/controllers/CandidateController.php';
require_once __DIR__ . '/controllers/StaffController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/DisabilityController.php';
require_once __DIR__ . '/controllers/PengalamanKerjaController.php';

// 5. MODEL
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Candidate.php';
require_once __DIR__ . '/models/Position.php';
require_once __DIR__ . '/models/Staff.php';
require_once __DIR__ . '/models/CandidateDisability.php';
require_once __DIR__ . '/models/PengalamanKerja.php';

