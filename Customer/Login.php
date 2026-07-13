<?php
session_start();

// Database connection
require_once __DIR__ . '/../db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Use a prepared statement to prevent SQL injection
    $query = "SELECT CUSTOMER_ID, PASSWORD FROM CUSTOMER_LOGIN WHERE USERNAME = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);

    // Fetch result
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $storedPassword = $row['PASSWORD'];

        // Since passwords are NOT hashed, use direct comparison
        if (password_verify($password, $storedPassword)) {
            // Store customer_id and username in session
            $_SESSION['customer_id'] = $row['CUSTOMER_ID'];
            $_SESSION['username'] = $username;
            $_SESSION['logged_in_user'] = $username;

            // Redirect to Dashboard or Menu
            header("Location: DashboardSB.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="stylesheet" href="Login.css">
</head>

<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="../Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè Login</div>
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
        <h1>Customer Login</h1>
        <?php if (isset($error) && $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <h4>Don't Have an Account? <a href="Register.php">Sign up!</a></h4>
    </div>
</body>

</html>
