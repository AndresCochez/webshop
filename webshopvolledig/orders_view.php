<?php
session_start();
include 'db.php'; // Zorg ervoor dat je db.php in dezelfde map hebt staan of pas het pad aan

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Haal de bestellingen van de ingelogde gebruiker op
$query = $pdo->prepare("
    SELECT o.id AS order_id, o.created_at, SUM(oi.quantity * p.price) AS total_price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id, o.created_at
    ORDER BY o.created_at DESC
");
$query->execute([$userId]);
$orders = $query->fetchAll(PDO::FETCH_ASSOC);

// Haal productinformatie op
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Haal gebruikersinformatie op als de gebruiker is ingelogd
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Bestellingen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigatie -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Webshop</a>
            <div class="ms-auto d-flex align-items-center">
                <?php if ($user): ?>
                    <!-- Dropdown-menu voor ingelogde gebruikers -->
                    <div class="dropdown me-3">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Welcome, <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item">Balance: €<?php echo number_format($user['balance'], 2); ?></a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="orders_view.php" class="btn btn-outline-light me-3">Mijn Bestellingen</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Login/Register knoppen voor niet-ingelogde gebruikers -->
                    <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                <?php endif; ?>

                <!-- Winkelmandje -->
                <a href="cart_view.php" class="btn btn-warning">
                    Winkelmandje (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>)
                </a>
            </div>
        </div>
    </nav>

    <!-- Modals -->

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="change_password.php">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="newPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h1>Mijn Bestellingen</h1>

        <?php if (!empty($orders)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Ordernummer</th>
                        <th>Datum</th>
                        <th>Totaalprijs</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td>€ <?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                                    Bekijk Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>U heeft nog geen bestellingen geplaatst.</p>
        <?php endif; ?>
    </div>
</body>
</html>