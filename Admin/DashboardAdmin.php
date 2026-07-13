<?php
session_start();
if (!isset($_SESSION['ADMIN_ID'])) {
    // Not logged in — redirect to Admin login page
    header("Location: Admin.php");
    exit();
}


// Database connection
require_once __DIR__ . '/../db.php';

// Retrieve the logged-in admin's username
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="DashboardAdmin.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="DLSU Logo">
            <div class="logo-text">SmartBrew Cafè</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="DashboardAdmin.php">
                <button type="submit" class="active">Dashboard</button>
            </form>
            <form method="GET" action="Transactions.php">
                <button type="submit">Transactions</button>
            </form>
            <form method="POST" action="Logout.php">
                <button type="submit">Log Out</button>
            </form>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Admin Dashboard</h1>
            <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
            <p>
                 Manage all transactions and view detailed reports from here.
            </p>
        </div>
        <div class="hero-image">
            <img src="Assets/Hero.png" alt="Admin Panel">
        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; 2024 De La Salle University-Dasmariñas. All rights reserved.
    </footer>

</body>
</html>