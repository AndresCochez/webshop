<?php
// Controleer of er een zoekopdracht of categorie is ingesteld
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Bouw de query op basis van zoekopdracht en categorie
$query = "SELECT p.id, p.title, p.description, p.price, p.image, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

$params = [];
if (!empty($searchQuery)) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
}
if (!empty($selectedCategory) && is_numeric($selectedCategory)) {
    $query .= " AND p.category_id = ?";
    $params[] = intval($selectedCategory); // Zorg dat category_id een integer is
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="userscherm">
<h1>Products</h1>
<!-- Zoek- en sorteerbalk -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="get">
            <div class="input-group">
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php
                    $categories = $pdo->query("SELECT id, name FROM categories");
                    while ($category = $categories->fetch(PDO::FETCH_ASSOC)) {
                        $selected = intval($selectedCategory) === intval($category['id']) ? "selected" : "";
                        echo "<option value='" . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <form method="get">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by title or description" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="background.css">

<!-- Product overview -->
<div class="row">
    <?php
    if ($products) {
        foreach ($products as $product) {
            echo "
            <div class='col-md-4 mb-3'>
                <div class='card'>
                    <a href='product_details.php?id=" . intval($product['id']) . "' style='text-decoration: none; color: inherit;'>
                        <div class='image-wrapper' style='height: 200px; overflow: hidden;'>
                            <img src='" . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') . "' class='card-img-top' alt='" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "' style='width: 100%; height: 100%; object-fit: cover;'>
                        </div>
                        <div class='card-body'>
                            <h5 class='card-title'>" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "</h5>
                            <p class='card-text'>" . htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') . "</p>
                            <p class='card-text'><strong>Price:</strong> € " . number_format(floatval($product['price']), 2) . "</p>
                            <p class='card-text'><small class='text-muted'>Category: " . htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') . "</small></p>
                        </div>
                    </a>
                </div>
            </div>";
        }
    } else {
        echo "<div class='col-12'><p class='text-center'>No products found.</p></div>";
    }
    ?>
</div>
</div>