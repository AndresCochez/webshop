<?php
// Databaseverbinding instellen
try {
    $pdo = new PDO("mysql:host=localhost;dbname=webshop", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

// Product toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image'];

    $errors = [];
    if (empty($title)) {
        $errors[] = "Title cannot be empty.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a non-negative number.";
    }
    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = "A valid category must be selected.";
    }
    if (!is_uploaded_file($image['tmp_name'])) {
        $errors[] = "An image is required.";
    } else {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (title, description, price, image, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $price, $imagePath, $category_id]);
            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error adding product: " . $e->getMessage() . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}

// Product bewerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image'];

    $errors = [];
    if (empty($title)) {
        $errors[] = "Title cannot be empty.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a non-negative number.";
    }
    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = "A valid category must be selected.";
    }

    $imagePath = null;
    if (is_uploaded_file($image['tmp_name'])) {
        $uploadDir = 'uploads/';
        $imagePath = $uploadDir . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    if (empty($errors)) {
        try {
            $query = "UPDATE products SET title = ?, description = ?, price = ?, category_id = ?";
            $params = [$title, $description, $price, $category_id];
            if ($imagePath) {
                $query .= ", image = ?";
                $params[] = $imagePath;
            }
            $query .= " WHERE id = ?";
            $params[] = $product_id;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            echo "<div class='alert alert-success'>Product updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error updating product: " . $e->getMessage() . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <!-- Knop om de Add Product Modal te openen -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
        Add Product
    </button>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="" disabled selected>Select a category</option>
                                <?php
                                $categories = $pdo->query("SELECT id, name FROM categories");
                                while ($category = $categories->fetch()) {
                                    echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Overzicht -->
    <h2>Product Overview</h2>
    <div class="row">
        <?php
        $stmt = $pdo->query("SELECT p.id, p.title, p.description, p.price, p.image, c.name AS category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id");
        while ($product = $stmt->fetch()) {
            echo "
            <div class='col-md-4 mb-3'>
                <div class='card'>
                    <img src='{$product['image']}' class='card-img-top' alt='{$product['title']}' style='height: 200px; object-fit: cover;'>
                    <div class='card-body'>
                        <h5 class='card-title'>{$product['title']}</h5>
                        <p class='card-text'>{$product['description']}</p>
                        <p class='card-text'><strong>Price:</strong> $ {$product['price']}</p>
                        <p class='card-text'><small class='text-muted'>Category: {$product['category_name']}</small></p>
                        <button type='button' class='btn btn-secondary' data-bs-toggle='modal' data-bs-target='#editProductModal{$product['id']}'>
                            Edit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div class='modal fade' id='editProductModal{$product['id']}' tabindex='-1' aria-labelledby='editProductModalLabel{$product['id']}' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='editProductModalLabel{$product['id']}'>Edit Product</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <form method='post' enctype='multipart/form-data'>
                            <input type='hidden' name='product_id' value='{$product['id']}'>
                            <div class='modal-body'>
                                <div class='mb-3'>
                                    <label for='title{$product['id']}' class='form-label'>Title</label>
                                    <input type='text' name='title' id='title{$product['id']}' class='form-control' value='{$product['title']}' required>
                                </div>
                                <div class='mb-3'>
                                    <label for='description{$product['id']}' class='form-label'>Description</label>
                                    <textarea name='description' id='description{$product['id']}' class='form-control' rows='4'>{$product['description']}</textarea>
                                </div>
                                <div class='mb-3'>
                                    <label for='price{$product['id']}' class='form-label'>Price</label>
                                    <input type='number' name='price' id='price{$product['id']}' class='form-control' step='0.01' min='0' value='{$product['price']}' required>
                                </div>
                                <div class='mb-3'>
                                    <label for='category_id{$product['id']}' class='form-label'>Category</label>
                                    <select name='category_id' id='category_id{$product['id']}' class='form-control' required>
                                        <option value='' disabled>Select a category</option>";
            $categories = $pdo->query("SELECT id, name FROM categories");
            while ($category = $categories->fetch()) {
                $selected = $category['id'] == $product['category_id'] ? "selected" : "";
                echo "<option value='{$category['id']}' $selected>{$category['name']}</option>";
            }
            echo "
                                    </select>
                                </div>
                                <div class='mb-3'>
                                    <label for='image{$product['id']}' class='form-label'>Image</label>
                                    <input type='file' name='image' id='image{$product['id']}' class='form-control' accept='image/*'>
                                    <small class='text-muted'>Leave empty to keep the current image.</small>
                                </div>
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                <button type='submit' name='edit_product' class='btn btn-primary'>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>
</body>
</html>