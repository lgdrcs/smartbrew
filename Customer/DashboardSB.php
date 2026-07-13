<?php
session_start();
$loggedIn = isset($_SESSION['customer_id']); // Check if the user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartBrew Cafè</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="DashboardSB.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo-container">
            <img src="../Assets/kape.png" alt="DLSU Logo">
            <div class="logo-text">SmartBrew Cafè</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="SmartBrew.php">
                <button type="submit" class="active">Dashboard</button>
            </form>
            <form method="GET" action="Menu.php">
                <button type="submit">Menu</button>
            </form>
            <form method="GET" action="QR.php">
                <button type="submit">QR</button>
            </form>
            <?php if (!$loggedIn): ?>
                <form method="GET" action="Login.php">
                    <button type="submit">Login</button>
                </form>
            <?php else: ?>
                <form method="GET" action="Logout.php">
                    <button type="submit">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>SmartBrew Café</h1>
            <h2>Convenience in Every Cup</h2>
            <p>
                Where technology and taste blend seamlessly. Enjoy expertly brewed coffee with ultimate convenience, 
                perfect for your on-the-go lifestyle.
            </p>
            <form method="GET" action="Menu.php">
                <button type="submit" class="register-button">Order now!</button>
            </form>
        </div>
        <div class="hero-image">
            <img src="../Assets/Hero.png" alt="Coffee Cup">
        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; 2024 De La Salle University-Dasmariñas. All rights reserved.
    </footer>

</body>
</html>
