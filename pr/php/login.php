<?php
session_start();
require '../db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $pdo->real_escape_string(trim($_POST['email']));
    $password = trim($_POST['password']);

    $result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: account.php');
            exit;
        }
    }
    $error = 'Неверный email или пароль';
}
?>

<!-- HTML-форма остается без изменений -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="../css/styleslog.css">
</head>
<body>
    <div class="login-container">
        <h1>Вход</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Войти</button>
        </form>
        <p>Ещё нет аккаунта? <a href="registration.php">Зарегистрируйтесь</a></p>
    </div>
</body>
</html>