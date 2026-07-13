<?php
session_start();

// Check if the order details are stored in the session
if (!isset($_SESSION['order_receipt'])) {
    // Redirect back to checkout if session data is missing
    header("Location: checkout.php");
    exit();
}

// Retrieve order details from session
$order = $_SESSION['order_receipt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <link rel="stylesheet" href="order_receipt.css">
    <script>
        function printReceipt() {
            window.print();  // This triggers the print dialog
        }
    </script>
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè</div>
        </div>
    </div>

    <div class="order-receipt">
        <h1>Official Receipt</h1>
        <p>Thank you, <?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?>. Your order has been placed successfully.</p>
        <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
        <p><strong>Account Number:</strong> <?php echo htmlspecialchars($order['account_number']); ?></p>

        <a href="Menu.php" class="back-to-dashboard no-print">Menu</a>
        <button onclick="printReceipt()" class="print-btn no-print">Print Receipt</button>

    </div>

</body>
</html>

<?php
// Clear session data after displaying receipt
unset($_SESSION['order_receipt']);
?>
