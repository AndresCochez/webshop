<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmandje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <?php if (!empty($_SESSION['cart'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Prijs</th>
                        <th>Aantal</th>
                        <th>Subtotaal</th>
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
                            <td><?php echo $item['quantity']; ?></td>
                            <td>€ <?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Totaal: € <?php echo number_format($total, 2); ?></h3>
            <form method="post" action="place_order.php">
                <button type="submit" class="btn btn-primary">Bestelling Plaatsen</button>
            </form>
        <?php else: ?>
            <p>Uw winkelmandje is leeg.</p>
        <?php endif; ?>
    </div>
</body>
</html>