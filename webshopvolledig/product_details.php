<?php
session_start();
require_once 'db.php';
require_once 'Classes/User_header.php'; // Importeer de UserHeader-klasse

// Haal productinformatie op
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($productId > 0) {
    // Gebruik de product_details-view voor eenvoudiger ophalen van gegevens
    $stmt = $pdo->prepare("SELECT * FROM product_details WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Haal gebruikersinformatie op als de gebruiker is ingelogd
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Bereken aantal items in het winkelmandje
$cartItemsCount = isset($_SESSION['cart']) ? array_sum(array_map(fn($item) => intval($item['quantity']), $_SESSION['cart'])) : 0;

// Initialiseer de UserHeader
$userHeader = new UserHeader($user, $cartItemsCount);

// Haal reviews op voor dit product
$reviews = [];
if ($product) {
    $stmt = $pdo->prepare("
        SELECT r.rating, r.comment, r.created_at, u.username 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title'] ?? 'Productdetails', ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="background.css">
</head>
<body>
    <!-- Gebruik UserHeader -->
    <?php $userHeader->render(); ?>

    <!-- Productdetails -->
    <div id="detailss" class="container mt-5">
        <?php if ($product): ?>
            <h1><?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 100%; height: auto;">
            <p><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Prijs:</strong> € <?php echo number_format(floatval($product['price']), 2); ?></p>
            <p><strong>Categorie:</strong> <?php echo htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <form method="post" action="cart.php">
                <input type="hidden" name="product_id" value="<?php echo intval($product['id']); ?>">
                <input type="hidden" name="product_title" value="<?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="product_price" value="<?php echo floatval($product['price']); ?>">
                <button type="submit" name="add_to_cart" class="btn btn-success">Toevoegen aan winkelmandje</button>
            </form>

            <hr>
            
            <h2>Reviews</h2>
            <?php if ($reviews): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="mb-3">
                        <strong><?php echo htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8'); ?></strong> 
                        <span>(<?php echo intval($review['rating']); ?>/5)</span>
                        <p><?php echo htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <small class="text-muted">Op <?php echo htmlspecialchars(date('d-m-Y', strtotime($review['created_at'])), ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Er zijn nog geen reviews voor dit product.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Product niet gevonden.</p>
        <?php endif; ?>
    </div>
</body>
</html>