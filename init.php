<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BASE CONFIG
require_once __DIR__ . '/config/config.php';

// 3. Koneksi Database
require_once __DIR__ . '/config/koneksi.php';

// 4. CONTROLLER 
require_once __DIR__ . '/controllers/AuthController.php';  // Tambahkan controller Auth
require_once __DIR__ . '/controllers/CandidateController.php'; // Tambahkan controller Candidate
require_once __DIR__ . '/controllers/StaffController.php'; // Tambahkan controller Staff
require_once __DIR__ . '/controllers/ProfileController.php'; // Tambahkan controller Profile
require_once __DIR__ . '/controllers/DisabilityController.php'; // Tambahkan controller Disability
require_once __DIR__ . '/controllers/DivisiController.php'; // Tambahkan controller Divisi
require_once __DIR__ . '/controllers/PosisiController.php'; 
require_once __DIR__ . '/controllers/PengalamanKerjaController.php';

// 5. MODEL
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Candidate.php';
require_once __DIR__ . '/models/Position.php';
require_once __DIR__ . '/models/Staff.php';
require_once __DIR__ . '/models/CandidateDisability.php';
require_once __DIR__ . '/models/PengalamanKerja.php';
require_once __DIR__ . '/models/Divisi.php'; // Tambahkan model Divisi
require_once __DIR__ . '/models/Posisi.php'; // Tambahkan model Posisi

