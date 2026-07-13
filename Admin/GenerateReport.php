<?php
session_start();

// TCPDF autoload via Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ================================
// Database connection
// ================================
require_once __DIR__ . '/../db.php';

// ================================
// Handle form inputs
// ================================
$filter = $_POST['filter'] ?? 'day';
$format = $_POST['format'] ?? 'pdf';

$where = "";
$queryParams = [];
$today = date('Y-m-d');

switch ($filter) {
    case 'day':
        $where = "WHERE CAST(TRANSACTION_DATE AS DATE) = ?";
        $queryParams = [$today];
        break;
    case 'week':
        $where = "WHERE EXTRACT(WEEK FROM TRANSACTION_DATE) = EXTRACT(WEEK FROM NOW())
                  AND EXTRACT(YEAR FROM TRANSACTION_DATE) = EXTRACT(YEAR FROM NOW())";
        break;
    case 'month':
        $where = "WHERE EXTRACT(MONTH FROM TRANSACTION_DATE) = EXTRACT(MONTH FROM NOW())
                  AND EXTRACT(YEAR FROM TRANSACTION_DATE) = EXTRACT(YEAR FROM NOW())";
        break;
    case 'year':
        $where = "WHERE EXTRACT(YEAR FROM TRANSACTION_DATE) = EXTRACT(YEAR FROM NOW())";
        break;
}

// ================================
// Fetch transactions
// ================================
$query = "SELECT TRANSACTION_ID, FIRST_NAME, LAST_NAME, CONTACT_NUMBER,
                 TRANSACTION_DATE, TOTAL_AMOUNT, PAYMENT_METHOD, STATUS
          FROM TRANSACTIONS
          $where
          ORDER BY TRANSACTION_DATE DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
}

// ================================
// If no records found, display custom HTML page
// ================================
if (empty($data)) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>No Transactions Found</title>
        <link rel='stylesheet' href='GenerateReport.css'>
    </head>
    <body>

        <div class='container'>
            <h1>No Transactions Found</h1>
            <p>There are no transactions for the selected range. <br>Please choose a different filter.</p>
            <button onclick='window.location.href=\"DashboardAdmin.php\"'>Go Back to Dashboard</button>
        </div>

    </body>
    </html>
    ";
    exit;  // Stop the script execution after showing the message
}

// ================================
// PDF Export
// ================================
if ($format === 'pdf') {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("SmartBrew Transaction Report");
    $pdf->SetMargins(10, 15, 10);
    $pdf->AddPage();

    $html = '<h2 style="text-align:center;">SmartBrew Transaction Report (' . ucfirst($filter) . ')</h2>';
    $html .= '<table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead><tbody>';

    foreach ($data as $row) {
        $formattedDate = date('Y-m-d H:i', strtotime($row['TRANSACTION_DATE']));

        $html .= '<tr>
                    <td>' . htmlspecialchars($row['TRANSACTION_ID']) . '</td>
                    <td>' . htmlspecialchars($row['FIRST_NAME'] . ' ' . $row['LAST_NAME']) . '</td>
                    <td>' . htmlspecialchars($row['CONTACT_NUMBER'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($formattedDate) . '</td>
                    <td>₱' . number_format($row['TOTAL_AMOUNT'], 2) . '</td>
                    <td>' . htmlspecialchars(ucfirst($row['PAYMENT_METHOD'])) . '</td>
                    <td>' . htmlspecialchars(ucfirst($row['STATUS'])) . '</td>
                </tr>';
    }

    $html .= '</tbody></table>';
    $pdf->writeHTML($html);
    $pdf->Output('transaction_report.pdf', 'D'); // D = Download
    exit;
}
?>
