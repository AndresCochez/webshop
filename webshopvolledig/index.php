<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Navigatie -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Webshop</a>
            <div class="ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    // Haal gebruikersinformatie op uit de database
                    $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    ?>
                    <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <span class="text-white me-3">Balance: <?php echo $user['balance']; ?> units</span>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                <?php else: ?>
                    <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    <button class="btn btn-outline-light ms-2" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hoofdinhoud -->
    <div class="container mt-5">
        <h1>Welcome to the Webshop</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Start shopping with your balance!</p>

            <!-- Wachtwoord wijzigen -->
            <div class="mt-5">
                <h2>Change Password</h2>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];

                    // Haal de gebruiker op uit de database
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user_password = $stmt->fetchColumn();

                    // Controleer het huidige wachtwoord
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
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        <?php else: ?>
            <p>Browse and shop your favorite items!</p>
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
</body>
</html>