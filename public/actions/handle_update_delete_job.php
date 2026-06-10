<?php
require_once __DIR__ . '/../../init.php';

// Handle Publish / Close
if (isset($_GET['status_id']) && isset($_GET['to'])) {
    JobFormController::updateStatus($conn, $_GET['status_id'], $_GET['to']);
    $_SESSION['success'] = "Status berhasil diubah menjadi " . $_GET['to'];
    header("Location: " . BASE_URL . "views/formJob/index.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    JobFormController::delete($conn, $_GET['delete_id']);
    $_SESSION['success'] = "Lowongan berhasil dihapus.";
    header("Location: " . BASE_URL . "views/formJob/index.php");
    exit;
}
