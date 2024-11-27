<?php
// Connect to the database
// (Zorg ervoor dat $pdo correct is geconfigureerd voor je database)

$selectedCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Haal de categorieën op uit de database
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Bereid de query voor om producten te filteren
$query = "SELECT p.id, p.title, p.description, p.price, p.image, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id";
$params = [];

if ($selectedCategory) {
    $query .= " WHERE p.category_id = ?";
    $params[] = $selectedCategory;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Product Overview</h2>

        <!-- Filter Form -->
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Filter by Category</label>
                    <select name="category_id" id="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $selectedCategory == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Productenlijst -->
        <div class="row">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['title']) ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="card-text"><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                                <p class="card-text"><small class="text-muted">Category: <?= htmlspecialchars($product['category_name']) ?></small></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted">No products found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>