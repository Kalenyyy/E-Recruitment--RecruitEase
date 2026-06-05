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
require_once __DIR__ . '/controllers/SkillController.php';
require_once __DIR__ . '/controllers/DivisiController.php'; // Tambahkan controller Divisi
require_once __DIR__ . '/controllers/PosisiController.php'; 
require_once __DIR__ . '/controllers/PengalamanKerjaController.php';

// 5. MODEL
require_once __DIR__ . '/models/User.php'; // Tambahkan model User
require_once __DIR__ . '/models/Candidate.php'; // Tambahkan model Candidate
require_once __DIR__ . '/models/Position.php'; // Tambahkan model Position
require_once __DIR__ . '/models/Staff.php'; // Tambahkan model Staff
require_once __DIR__ . '/models/CandidateDisability.php'; // Tambahkan model CandidateDisability
require_once __DIR__ . '/models/Divisi.php'; // Tambahkan model Divisi
require_once __DIR__ . '/models/Skill.php';

