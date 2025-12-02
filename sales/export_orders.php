<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

require_once __DIR__ . '/../vendor/autoload.php'; // por si usas composer para PDF

ini_set('display_errors', 1);
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();
global $pdo;

$format = $_GET['format'] ?? 'pdf';
$search = $_GET['search'] ?? '';

$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE :search OR phone LIKE :search OR address LIKE :search OR city LIKE :search";
    $params['search'] = "%$search%";
}

// Traer todas las órdenes (sin límite)
$sql = "
    SELECT id, total, status, created_at, name, phone, address, city, payment_method
    FROM mi_tienda.orders
    $where
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay resultados, detener
if (empty($orders)) {
    exit("No hay órdenes para exportar.");
}

// Exportar según el formato
if ($format === 'excel') {
    // ===== EXPORTAR A EXCEL =====
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=ordenes.xlsx");

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados
    $headers = ['ID', 'Cliente', 'Teléfono', 'Dirección', 'Ciudad', 'Total', 'Estado', 'Método', 'Fecha'];
    $sheet->fromArray($headers, NULL, 'A1');

    $row = 2;
    foreach ($orders as $order) {
        $sheet->setCellValue("A$row", $order['id']);
        $sheet->setCellValue("B$row", $order['name'] ?: '—');
        $sheet->setCellValue("C$row", $order['phone'] ?: '—');
        $sheet->setCellValue("D$row", $order['address'] ?: '—');
        $sheet->setCellValue("E$row", $order['city'] ?: '—');
        $sheet->setCellValue("F$row", 'L. '.number_format($order['total'], 2));
        $sheet->setCellValue("G$row", $order['status']);
        $sheet->setCellValue("H$row", $order['payment_method']);
        $sheet->setCellValue("I$row", $order['created_at']);
        $row++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

if ($format === 'pdf') {
    // ===== EXPORTAR A PDF =====
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php'; // O dompdf si prefieres

    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema de Órdenes');
    $pdf->SetTitle('Listado de Órdenes');
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    $html = '<h2 style="text-align:center;">📦 Listado de Órdenes</h2>';
    $html .= '<table border="1" cellpadding="4">
                <tr style="background-color:#f2f2f2;">
                    <th><b>ID</b></th>
                    <th><b>Cliente</b></th>
                    <th><b>Teléfono</b></th>
                    <th><b>Dirección</b></th>
                    <th><b>Ciudad</b></th>
                    <th><b>Total</b></th>
                    <th><b>Estado</b></th>
                    <th><b>Método</b></th>
                    <th><b>Fecha</b></th>
                </tr>';

    foreach ($orders as $order) {
        $html .= '<tr>
                    <td>'.$order['id'].'</td>
                    <td>'.($order['name'] ?: '—').'</td>
                    <td>'.($order['phone'] ?: '—').'</td>
                    <td>'.($order['address'] ?: '—').'</td>
                    <td>'.($order['city'] ?: '—').'</td>
                    <td>L. '.number_format($order['total'], 2).'</td>
                    <td>'.$order['status'].'</td>
                    <td>'.$order['payment_method'].'</td>
                    <td>'.$order['created_at'].'</td>
                 </tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('ordenes.pdf', 'I');
    exit;
}

exit("Formato no válido.");
