<?php

$host = "localhost";
$user = "root";
$pass = "";

/* =========================
   1. KONEKSI MYSQL
========================= */
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}


/* =========================
   2. BUAT DATABASE
========================= */
$dbName = "db_recruitment";
$sqlDB = "CREATE DATABASE IF NOT EXISTS $dbName";
mysqli_query($conn, $sqlDB);
mysqli_select_db($conn, $dbName);

/* =========================
   3. BUAT TABEL
========================= */
$sqlUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr', 'candidate') DEFAULT 'candidate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if (mysqli_query($conn, $sqlUsers)) {
    echo 'Tabel users berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel: ' . mysqli_error($conn);
}

$sqlDivisi = "CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_divisi VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if (mysqli_query($conn, $sqlDivisi)) {
    echo 'Tabel divisi berhasil dibuat <br>';
} else {
    echo 'Error divisi: ' . mysqli_error($conn);
}

$sqlPositions = "CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    divisi_id INT NOT NULL,
    nama_posisi VARCHAR(100) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (divisi_id) REFERENCES divisions(id)
);
";

if (mysqli_query($conn, $sqlPositions)) {
    echo 'Tabel positions berhasil dibuat <br>';
} else {
    echo 'Error positions: ' . mysqli_error($conn);
}

$sqlSkill = "CREATE TABLE IF NOT EXISTS skills (
    id_skill INT AUTO_INCREMENT PRIMARY KEY,
    nama_skill VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if (mysqli_query($conn, $sqlSkill)) {
    echo 'Tabel skills berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel skills: ' . mysqli_error($conn);
}


$sqlStaff = "CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    nama_staff VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    no_telp VARCHAR(20) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    jenis_kelamin ENUM('L', 'P') DEFAULT NULL,

    foto VARCHAR(255) DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)  
);
";

if (mysqli_query($conn, $sqlStaff)) {
    echo 'Tabel staff berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel staff: ' . mysqli_error($conn);
}

$sqlCandidate = "CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL,

    alamat TEXT DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,

    jenis_kelamin ENUM('L', 'P') DEFAULT NULL,

    is_disabled BOOLEAN DEFAULT FALSE,
    disability_description TEXT DEFAULT NULL,

    foto VARCHAR(255) DEFAULT NULL,
    cv_file VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);
";

if (mysqli_query($conn, $sqlCandidate)) {
    echo 'Tabel candidates berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel candidates: ' . mysqli_error($conn);
}

$sqlCandidateDisabilities = "CREATE TABLE IF NOT EXISTS candidate_disabilities (
    id INT AUTO_INCREMENT PRIMARY KEY,

    candidate_id INT NOT NULL,
    disability_type VARCHAR(100) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlCandidateDisabilities)) {
    echo 'Tabel candidate_disabilities berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel candidate_disabilities: ' . mysqli_error($conn);
}

//PENGALAMAN KERJA
$sqlPengalamanKerja = "
CREATE TABLE IF NOT EXISTS pengalaman_kerja (
    id INT AUTO_INCREMENT PRIMARY KEY,

    candidate_id INT NOT NULL,

    nama_perusahaan VARCHAR(255) NOT NULL,
    posisi VARCHAR(255) NOT NULL,

    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL,

    deskripsi_pekerjaan TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id)
        REFERENCES candidates(id)
        ON DELETE CASCADE
);
";

//PENIDIKAN
$sqlPendidikan = "
CREATE TABLE IF NOT EXISTS pendidikan (
    id_pendidikan INT AUTO_INCREMENT PRIMARY KEY,

    candidate_id INT NOT NULL,

    institusi VARCHAR(255) NOT NULL,
    jenjang ENUM('SD', 'SMP', 'SMA', 'SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3') NOT NULL,
    jurusan VARCHAR(255) DEFAULT NULL,

    tahun_masuk YEAR NOT NULL,
    tahun_lulus YEAR DEFAULT NULL,

    ipk DECIMAL(3,2) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id)
        REFERENCES candidates(id)
        ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlPendidikan)) {
    echo 'Tabel pendidikan berhasil dibuat <br>';
} else {
    echo 'Error pendidikan: ' . mysqli_error($conn);
}

if (mysqli_query($conn, $sqlPengalamanKerja)) {
    echo 'Tabel pengalaman_kerja berhasil dibuat <br>';
} else {
    echo 'Error pengalaman_kerja: ' . mysqli_error($conn);
}

$sqlSertifikasi = "
CREATE TABLE IF NOT EXISTS sertifikasi (
    id_sertifikasi INT AUTO_INCREMENT PRIMARY KEY,

    candidate_id INT NOT NULL,

    nama_sertifikasi VARCHAR(255) NOT NULL,
    penyelenggara VARCHAR(255) NOT NULL,
    tanggal_terbit DATE NOT NULL,

    file_sertifikasi VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id)
        REFERENCES candidates(id)
        ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlSertifikasi)) {
    echo 'Tabel sertifikasi berhasil dibuat <br>';
} else {
    echo 'Error sertifikasi: ' . mysqli_error($conn);
}

$sqlCandidateSkill = "
CREATE TABLE IF NOT EXISTS candidate_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,

    candidate_id INT NOT NULL,
    skill_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id_skill) ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlCandidateSkill)) {
    echo 'Tabel candidate_skills berhasil dibuat <br>';
} else {
    echo 'Error candidate_skills: ' . mysqli_error($conn);
}

// JOB POSTING
$sqlJobPosting = "
CREATE TABLE IF NOT EXISTS job_posting (
    id INT AUTO_INCREMENT PRIMARY KEY,

    posisi_id INT NOT NULL,
    staff_id INT NOT NULL,

    judul_job VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(255) NOT NULL,

    tipe_pekerjaan ENUM('Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance') NOT NULL,

    gaji DECIMAL(15,2) DEFAULT NULL,

    status ENUM('open', 'closed', 'draft') DEFAULT 'draft',

    is_disabilitas BOOLEAN DEFAULT FALSE,
    is_remote_interview BOOLEAN DEFAULT FALSE,
    is_remote_work BOOLEAN DEFAULT FALSE,

    additional_support TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (posisi_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlJobPosting)) {
    echo 'Tabel job_posting berhasil dibuat <br>';
} else {
    echo 'Error job_posting: ' . mysqli_error($conn);
}


// JOB DISABILITAS
$sqlJobDisabilitas = "
CREATE TABLE IF NOT EXISTS job_disabilitas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    job_id INT NOT NULL,
    disability_type VARCHAR(100) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (job_id)
        REFERENCES job_posting(id)
        ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlJobDisabilitas)) {
    echo 'Tabel job_disabilitas berhasil dibuat <br>';
} else {
    echo 'Error job_disabilitas: ' . mysqli_error($conn);
}


// JOB SKILLS
$sqlJobSkills = "
CREATE TABLE IF NOT EXISTS job_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,

    job_id INT NOT NULL,
    skill_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (job_id)
        REFERENCES job_posting(id)
        ON DELETE CASCADE,

    FOREIGN KEY (skill_id)
        REFERENCES skills(id_skill)
        ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlJobSkills)) {
    echo 'Tabel job_skills berhasil dibuat <br>';
} else {
    echo 'Error job_skills: ' . mysqli_error($conn);
}

$sqlTransaksiLamaran = "
CREATE TABLE IF NOT EXISTS candidate_apply_job (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kandidat INT NOT NULL,
    id_lowongan INT NOT NULL,
    catatan TEXT DEFAULT NULL, 
    expert_bidang VARCHAR(50) NOT NULL,
    pengalaman_bidang VARCHAR(50) NOT NULL, 
    status_lamaran ENUM('ADMINISTRASI', 'INTERVIEW', 'OFFERING', 'DITOLAK', 'DITERIMA') DEFAULT 'ADMINISTRASI', 
    tanggal_melamar DATETIME DEFAULT CURRENT_TIMESTAMP,
    tolak_HR TEXT DEFAULT NULL,
    tolak_candidate TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kandidat) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (id_lowongan) REFERENCES job_posting(id) ON DELETE CASCADE,
    UNIQUE KEY unique_apply (id_kandidat, id_lowongan)
);
";

if (mysqli_query($conn, $sqlTransaksiLamaran)) {
    echo 'Tabel candidate_apply_job berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel candidate_apply_job: ' . mysqli_error($conn) . '<br>';
}

$sqlJadwalInterview = "
CREATE TABLE IF NOT EXISTS jadwal_interview (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kandidat INT NOT NULL,
    id_candidate_apply_job INT NOT NULL,
    status_interview ENUM('JADWAL', 'SELESAI', 'BATAL') DEFAULT 'JADWAL',
    catatan TEXT DEFAULT NULL, 
    tanggal_interview DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_kandidat) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (id_candidate_apply_job) REFERENCES candidate_apply_job(id) ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlJadwalInterview)) {
    echo 'Tabel jadwal_interview berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel jadwal_interview: ' . mysqli_error($conn) . '<br>';
}

$sqlOfferingLetter = "
CREATE TABLE IF NOT EXISTS offering_letter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kandidat INT NOT NULL,
    id_candidate_apply_job INT NOT NULL,
    gaji_offering INT NOT NULL,
    status ENUM('DITERIMA', 'DITOLAK'),
    tanggal_offering DATETIME NOT NULL,
    file_offering VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_kandidat) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (id_candidate_apply_job) REFERENCES candidate_apply_job(id) ON DELETE CASCADE
);
";

if (mysqli_query($conn, $sqlOfferingLetter)) {
    echo 'Tabel offering_letter berhasil dibuat <br>';
} else {
    echo 'Error membuat tabel offering_letter: ' . mysqli_error($conn) . '<br>';
}

/* =========================
   4. INSERT ADMIN DEFAULT
========================= */
$password = password_hash("admin123", PASSWORD_DEFAULT);

$sqlInsert = "INSERT INTO users (username, email, password, role)
VALUES ('admin', 'admin@gmail.com', '$password', 'admin')
";

mysqli_query($conn, $sqlInsert);

echo "Database dan tabel berhasil dibuat!";
