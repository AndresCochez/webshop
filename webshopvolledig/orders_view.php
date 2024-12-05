<?php
session_start();
include 'db.php'; 
include 'Classes/User_header.php'; // Importeer de UserHeader-klasse

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = intval($_SESSION['user_id']); // Zorg ervoor dat de user_id veilig wordt verwerkt

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

// Haal gebruikersinformatie op
$stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Bereken aantal items in het winkelmandje
$cartItemsCount = isset($_SESSION['cart']) ? array_sum(array_map(fn($item) => intval($item['quantity']), $_SESSION['cart'])) : 0;

// Initialiseer de UserHeader
$userHeader = new UserHeader($user, $cartItemsCount);
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
    <!-- Gebruik UserHeader -->
    <?php $userHeader->render(); ?>

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
                            <td><?php echo htmlspecialchars($order['order_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>€ <?php echo number_format(floatval($order['total_price']), 2); ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo intval($order['order_id']); ?>" class="btn btn-primary">
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