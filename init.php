<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BASE CONFIG
require_once __DIR__ . '/config/config.php';

// 3. Koneksi Database
require_once __DIR__ . '/config/koneksi.php';

require_once __DIR__ . '/vendor/autoload.php';

// 4. CONTROLLER 
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CandidateController.php';
require_once __DIR__ . '/controllers/CandidateSkillController.php';
require_once __DIR__ . '/controllers/StaffController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/DisabilityController.php';
require_once __DIR__ . '/controllers/SkillController.php';
require_once __DIR__ . '/controllers/DivisiController.php'; 
require_once __DIR__ . '/controllers/PosisiController.php'; 
require_once __DIR__ . '/controllers/PengalamanKerjaController.php';
require_once __DIR__ . '/controllers/SertifikasiController.php';
require_once __DIR__ . '/controllers/PendidikanController.php'; // Tambahkan controller Pendidikan
require_once __DIR__ . '/controllers/JobFormController.php'; 
require_once __DIR__ . '/controllers/LowonganPekerjaanController.php'; 
require_once __DIR__ . '/controllers/LamaranController.php'; 
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/PelamarPekerjaanController.php';
require_once __DIR__ . '/controllers/LaporanController.php';

// 5. MODEL
require_once __DIR__ . '/models/User.php'; 
require_once __DIR__ . '/models/Candidate.php'; 
require_once __DIR__ . '/models/Posisi.php'; 
require_once __DIR__ . '/models/Staff.php'; 
require_once __DIR__ . '/models/CandidateDisability.php'; 
require_once __DIR__ . '/models/CandidateSkill.php'; 
require_once __DIR__ . '/models/Divisi.php'; 
require_once __DIR__ . '/models/Skill.php';
require_once __DIR__ . '/models/PengalamanKerja.php';
require_once __DIR__ . '/models/Sertifikasi.php';
require_once __DIR__ . '/models/Pendidikan.php'; // Tambahkan model Pendidikan
require_once __DIR__ . '/models/JobForm.php';
require_once __DIR__ . '/models/LowonganPekerjaan.php';
require_once __DIR__ . '/models/Lamaran.php';
require_once __DIR__ . '/models/Dashboard.php';
require_once __DIR__ . '/models/Laporan.php';

//helper
require_once __DIR__ . '/views/helpers/ProfileHelper.php';

