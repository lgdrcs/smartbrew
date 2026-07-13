<?php
session_start();

require_once __DIR__ . '/../db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // INSERT IN SQL
    $sql = "INSERT INTO ADMIN_LOGIN (USERNAME, PASSWORD) VALUES (?, ?)";
    $params = array($username, $password);
    $sqlResult = $pdo->prepare($sql)->execute($params);

    if ($sqlResult === false) {
        die("Registration failed.");
    } else {
        // Redirect to adminlogin page after successful registration
        header("Location: Admin.php");
        exit(); // Make sure to stop script execution after redirection
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="AdminRegister.css">
</head>

<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè Admin</div>
        </div>
    </div>
    <div class="container">
        <h1>Register as Admin</h1>
        <?php if (isset($error) && $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Register</button>
        </form>
        <h4>Already have an Admin Account? <a href="Admin.php">Login</a></h4>
    </div>
</body>

</html>