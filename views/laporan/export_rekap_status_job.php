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

AuthController::requireLogin();
if (!AuthController::isHRD() && !AuthController::isAdmin()) die("Akses Ditolak");

$data = LaporanController::exportRekapStatusJob($conn);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Status Lowongan');

// --- 1. HEADER LAPORAN ---
$sheet->setCellValue('A1', 'LAPORAN REKAPITULASI STATUS KANDIDAT PER PEKERJAAN');
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A2', 'Dicetak pada: ' . date('d F Y, H:i'));
$sheet->mergeCells('A2:H2');

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- 2. HEADER TABEL ---
$headers = ['NO', 'JUDUL PEKERJAAN', 'ADMINISTRASI', 'INTERVIEW', 'OFFERING', 'DITERIMA', 'DITOLAK', 'TOTAL PELAMAR'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '4', $h);
    $col++;
}

// Styling Header
$styleHeader = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A4:H4')->applyFromArray($styleHeader);

// --- 3. ISI DATA ---
$rowNum = 5;
$no = 1;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['judul_job']);
    $sheet->setCellValue('C' . $rowNum, $row['jml_administrasi']);
    $sheet->setCellValue('D' . $rowNum, $row['jml_interview']);
    $sheet->setCellValue('E' . $rowNum, $row['jml_offering']);
    $sheet->setCellValue('F' . $rowNum, $row['jml_diterima']);
    $sheet->setCellValue('G' . $rowNum, $row['jml_ditolak']);
    $sheet->setCellValue('H' . $rowNum, $row['total_pelamar']);

    // Alignment Center untuk angka
    $sheet->getStyle('C' . $rowNum . ':H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Highlight warna untuk "Diterima"
    $sheet->getStyle('F' . $rowNum)->getFont()->setBold(true)->getColor()->setRGB('059669');

    $rowNum++;
}

// Auto size
foreach (range('A', 'H') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// --- 4. DOWNLOAD ---
$fileName = "Rekap_Status_Pelamar_" . date('Ymd') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
