<?php

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

// kalau sudah login
header("Location: " . BASE_URL . "views/dashboard.php");
exit;