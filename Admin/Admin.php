<?php
session_start();

require_once __DIR__ . '/../db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Use a prepared statement to prevent SQL injection
    $query = "SELECT PASSWORD FROM ADMIN_LOGIN WHERE USERNAME = ?";
    $params = [$username];
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Fetch result
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $storedPassword = $row['PASSWORD'];

        // Since passwords are NOT hashed, use direct comparison
        if (password_verify($password, $storedPassword)) {
            $_SESSION['username'] = $username;

            // Retrieve ADMIN_ID from the database
            $query = "SELECT ADMIN_ID FROM ADMIN_LOGIN WHERE USERNAME = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['ADMIN_ID'] = $row['ADMIN_ID'];  // Set ADMIN_ID in session
            }

            header("Location: DashboardAdmin.php");
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="Admin.css">
 </head>

<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè Admin Login</div>
        </div>
    </div>
    <div class="container">
        <h1>Admin Login</h1>
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
        <h4>Don't have an account as Admin? <a href="AdminRegister.php">Sign up!</a></h4>
    </div>
</body>

</html>