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
    idINT AUTO_INCREMENT PRIMARY KEY,

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

if (mysqli_query($conn, $sqlPengalamanKerja)) {
    echo 'Tabel pengalaman_kerja berhasil dibuat <br>';
} else {
    echo 'Error pengalaman_kerja: ' . mysqli_error($conn);
}

/* =========================
   4. INSERT ADMIN DEFAULT
========================= */
$password = password_hash("admin123", PASSWORD_DEFAULT);

$sqlInsert = "
INSERT INTO users (username, email, password, role)
VALUES ('admin', 'admin@gmail.com', '$password', 'admin')
";

mysqli_query($conn, $sqlInsert);

echo "Database dan tabel berhasil dibuat!";

