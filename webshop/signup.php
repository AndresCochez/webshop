<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Registreren</h1>
            <form action="signup.php" method="POST">
                <label for="name">Naam:</label>
                <input type="text" id="name" name="name" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required><br>

                <button type="submit">Registreren</button>
            </form>

            <p>Al een account? <a href="login.php">Log hier in</a>.</p>
        </div>
    </div>
</body>
</html>