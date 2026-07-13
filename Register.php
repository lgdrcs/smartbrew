<?php
session_start();

require_once __DIR__ . '/db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];  
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $contactnumber = $_POST['contactnumber'];

    // Generate a UUID
    $uuid = uniqid('qr_', true); // You can also use openssl_random_pseudo_bytes for stronger randomness

    // Check if the username already exists
    $checkSql = "SELECT * FROM CUSTOMER_LOGIN WHERE USERNAME = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$username]);
    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
        $error = "Username already exists. Please choose another.";
    } else {
        // Insert with QR UUID
        $sql = "INSERT INTO CUSTOMER_LOGIN (USERNAME, PASSWORD, ADDRESS, FIRSTNAME, LASTNAME, CONTACTNUMBER, UNIQUE_QR)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = array($username, $password, $address, $firstname, $lastname, $contactnumber, $uuid);
        $sqlResult = $pdo->prepare($sql)->execute($params);

        if ($sqlResult) {
            // Store UUID in session for future use if needed (e.g., in QR.php)
            $_SESSION['qr_uuid'] = $uuid;
        
            // ✅ Redirect to login page instead of QR page
            header("Location: Login.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Customer Registration</title>
    <link rel="stylesheet" href="Register.css" />
</head>

<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="SmartBrew Logo" />
            <div class="logo-text">SmartBrew Dashboard</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="DashboardSB.php">
                <button type="submit" class="active">Dashboard</button>
            </form>
            <form method="GET" action="Menu.php">
                <button type="submit">Menu</button>
            </form>
        </div>
    </div>
    <div class="container">
        <h1>Customer Registration</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required />

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required />

            <label for="contactnumber">Contact Number:</label>
            <input type="tel" id="contactnumber" name="contactnumber" required pattern="[0-9+\-\s]+" title="Enter a valid phone number" />

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required />

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required />

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required />

            <button type="submit">Register</button>
        </form>
        <h4>Already have an Account? <a href="login.php">Login</a></h4>
    </div>
</body>

</html>