<?php
require_once __DIR__ . '/../../init.php';

if (ob_get_contents()) ob_end_clean(); 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Proteksi
AuthController::requireLogin();
AuthController::isHRD() or die("Akses Ditolak");

// Ambil Data dari Controller
$data = LaporanController::exportLowongan($conn);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 1. SET HEADER TABEL
$headers = ['No', 'Judul Lowongan', 'Divisi', 'Tipe', 'Gaji Min', 'Gaji Max', 'Status', 'Total Pelamar'];
$columnIndex = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . '1', $header);
    $columnIndex++;
}

// 2. STYLING HEADER (Elegant Blue)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1E3A8A']
    ]
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// 3. ISI DATA
$rowNum = 2;
$no = 1;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['judul_job']);
    $sheet->setCellValue('C' . $rowNum, $row['nama_divisi']);
    $sheet->setCellValue('D' . $rowNum, $row['tipe_pekerjaan']);
    $sheet->setCellValue('E' . $rowNum, $row['gaji_min']);
    $sheet->setCellValue('F' . $rowNum, $row['gaji_max']);
    $sheet->setCellValue('G' . $rowNum, strtoupper($row['status']));
    $sheet->setCellValue('H' . $rowNum, $row['total_pelamar']);
    $rowNum++;
}

// 4. OTOMATIS LEBAR KOLOM
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. PROSES DOWNLOAD
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_Lowongan_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
