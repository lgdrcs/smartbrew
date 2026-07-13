<?php
session_start();

// Check if user is logged in
$username = $_SESSION['username'] ?? null;
if (!$username) {
    echo "Please login to the website.
    You will now be redirected back to the Dashboard to login after 5 seconds.";
    echo '<meta http-equiv="refresh" content="5;url=dashboardsb.php">';
    exit();
}

require_once __DIR__ . '/../db.php';

// Fetch UUID from database
$sql = "SELECT UNIQUE_QR FROM CUSTOMER_LOGIN WHERE USERNAME = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$uuid = $row['UNIQUE_QR'] ?? null;
if (!$uuid) {
    echo "QR code not found for user.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User QR Code</title>
    <link rel="stylesheet" href="QR.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
  :root {
    --primary-color: #3e2b1f;  /* Adjust based on your theme */
    --secondary-color: #5f4b3a;
  }
</style>
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="../Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="DashboardSB.php">
                <button type="submit">Dashboard</button>
            </form>
             <form method="GET" action="Menu.php">
                <button type="submit">Menu</button>
            </form>
            <form method="GET" action="QR.php">
                <button type="submit" class="active">QR</button>
            </form>
            <form method="GET" action="Logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>

  <h2>Please scan your personal QR Code</h2>
  <div id="qrcode"></div>
  <h4>Happy Eating!</h4>

  <script>
    const uuid = "<?= $uuid ?>";
    new QRCode(document.getElementById("qrcode"), {
      text: uuid,
      width: 256,
      height: 256,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  </script>

</body>
</html>
