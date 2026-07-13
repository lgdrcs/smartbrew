<?php
session_start();

require_once __DIR__ . '/../db.php';

// Ensure user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: Login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$loggedIn = isset($_SESSION['customer_id']);

// Add or update cart item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];

    // Check if item exists in cart
    $query = "SELECT QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
    $params = [$customer_id, $item_id];
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $new_quantity = $row['QUANTITY'] + 1;
        $updateQuery = "UPDATE CUSTOMER_CART SET QUANTITY = ? WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
        $pdo->prepare($updateQuery)->execute([$new_quantity, $customer_id, $item_id]);
    } else {
        $insertQuery = "INSERT INTO CUSTOMER_CART (CUSTOMER_ID, ITEM_ID, NAME, QUANTITY, PRICE)
                        VALUES (?, ?, ?, 1, ?)";
        $pdo->prepare($insertQuery)->execute([$customer_id, $item_id, $name, $price]);
    }

    header("Location: cart.php");
    exit();
}

// Decrease item quantity by 1 or remove item completely if quantity is 1
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item_id'])) {
    $remove_item_id = $_POST['remove_item_id'];

    // Fetch the current quantity of the item
    $query = "SELECT QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
    $params = [$customer_id, $remove_item_id];
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $current_quantity = $row['QUANTITY'];

        if ($current_quantity > 1) {
            // Decrease the quantity by 1
            $new_quantity = $current_quantity - 1;
            $updateQuery = "UPDATE CUSTOMER_CART SET QUANTITY = ? WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
            $pdo->prepare($updateQuery)->execute([$new_quantity, $customer_id, $remove_item_id]);
        } else {
            // If quantity is 1, remove the item completely
            $deleteQuery = "DELETE FROM CUSTOMER_CART WHERE CUSTOMER_ID = ? AND ITEM_ID = ?";
            $pdo->prepare($deleteQuery)->execute([$customer_id, $remove_item_id]);
        }
    }

    header("Location: cart.php");
    exit();
}

// Fetch cart items for the logged-in user
$query = "SELECT ITEM_ID, NAME, PRICE, QUANTITY FROM CUSTOMER_CART WHERE CUSTOMER_ID = ?";
$params = [$customer_id];
$stmt = $pdo->prepare($query);
$stmt->execute($params);

// Calculate total price
$totalPrice = 0;
$itemCount = 0;  // Variable to count the number of items
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $totalPrice += $row['PRICE'] * $row['QUANTITY'];
    $itemCount += $row['QUANTITY'];  // Add quantity to the item count
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="Cart.css">
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <img src="../Assets/kape.png" alt="SmartBrew Logo">
            <div class="logo-text">SmartBrew Cafè</div>
        </div>
        <div class="nav-links">
            <form method="GET" action="DashboardSB.php">
                <button type="submit" class="active">Dashboard</button>
            </form>
            <form method="GET" action="QR.php">
                <button type="submit" class="active">QR</button>
            </form>
            <form method="GET" action="Menu.php">
                <button type="submit">Menu</button>
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

    <div class="cartarea">
        <h1>Your Cart</h1>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th></th>
            </tr>
            <?php
            // Fetch cart items again after calculating total price
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['NAME']) ?></td>
                    <td>₱<?= number_format($row['PRICE'], 2) ?></td>
                    <td><?= $row['QUANTITY'] ?></td>
                    <td>
                        <form method="POST" action="cart.php" style="display:inline;">
                            <input type="hidden" name="remove_item_id" value="<?= $row['ITEM_ID'] ?>">
                            <button type="submit" class="remove-btn">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <p><strong>Total Price: ₱<?= number_format($totalPrice, 2) ?></strong></p>

        <?php if ($itemCount > 0): ?>  <!-- Show buttons only if there are items in the cart -->
            <div class="button-container">
                <form action="checkout.php" method="GET">
                    <button type="submit" class="checkout-btn">Proceed to Checkout >></button>
                </form>
            </div>

            <div class="continue-shopping-container">
                <form action="Menu.php" method="GET">
                    <button type="submit" class="continue-shopping-btn"><< Continue Shopping</button>
                </form>
            </div>
        <?php else: ?>  <!-- If there are no items, show "Go back to Menu" -->
            <div class="go-back-container">
                <form action="Menu.php" method="GET">
                    <button type="submit" class="go-back-btn"><< Menu</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
