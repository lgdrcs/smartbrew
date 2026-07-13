<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: Login.php");
    exit();
}

require_once __DIR__ . '/db.php';

$customer_id = $_SESSION['customer_id'];

$stampCount = 0;
$stampQuery = "SELECT STAMPS FROM CUSTOMER_LOGIN WHERE CUSTOMER_ID = ?";
$stampStmt = $pdo->prepare($stampQuery);
$stampStmt->execute([$customer_id]);
if ($stampRow = $stampStmt->fetch(PDO::FETCH_ASSOC)) {
    $stampCount = (int)$stampRow['STAMPS'];
}

// Fetch default info
$firstNamePrefill = $lastNamePrefill = $addressPrefill = $contactNumberPrefill = "";
$userQuery = "SELECT FIRSTNAME, LASTNAME, ADDRESS, CONTACTNUMBER FROM CUSTOMER_LOGIN WHERE CUSTOMER_ID = ?";
$userParams = [$customer_id];
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute($userParams);
if ($userData = $userStmt->fetch(PDO::FETCH_ASSOC)) {
    $firstNamePrefill = htmlspecialchars($userData['FIRSTNAME']);
    $lastNamePrefill = htmlspecialchars($userData['LASTNAME']);
    $addressPrefill = htmlspecialchars($userData['ADDRESS']);
    $contactNumberPrefill = htmlspecialchars($userData['CONTACTNUMBER']);
}


// Handle form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isRedeemed = isset($_POST['redeem_stamp']) && $_POST['redeem_stamp'] === '1';
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $address = $_POST['address'];
    $contactNumber = $_POST['contact_number'];
    $paymentMethod = $_POST['payment_method'];

    $accountNumber = '';
if (in_array($paymentMethod, ['gcash', 'maya'])) {
    $field = $paymentMethod . '_number';
    $accountNumber = trim($_POST[$field] ?? '');

    if ($accountNumber === '') {
        echo "<script>alert('Account number is required for $paymentMethod payment.'); window.history.back();</script>";
        exit();
        }
    }   

    // Receipt logic
    $receiptFileName = null;
    if ($paymentMethod === 'gcash' || $paymentMethod === 'maya') {
        if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['payment_receipt']['tmp_name'];
            $fileName = $_FILES['payment_receipt']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $dir = './uploads/receipts/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            if (in_array($ext, $allowed)) {
                $newName = $customer_id . '_' . time() . '.' . $ext;
                $dest = $dir . $fileName;
                if (move_uploaded_file($fileTmpPath, $dest)) {
                    $receiptFileName = $fileName;
                } else {
                    die("Upload failed.");
                }
            } else {
                die("Invalid file type.");
            }
        } else {
            die("File required for this payment method.");
        }
    }

    // Compute total
    $query = "SELECT PRICE, QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$customer_id]);
    $totalPrice = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $totalPrice += $row['PRICE'] * $row['QUANTITY'];
    }


    $insert = "
    INSERT INTO TRANSACTIONS (
        CUSTOMER_ID, FIRST_NAME, LAST_NAME, CONTACT_NUMBER, TRANSACTION_DATE,
        TOTAL_AMOUNT, PAYMENT_METHOD, STATUS, ADDRESS, ACCOUNT_NUMBER, PAYMENT_RECEIPT
    ) VALUES (?, ?, ?, ?, NOW(), ?, ?, 'Completed', ?, ?, ?)";
$params = [$customer_id, $firstName, $lastName, $contactNumber, $totalPrice, $paymentMethod, $address, $accountNumber, $receiptFileName];
$insertSuccess = $pdo->prepare($insert)->execute($params);

if ($insertSuccess) {
    // Only if transaction insert is successful

    // Clear user's cart
    $pdo->prepare("DELETE FROM CUSTOMER_CART WHERE CUSTOMER_ID = ?")->execute([$customer_id]);

    // Add stamp only if current stamp count is less than 3
    $updateStamps = "
        UPDATE CUSTOMER_LOGIN
        SET STAMPS = CASE
            WHEN COALESCE(STAMPS, 0) < 3 THEN STAMPS + 1
            ELSE STAMPS
        END
        WHERE CUSTOMER_ID = ?
    ";
    $pdo->prepare($updateStamps)->execute([$customer_id]);


    if ($isRedeemed && $stampCount === 3) {
        $totalPrice *= 0.5;

        // Reset stamps to 0
        $resetStamps = "UPDATE CUSTOMER_LOGIN SET STAMPS = 0 WHERE CUSTOMER_ID = ?";
        $pdo->prepare($resetStamps)->execute([$customer_id]);

        // Update session to show user redeemed
        $_SESSION['redeemed_stamp'] = true;
    }
    
    
    $_SESSION['order_receipt'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'address' => $address,
        'contact_number' => $contactNumber,
        'payment_method' => $paymentMethod,
        'total_price' => $totalPrice,
        'account_number' => $accountNumber
    ];
    header("Location: order_receipt.php");
    exit();
}
}

// Fetch total price for display
$query = "SELECT PRICE, QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$customer_id]);
$totalPrice = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $totalPrice += $row['PRICE'] * $row['QUANTITY'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
<div class="navbar">
    <div class="logo-container">
        <img src="Assets/kape.png" alt="SmartBrew Logo">
        <div class="logo-text">SmartBrew Cafe</div>
    </div>
    <div class="nav-links">
        <a href="DashboardSB.php">Dashboard</a>
        <a href="Cart.php">Cart</a>
        <a href="Logout.php">Logout</a>
    </div>
</div>

<div class="checkout-form">
    <h1>Checkout Form</h1>
    <form action="checkout.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="info_option">Select Info Option:</label>
            <select id="info_option" name="info_option" required>
                <option value="default">Use Default Info</option>
                <option value="manual">Fill Up Manually</option>
            </select>
        </div>

        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required value="<?= $firstNamePrefill ?>">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required value="<?= $lastNamePrefill ?>">
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <textarea id="address" name="address" required><?= $addressPrefill ?></textarea>
        </div>

        <div class="form-group">
            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" required value="<?= $contactNumberPrefill ?>">
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select id="payment_method" name="payment_method" required>
                <option value="cash_on_delivery">Cash on Delivery</option>
                <option value="gcash">GCash</option>
                <option value="maya">Maya</option>
            </select>
        </div>

        <div id="gcash-details" class="payment-method-details" style="display:none;">
            <div class="form-group">
                <label for="gcash_number">GCash Number:</label>
                <input type="text" id="gcash_number" name="gcash_number" placeholder="Enter GCash number">
            </div>
        </div>

        <div id="maya-details" class="payment-method-details" style="display:none;">
            <div class="form-group">
                <label for="maya_number">Maya Number:</label>
                <input type="text" id="maya_number" name="maya_number" placeholder="Enter Maya number">
            </div>
        </div>

        <div id="payment_receipt_div" class="payment-method-details" style="display:none;">
            <div class="form-group">
                <label for="payment_receipt">Upload Payment Receipt (Image only):</label>
                <input type="file" id="payment_receipt" name="payment_receipt" accept="image/*">
            </div>
        </div>

        <div class="total-price">
            <strong>Total Price: ₱<?= number_format($totalPrice, 2) ?></strong>
        </div>

        
        <div class="stamp-redeem-section">
    <p class="stamp-status">
        You currently have <?= $stampCount ?> stamp<?= $stampCount !== 1 ? 's' : '' ?>.
    </p>
    <div class="redeem-controls">
        <input
            type="checkbox"
            name="redeem_stamp"
            value="1"
            id="redeem_checkbox"
            <?= $stampCount < 3 ? 'disabled' : '' ?>
        >
        <label
            for="redeem_checkbox"
            class="redeem-text <?= $stampCount === 3 ? 'green' : 'gray' ?>"
        >
            Redeem 3 stamps for 50% off
        </label>
    </div>
</div>



        <button type="submit" class="submit-btn">Place Order</button>
    </form>
</div>

<script>
    const infoOption = document.getElementById('info_option');
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    const address = document.getElementById('address');
    const contactNumber = document.getElementById('contact_number');
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentReceiptDiv = document.getElementById('payment_receipt_div');
    const paymentReceiptInput = document.getElementById('payment_receipt');

    const defaultValues = {
        firstName: firstName.value,
        lastName: lastName.value,
        address: address.value,
        contactNumber: contactNumber.value
    };

    infoOption.addEventListener('change', () => {
        if (infoOption.value === 'default') {
            firstName.value = defaultValues.firstName;
            lastName.value = defaultValues.lastName;
            address.value = defaultValues.address;
            contactNumber.value = defaultValues.contactNumber;
        } else {
            firstName.value = '';
            lastName.value = '';
            address.value = '';
            contactNumber.value = '';
        }
    });

    paymentMethodSelect.addEventListener('change', function () {
        const method = this.value;
        document.getElementById('gcash-details').style.display = method === 'gcash' ? 'block' : 'none';
        document.getElementById('maya-details').style.display = method === 'maya' ? 'block' : 'none';

        if (method === 'gcash' || method === 'maya') {
            paymentReceiptDiv.style.display = 'block';
            paymentReceiptInput.required = true;
        } else {
            paymentReceiptDiv.style.display = 'none';
            paymentReceiptInput.required = false;
            paymentReceiptInput.value = '';
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('payment_method').dispatchEvent(new Event('change'));
    });
</script>
</body>
</html>
