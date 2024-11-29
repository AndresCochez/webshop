<?php
session_start();
include 'db.php'; // Zorg ervoor dat je db.php in dezelfde map hebt staan of pas het pad aan

// Verwijder een product uit het winkelmandje
if (isset($_POST['remove_item'])) {
    $productId = intval($_POST['product_id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $productId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Herindexeer array
}

// Initialiseer meldingen
$successMessage = "";
$errorMessage = "";

// Plaats bestelling
if (isset($_POST['place_order'])) {
    $userId = $_SESSION['user_id']; // Zorg dat je de gebruiker-id opslaat in de sessie
    $total = 0;

    // Bereken het totaalbedrag van de bestelling
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Haal het huidige saldo van de gebruiker op
    $query = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $query->execute([$userId]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $currentBalance = $user['balance'];

        // Controleer of de gebruiker voldoende saldo heeft
        if ($currentBalance >= $total) {
            // Trek het bedrag af van het saldo
            $newBalance = $currentBalance - $total;
            $updateQuery = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $updateQuery->execute([$newBalance, $userId]);

            // Plaats de bestelling
            $orderQuery = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
            $orderQuery->execute([$userId]);
            $orderId = $pdo->lastInsertId();

            // Voeg de items toe aan de order_items-tabel
            foreach ($_SESSION['cart'] as $item) {
                $orderItemQuery = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
                $orderItemQuery->execute([$orderId, $item['id'], $item['quantity']]);
            }

            // Leeg het winkelmandje
            $_SESSION['cart'] = [];
            $successMessage = "Uw bestelling is succesvol geplaatst! Uw nieuwe saldo is: € " . number_format($newBalance, 2);
        } else {
            $errorMessage = "Onvoldoende saldo om de bestelling te plaatsen. Uw huidige saldo is: € " . number_format($currentBalance, 2);
        }
    } else {
        $errorMessage = "Gebruiker niet gevonden.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmandje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Navigatie -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Webshop</a>
            <div class="ms-auto">
                <a href="cart_view.php" class="btn btn-warning">
                    Winkelmandje (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>)
                </a>
            </div>
        </div>
    </nav>

    <!-- Winkelmandje -->
    <div class="container mt-5">
        <h1>Winkelmandje</h1>

        <!-- Meldingen -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['cart'])): ?>
            <form method="post">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Prijs</th>
                            <th>Aantal</th>
                            <th>Subtotaal</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item):
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td>€ <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td>€ <?php echo number_format($subtotal, 2); ?></td>
                                <td>
                                    <form method="post" style="display: inline-block;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger">Verwijderen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h3>Totaal: € <?php echo number_format($total, 2); ?></h3>
                <button type="submit" name="place_order" class="btn btn-success">Bestelling Plaatsen</button>
            </form>
        <?php else: ?>
            <p>Uw winkelmandje is leeg.</p>
        <?php endif; ?>
    </div>
</body>
</html>