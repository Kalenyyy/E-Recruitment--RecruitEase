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

// Ambil Data
$data = LaporanController::exportKandidat($conn);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$headers = ['No', 'Nama Lengkap', 'Email', 'No HP', 'Gender', 'Status Disabilitas', 'Alamat'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}

// Styling Header (Elegant Emerald)
$sheet->getStyle('A1:G1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Isi Data
$i = 2; $no = 1;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $i, $no++);
    $sheet->setCellValue('B' . $i, $row['nama_lengkap']);
    $sheet->setCellValue('C' . $i, $row['email']);
    
    // Set format No HP sebagai TEXT agar nol tidak hilang
    $sheet->setCellValueExplicit('D' . $i, $row['no_hp'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    
    $sheet->setCellValue('E' . $i, $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');
    $sheet->setCellValue('F' . $i, $row['is_disabled'] ? 'Disabilitas' : 'Non-Disabilitas');
    $sheet->setCellValue('G' . $i, $row['alamat']);
    $i++;
}

// Auto size kolom
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_Kandidat_' . date('Y-m-d') . '.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;