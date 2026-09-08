<?php
require '../../vendor/autoload.php';
require_once '../../includes/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Sistem Disposisi')
        ->setLastModifiedBy('Sistem Disposisi')
        ->setTitle('Data Disposisi Surat')
        ->setSubject('Data Disposisi Surat')
        ->setDescription('Daftar Disposisi Surat')
        ->setKeywords('disposisi surat export')
        ->setCategory('Data Export');

    // Add header row with new Kategori column
    $headers = ['No', 'Kode', 'Kategori', 'Tanggal', 'Nomor Surat', 'Perihal', 'Ke'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Style the header row
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '000000'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FBFBFB'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
    $sheet->getRowDimension('1')->setRowHeight(30);

    // Build base query
    $query = "SELECT * FROM disposisi_keluar";
    $params = [];
    $where_conditions = [];

    // Add filter conditions
    if (isset($_GET['bulan']) && $_GET['bulan'] !== '') {
        $where_conditions[] = "MONTH(tanggal) = ?";
        $params[] = $_GET['bulan'];
    }

    if (isset($_GET['tahun']) && $_GET['tahun'] !== '') {
        $where_conditions[] = "YEAR(tanggal) = ?";
        $params[] = $_GET['tahun'];
    }

    // Add category filter
    if (isset($_GET['kategori']) && $_GET['kategori'] !== '') {
        if ($_GET['kategori'] === 'BI') {
            $where_conditions[] = "kode = '1'";
        } elseif ($_GET['kategori'] === 'OJK') {
            $where_conditions[] = "kode = '2'";
        } elseif ($_GET['kategori'] === 'UMUM') {
            $where_conditions[] = "kode NOT IN ('1', '2')";
        }
    }

    // Add WHERE clause if conditions exist
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // Add ORDER BY
    $query .= " ORDER BY tanggal DESC, id DESC";

    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Fetch and write data
    $row = 2;
    $nomor = 1;
    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Determine category based on kode
        $kategori = '';
        if ($data['kode'] === '1') {
            $kategori = 'Surat BI';
        } elseif ($data['kode'] === '2') {
            $kategori = 'Surat OJK';
        } else {
            $kategori = 'Surat Umum';
        }

        $sheet->setCellValue('A' . $row, $nomor);
        $sheet->setCellValue('B' . $row, $data['kode']);
        $sheet->setCellValue('C' . $row, $kategori);
        $sheet->setCellValue('D' . $row, $data['tanggal']);
        $sheet->setCellValue('E' . $row, $data['nomor_surat']);
        $sheet->setCellValue('F' . $row, $data['perihal']);
        $sheet->setCellValue('G' . $row, $data['ke']);
        $row++;
        $nomor++;
    }

    // Style the data rows
    if ($row > 2) {
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:G' . ($row - 1))->applyFromArray($dataStyle);
    }

    // Center align the No column and Category column
    $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C2:C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Auto-size columns
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Generate filename with filter info
    $filename = 'Data_Disposisi_Keluar';
    if (isset($_GET['kategori']) && $_GET['kategori'] !== '') {
        $filename .= '_' . $_GET['kategori'];
    }
    if (isset($_GET['bulan']) && $_GET['bulan'] !== '') {
        $bulan_list = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $filename .= '_' . $bulan_list[$_GET['bulan']];
    }
    if (isset($_GET['tahun']) && $_GET['tahun'] !== '') {
        $filename .= '_' . $_GET['tahun'];
    }
    $filename .= '_' . date('Y-m-d_His') . '.xlsx';

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Output file to browser
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
