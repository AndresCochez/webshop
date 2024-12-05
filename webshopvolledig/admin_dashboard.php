<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
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
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . '/' . htmlspecialchars(basename($image['name']), ENT_QUOTES, 'UTF-8');
        if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
            $errors[] = "Failed to upload the image.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (title, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $price, $category_id, $imagePath]);

            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error adding product: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}
?>

<!-- Knop voor het openen van de modal -->
<div class="text-end mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        Add New Product
    </button>
</div>

<!-- Modal voor het toevoegen van een product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
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
                        <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
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
                                echo "<option value='" . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . "</option>";
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

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = htmlspecialchars($_POST['delete_product_id'], ENT_QUOTES, 'UTF-8');
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error deleting product: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

// Bewerken van product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = htmlspecialchars($_POST['product_id'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
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
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . '/' . htmlspecialchars(basename($image['name']), ENT_QUOTES, 'UTF-8');
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
            echo "<div class='alert alert-danger'>Error updating product: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}
?>

<!-- Productenlijst met bewerken -->
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
                <img src='" . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') . "' class='card-img-top' alt='" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "' style='height: 200px; object-fit: cover;'>
                <div class='card-body'>
                    <h5 class='card-title'>" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "</h5>
                    <p class='card-text'>" . htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='card-text'><strong>Price:</strong> € " . htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='card-text'><small class='text-muted'>Category: " . htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') . "</small></p>
                    <button type='button' class='btn btn-secondary' data-bs-toggle='modal' data-bs-target='#editProductModal" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>
                        Edit
                    </button>
                    <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#deleteProductModal" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div class='modal fade' id='editProductModal" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' tabindex='-1' aria-labelledby='editProductModalLabel" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='editProductModalLabel" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>Edit Product</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <form method='post' enctype='multipart/form-data'>
                        <input type='hidden' name='product_id' value='" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>
                        <div class='modal-body'>
                            <div class='mb-3'>
                                <label for='title" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-label'>Title</label>
                                <input type='text' name='title' id='title" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-control' value='" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "' required>
                            </div>
                            <div class='mb-3'>
                                <label for='description" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-label'>Description</label>
                                <textarea name='description' id='description" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-control' rows='4'>" . htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') . "</textarea>
                            </div>
                            <div class='mb-3'>
                                <label for='price" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-label'>Price</label>
                                <input type='number' name='price' id='price" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-control' step='0.01' min='0' value='" . htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') . "' required>
                            </div>
                            <div class='mb-3'>
                                <label for='category_id" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-label'>Category</label>
                                <select name='category_id' id='category_id" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-control' required>
                                    <option value='' disabled>Select a category</option>";
        $categories = $pdo->query("SELECT id, name FROM categories");
        while ($category = $categories->fetch()) {
            $selected = $category['id'] == $product['category_id'] ? "selected" : "";
            echo "<option value='" . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . "' " . htmlspecialchars($selected, ENT_QUOTES, 'UTF-8') . ">" . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . "</option>";
        }
        echo "
                                </select>
                            </div>
                            <div class='mb-3'>
                                <label for='image" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-label'>Image</label>
                                <input type='file' name='image' id='image" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' class='form-control' accept='image/*'>
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
        </div>
        <!-- Delete Product Modal -->
        <div class='modal fade' id='deleteProductModal" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' tabindex='-1' aria-labelledby='deleteProductModalLabel" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='deleteProductModalLabel" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>Delete Product</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        Are you sure you want to delete the product <strong>" . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . "</strong>?
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <form method='post'>
                            <input type='hidden' name='delete_product_id' value='" . htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') . "'>
                            <button type='submit' name='delete_product' class='btn btn-danger'>Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>";
    }
    ?>
</div>