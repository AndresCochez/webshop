<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starcoffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Navigatie -->
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            // Controleer de gebruikersrol
            $stmt = $pdo->prepare("SELECT username, is_admin FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user['username'] === 'admin' || $user['is_admin'] == 1): ?>
                <!-- Admin Navigatie -->
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: -50px;">
                    <div class="container">
                        <a class="navbar-brand" href="#">Starcoffee</a>
                        <div class="ms-auto">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php
                                // Haal gebruikersinformatie op uit de database
                                $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $user = $stmt->fetch();
                                ?>
                                <!-- Dropdown-menu -->
                                <div class="dropdown">
                                    <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Welcome, <?php echo htmlspecialchars($user['username']); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
                                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                                <button class="btn btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            <?php else: ?>
                <!-- User Navigatie -->
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: -50px;">
                    <div class="container">
                        <a class="navbar-brand" href="index.php">Starcoffee</a>
                        <div class="ms-auto d-flex align-items-center">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php
                                // Haal gebruikersinformatie op
                                $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
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
                                Cart (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>)
                            </a>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <!-- Standaard inhoud voor niet-ingelogde gebruikers -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: -50px;">
                    <div class="container">
                        <a class="navbar-brand" href="#">Starcoffee</a>
                        <div class="ms-auto">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php
                                // Haal gebruikersinformatie op uit de database
                                $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $user = $stmt->fetch();
                                ?>
                                <!-- Dropdown-menu -->
                                <div class="dropdown">
                                    <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Welcome, <?php echo htmlspecialchars($user['username']); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
                                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                                <button class="btn btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
        <?php endif; ?>
    </div>

    <!-- Hoofdinhoud -->
    <div class="container mt-5">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            // Controleer de gebruikersrol
            $stmt = $pdo->prepare("SELECT username, is_admin FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user['username'] === 'admin' || $user['is_admin'] == 1): ?>
                <!-- Admin Dashboard -->
                <?php include 'admin_dashboard.php'; ?>
            <?php else: ?>
                <!-- User Dashboard -->
                <?php include 'user_dashboard.php'; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Standaard inhoud voor niet-ingelogde gebruikers -->
            <section id="home">
                <div class="content">
                    <img src="images/banner.png" alt="">
                    <br>
                </div>
            </section>

            <section id="portfolio">
                <div class="container">
                    <div class="portfolio-grid">
                        <div class="portfolio-item">
                            <a>
                                <img src="images/cold.png" alt="Project 1">
                                <p id="products">Cold Drinks</p>
                            </a>
                        </div>
                        <div class="portfolio-item">
                            <a>
                                <img src="images/hot.png" alt="Project 2">
                                <p id="products" >Hot Drinks</p>
                            </a>
                        </div>
                        <div class="portfolio-item">
                            <a>
                                <img src="images/food.png" alt="Project 3">
                                <p id="products">Food</p>
                            </a>
                        </div>
                    </div>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#loginModal">Discover our products</button>
                </div>
                <br>
                <br>
            </section>

            <section id="about">
                <br>
                <br>
                <div class="container">
                    <h1>Welcome to Starcoffee</h1>
                    <br>
                    <p>Every cup of coffee begins with a story. Journey back to <span class="highlight">Ethiopia</span>, the birthplace of coffee, where legend has it a goat herder named <span class="highlight">Kaldi</span> discovered the magical red cherries. This remarkable discovery spread quickly, making coffee a symbol of warmth, connection, and inspiration across cultures.</p>
                    <br>
                    <p>We source the finest beans from around the globe: the sun-kissed fields of <span class="highlight">Colombia</span>, the lush highlands of <span class="highlight">Guatemala</span>, and the fertile volcanic soils of <span class="highlight">Indonesia</span>. Each bean is handpicked, carefully processed, and roasted with passion to preserve its unique flavors and aromas.</p>
                </div>
                <br>
                <br>
            </section>

            <footer>
                <div class="container">
                    <p>&copy; 2024 Andres Cochez</p>
                    <link rel="stylesheet" href="start.css">
                </div>
            </footer>
        <?php endif; ?>
    </div>


    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
                        $username = $_POST['username'];
                        $password = $_POST['password'];

                        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                        $stmt->execute([$username]);
                        $user = $stmt->fetch();

                        if ($user && password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['is_admin'] = $user['is_admin'];
                            echo "<script>alert('Login successful!'); window.location='index.php';</script>";
                        } else {
                            echo "<div class='alert alert-danger'>Invalid username or password!</div>";
                        }
                    }
                    ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Username</label>
                            <input type="text" name="username" id="loginUsername" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" name="password" id="loginPassword" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
                        $username = $_POST['username'];
                        $email = $_POST['email'];
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$username, $email]);

                        if ($stmt->rowCount() > 0) {
                            echo "<div class='alert alert-danger'>Username or email already exists!</div>";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, balance) VALUES (?, ?, ?, 1000)");
                            $stmt->execute([$username, $email, $password]);
                            echo "<script>alert('Registration successful! You have been credited with 1,000 units.'); window.location='index.php';</script>";
                        }
                    }
                    ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Username</label>
                            <input type="text" name="username" id="registerUsername" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" name="email" id="registerEmail" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" name="password" id="registerPassword" class="form-control" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
                        $current_password = $_POST['current_password'];
                        $new_password = $_POST['new_password'];
                        $confirm_password = $_POST['confirm_password'];

                        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user_password = $stmt->fetchColumn();

                        if ($user_password && password_verify($current_password, $user_password)) {
                            if ($new_password === $confirm_password) {
                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                                $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                                echo "<div class='alert alert-success'>Password successfully changed!</div>";
                            } else {
                                echo "<div class='alert alert-danger'>New passwords do not match!</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Current password is incorrect!</div>";
                        }
                    }
                    ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="newPassword" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary w-100">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>