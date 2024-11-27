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
    $params[] = $selectedCategory;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Zoek- en sorteerbalk -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="get">
            <div class="input-group">
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php
                    $categories = $pdo->query("SELECT id, name FROM categories");
                    while ($category = $categories->fetch()) {
                        $selected = $selectedCategory == $category['id'] ? "selected" : "";
                        echo "<option value='{$category['id']}' $selected>{$category['name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <form method="get">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by title or description" value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Product overview -->
<div class="row">
    <?php
    if ($products) {
        foreach ($products as $product) {
            echo "
            <div class='col-md-4 mb-3'>
                <div class='card'>
                    <a href='product_details.php?id={$product['id']}' style='text-decoration: none; color: inherit;'>
                        <div class='image-wrapper' style='height: 200px; overflow: hidden;'>
                            <img src='{$product['image']}' class='card-img-top' alt='{$product['title']}' style='width: 100%; height: 100%; object-fit: cover;'>
                        </div>
                        <div class='card-body'>
                            <h5 class='card-title'>{$product['title']}</h5>
                            <p class='card-text'>{$product['description']}</p>
                            <p class='card-text'><strong>Price:</strong> € {$product['price']}</p>
                            <p class='card-text'><small class='text-muted'>Category: {$product['category_name']}</small></p>
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