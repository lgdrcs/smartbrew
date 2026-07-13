<?php
session_start();
$loggedIn = isset($_SESSION['customer_id']); // Check if the user is logged in

require_once __DIR__ . '/db.php';

// Sample menu items
$menuItems = [
    ["id" => 1, "name" => "Caramel Machiato", "category" => "coffee", "price" => 150, "image" => "Assets/cm.png"],
    ["id" => 2, "name" => "Vietnamese", "category" => "coffee", "price" => 150, "image" => "Assets/vietnam.png"],
    ["id" => 3, "name" => "Hot-Chocolate", "category" => "noncoffee", "price" => 100, "image" => "Assets/Hotc.png"],
    ["id" => 4, "name" => "Brownies", "category" => "pastries", "price" => 50, "image" => "Assets/Brown.png"],
    ["id" => 5, "name" => "Cookies", "category" => "pastries", "price" => 55, "image" => "Assets/Cok.png"],
];

// Get logged-in customer ID
$customer_id = $_SESSION['customer_id'] ?? null;

// Handle add to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    if (!$loggedIn) {
        // Redirect to login page if not logged in
        header("Location: Login.php");
        exit();
    }

    $item_id = $_POST['item_id'];
    foreach ($menuItems as $item) {
        if ($item['id'] == $item_id) {
            $query = "SELECT QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
            $params = [$customer_id, $item_id];
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $updateQuery = "UPDATE CUSTOMER_CART SET QUANTITY = QUANTITY + 1 WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
                $pdo->prepare($updateQuery)->execute($params);
            } else {
                $insertQuery = "INSERT INTO CUSTOMER_CART (CUSTOMER_ID, ITEM_ID, NAME, QUANTITY, PRICE) VALUES (?, ?, ?, 1, ?)";
                $insertParams = [$customer_id, $item_id, $item['name'], $item['price']];
                $pdo->prepare($insertQuery)->execute($insertParams);
            }
            break;
        }
    }
}

// Get cart count
$cart_count = 0;
if ($customer_id) {
    $cartQuery = "SELECT SUM(QUANTITY) FROM CUSTOMER_CART WHERE CUSTOMER_ID = ?";
    $stmt = $pdo->prepare($cartQuery);
    $stmt->execute([$customer_id]);
    if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $cart_count = $row[0] ?? 0;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu Dashboard</title>
    <link rel="stylesheet" href="Menu.css">
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè Dashboard</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="DashboardSB.php">
                <button type="submit">Dashboard</button>
            </form>
            <form method="GET" action="Menu.php">
                <button type="submit" class="active">Menu</button>
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

    <div class="container">
        <div class="search-bar">
            <input type="text" id="search" placeholder="Search food...">
            <button onclick="window.location.href='cart.php'">Cart (<?php echo $cart_count; ?>)</button>
        </div>

        <div class="menu-grid" id="menu">
            <?php foreach ($menuItems as $item): ?>
                <div class="menu-item" data-category="<?php echo $item['category']; ?>">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                    <h3><?php echo $item['name']; ?></h3>
                    <p>₱<?php echo number_format($item['price'], 2); ?></p>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
