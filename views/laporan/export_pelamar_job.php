<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LaporanController.php';
require_once __DIR__ . '/../../models/Laporan.php';

if (ob_get_contents()) ob_end_clean(); 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Proteksi
AuthController::requireLogin();
if (!AuthController::isHRD() && !AuthController::isAdmin()) {
    die("Access denied");
}

$job_id = $_GET['job_id'] ?? null;
if (!$job_id) die("ID Lowongan tidak ditemukan.");

// Ambil Data
$jobTitle = LaporanController::getJobTitle($conn, $job_id);
$applicants = LaporanController::exportPelamarByJob($conn, $job_id);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Daftar Pelamar');

// --- 1. HEADER LAPORAN (JUDUL ATAS) ---
$sheet->setCellValue('A1', 'REKAPITULASI DATA PELAMAR KERJA');
$sheet->mergeCells('A1:O1');
$sheet->setCellValue('A2', strtoupper($jobTitle));
$sheet->mergeCells('A2:O2');
$sheet->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i'));
$sheet->mergeCells('A3:O3');

// Styling Judul
$sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- 2. HEADER TABEL (DUA BARIS) ---
// Baris 4: Kategori Header
$sheet->setCellValue('A4', 'NO');
$sheet->mergeCells('A4:A5');
$sheet->setCellValue('B4', 'PROFIL KANDIDAT');
$sheet->mergeCells('B4:E4');
$sheet->setCellValue('F4', 'KUALIFIKASI KHUSUS');
$sheet->mergeCells('F4:G4');
$sheet->setCellValue('H4', 'DETAIL STATUS & EVALUASI LAMARAN');
$sheet->mergeCells('H4:O4');

// Baris 5: Nama Kolom
$headers = [
    'B' => 'NAMA LENGKAP',
    'C' => 'EMAIL',
    'D' => 'NO. HANDPHONE',
    'E' => 'L/P',
    'F' => 'DISABILITAS & KET',
    'G' => 'KEAHLIAN / SKILLS',
    'H' => 'STATUS AKHIR',
    'I' => 'ALASAN TOLAK (HR)',
    'J' => 'ALASAN TOLAK (KANDIDAT)',
    'K' => 'EXPERT BIDANG',
    'L' => 'PENGALAMAN',
    'M' => 'TGL MELAMAR',
    'N' => 'CATATAN AWAL',
    'O' => 'ALAMAT'
];

foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '5', $text);
}

// --- 3. STYLING HEADER TABEL ---
$styleHeader = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']]
];

// Warnai Header Utama
$sheet->getStyle('A4:O5')->applyFromArray($styleHeader);
// Pembeda warna sub-kategori
$sheet->getStyle('B4:E4')->getFill()->getStartColor()->setRGB('2563EB'); // Biru
$sheet->getStyle('F4:G4')->getFill()->getStartColor()->setRGB('475569'); // Abu
$sheet->getStyle('H4:O4')->getFill()->getStartColor()->setRGB('059669'); // Hijau

// --- 4. INPUT DATA ---
$rowNum = 6;
$no = 1;

foreach ($applicants as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['nama_lengkap']);
    $sheet->setCellValue('C' . $rowNum, $row['email']);
    
    // Format No HP sebagai String (biar angka 0 tidak hilang)
    $sheet->setCellValueExplicit('D' . $rowNum, $row['no_hp'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    
    $sheet->setCellValue('E' . $rowNum, $row['jenis_kelamin']);
    
    // Disabilitas
    $disabilityText = $row['is_disabled'] 
        ? "YA (" . $row['jenis_disabilitas'] . ")\n" . $row['disability_description'] 
        : "TIDAK";
    $sheet->setCellValue('F' . $rowNum, $disabilityText);
    
    $sheet->setCellValue('G' . $rowNum, $row['daftar_skill'] ?? '-');
    $sheet->setCellValue('H' . $rowNum, strtoupper($row['status_lamaran']));
    $sheet->setCellValue('I' . $rowNum, $row['tolak_HR'] ?: '-');
    $sheet->setCellValue('J' . $rowNum, $row['tolak_candidate'] ?: '-');
    $sheet->setCellValue('K' . $rowNum, $row['expert_bidang'] ?: '-');
    $sheet->setCellValue('L' . $rowNum, $row['pengalaman_bidang'] ?: '-');
    $sheet->setCellValue('M' . $rowNum, date('d/m/Y', strtotime($row['tanggal_melamar'])));
    $sheet->setCellValue('N' . $rowNum, $row['catatan_lamaran'] ?: '-');
    $sheet->setCellValue('O' . $rowNum, $row['alamat']);

    // Styling Baris Data (Borders & Alignment)
    $sheet->getStyle('A'.$rowNum.':O'.$rowNum)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    $sheet->getStyle('A'.$rowNum.':O'.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    // Highlight kolom alasan jika ada isinya (warna merah muda halus)
    if (!empty($row['tolak_HR']) || !empty($row['tolak_candidate'])) {
        $sheet->getStyle('I'.$rowNum.':J'.$rowNum)->getFill()
              ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF1F2');
    }

    $rowNum++;
}

// --- 5. FINALISASI (AUTO SIZE & WRAP TEXT) ---
foreach (range('A', 'O') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Kolom yang isinya panjang kita set manual dan aktifkan Wrap Text
$sheet->getColumnDimension('F')->setAutoSize(false)->setWidth(30);
$sheet->getColumnDimension('G')->setAutoSize(false)->setWidth(30);
$sheet->getColumnDimension('I')->setAutoSize(false)->setWidth(25);
$sheet->getColumnDimension('J')->setAutoSize(false)->setWidth(25);
$sheet->getColumnDimension('N')->setAutoSize(false)->setWidth(25);
$sheet->getColumnDimension('O')->setAutoSize(false)->setWidth(30);
$sheet->getStyle('A6:O' . ($rowNum-1))->getAlignment()->setWrapText(true);

// --- 6. PROSES DOWNLOAD ---
$fileName = "Laporan_Pelamar_" . str_replace(' ', '_', $jobTitle) . "_" . date('Ymd') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;