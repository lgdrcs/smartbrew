<?php
session_start();

// Database connection
require_once __DIR__ . '/../db.php';

/// Handle pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total transactions
$countQuery = "SELECT COUNT(*) as total FROM TRANSACTIONS";
$totalRow = $pdo->query($countQuery)->fetch(PDO::FETCH_ASSOC);
$totalTransactions = $totalRow['TOTAL'];
$totalPages = ceil($totalTransactions / $limit);

// Fetch paginated transactions
$query = "SELECT TRANSACTION_ID, FIRST_NAME, LAST_NAME, CONTACT_NUMBER, TRANSACTION_DATE, TOTAL_AMOUNT, PAYMENT_METHOD, STATUS, PAYMENT_RECEIPT
FROM TRANSACTIONS
ORDER BY TRANSACTION_DATE DESC
LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$limit, $offset]);
$transactions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $transactions[] = $row;
}

$noTransactions = count($transactions) === 0; // Flag to check if no transactions exist

// Redirect to GenerateReport.php if no transactions found
if ($noTransactions) {
    header('Location: GenerateReport.php'); // Redirect to the report generation page
    exit; // Make sure to stop further execution of the code
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Transactions</title>
    <link rel="stylesheet" href="Transactions.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo-container">
        <img src="Assets/kape.png" alt="Logo">
        <div class="logo-text">Transactions</div>
    </div>
    <div class="nav-links">
        <a href="DashboardAdmin.php">Dashboard</a>
        <a href="Transactions.php" class="active">Transactions</a>
        <a href="Logout.php">Log Out</a>
    </div>
</div>

<!-- Content -->
<div class="content">
    <h1>Customer Transactions</h1>
    <div class="reports">
        <form method="POST" action="GenerateReport.php" id="reportForm">
            <label for="filter">Select Report Range:</label>
            <select name="filter" id="filter" required>
                <option value="day">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>

            <label for="format">Format:</label>
            <select name="format" id="format" required>
                <option value="pdf">PDF</option>
            </select>

            <button type="submit">Generate Report</button>
        </form>
    </div>

    <?php if (!$noTransactions): ?>

        <div class="scrollable-table">
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>#<?php echo $t['TRANSACTION_ID']; ?></td>
                        <td><?php echo $t['FIRST_NAME'] . ' ' . $t['LAST_NAME']; ?></td>
                        <td><?php echo $t['CONTACT_NUMBER'] ?? 'N/A'; ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($t['TRANSACTION_DATE'])); ?></td>
                        <td>₱<?php echo number_format($t['TOTAL_AMOUNT'], 2); ?></td>
                        <td><?php echo ucfirst($t['PAYMENT_METHOD']); ?></td>
                        <td><?php echo ucfirst($t['STATUS']); ?></td>
                        <td>
                            <?php if (!empty($t['PAYMENT_RECEIPT'])): ?>
                                <a href="../uploads/receipts/<?php echo htmlspecialchars($t['PAYMENT_RECEIPT']); ?>" 
                                target="_blank" 
                                class="receipt-button">View Receipt</a>
                            <?php else: ?>
                                <button class="receipt-button disabled" disabled>N/A</button>
                            <?php endif; ?>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php echo ($i === $page) ? 'class="active"' : ''; ?>>
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
</div>

</footer>

<script>
    // Prevent form submission if no records found
    const reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(event) {
        <?php if ($noTransactions): ?>
            event.preventDefault(); // Prevent form submission
            alert('No transactions available for the selected filter.');
        <?php endif; ?>
    });
</script>

</body>
</html>
