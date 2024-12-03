<?php
session_start();
include 'db.php';

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$userId = $_SESSION['user_id'];

// Haal de details van de bestelling op
$query = $pdo->prepare("
    SELECT p.id AS product_id, p.title, p.price, oi.quantity, (oi.quantity * p.price) AS subtotal
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ? AND EXISTS (
        SELECT 1 FROM orders o WHERE o.id = oi.order_id AND o.user_id = ?
    )
");
$query->execute([$orderId, $userId]);
$orderItems = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$orderItems) {
    echo "Bestelling niet gevonden of u heeft geen toegang.";
    exit();
}

// Beoordeling toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $review = trim($_POST['comment']);
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];

    // Controleer of de gebruiker dit product heeft gekocht
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = ? AND oi.order_id = ? AND o.user_id = ?
    ");
    $stmt->execute([$productId, $orderId, $userId]);

    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $productId, $rating, $review]);
        $message = "Review succesvol toegevoegd!";
    } else {
        $message = "Je kunt alleen producten beoordelen die je hebt gekocht.";
    }
}

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
    <title>Details van Bestelling #<?php echo $orderId; ?></title>
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

    <div class="container mt-5">
        <h1>Details van Bestelling #<?php echo $orderId; ?></h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

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
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td>€ <?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>€ <?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button 
            class="btn btn-primary" 
            data-bs-toggle="modal" 
            data-bs-target="#reviewModal" 
            data-product-id="<?php echo $item['product_id']; ?>" 
            data-product-title="<?php echo htmlspecialchars($item['title']); ?>">
            Beoordeling toevoegen
        </button>
        <a href="orders_view.php" class="btn btn-secondary">Terug naar Bestellingen</a>
    </div>

    <!-- Beoordeling Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Beoordeling toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="modalProductId">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (1-5)</label>
                            <select name="rating" id="rating" class="form-select" required>
                                <option value="">Selecteer een rating</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Recensie</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit_review" class="btn btn-primary">Beoordeling indienen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const reviewModal = document.getElementById('reviewModal');
        reviewModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-product-id');
            const productTitle = button.getAttribute('data-product-title');
            
            const modalTitle = reviewModal.querySelector('.modal-title');
            const modalProductId = reviewModal.querySelector('#modalProductId');

            modalTitle.textContent = `Beoordeling toevoegen voor ${productTitle}`;
            modalProductId.value = productId;
        });
    </script>
</body>
</html>
