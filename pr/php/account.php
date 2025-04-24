<?php
session_start();
require '../db.php';

// Проверяем авторизацию или создаем гостевую сессию
if (!isset($_SESSION['user_id'])) {
    $_SESSION['guest'] = true;
    $_SESSION['user_name'] = 'Гость';
}

// Получаем данные пользователя, если авторизован
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = $pdo->query("SELECT * FROM users WHERE id = $user_id");
    $user = $result->fetch_assoc();
}

// Выход из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="../css/stylesacc.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/logo.png" alt="Логотип магазина">
        </div>
        <nav>
            <ul>
                <li><a href="../index.html">Главная</a></li>
                <li><a href="../php/products.php">Товары</a></li>
                <li><a href="../html/set_of_products.html">Корзина</a></li>
                <li><a href="account.php" id="account-link">Личный кабинет</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="account.php?logout=1" id="logout-link">Выйти</a></li>
                <?php else: ?>
                    <li><a href="login.php" id="login-link">Войти</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Личный кабинет</h1>
        
        <?php if (isset($_SESSION['guest'])): ?>
            <?php 
            if (!isset ($user)) 
            {echo '<div class="guest-warning">
                <h3>Вы вошли как гость</h3>
                <p>Для доступа ко всем функциям <a href="login.php">войдите</a> или <a href="registration.php">зарегистрируйтесь</a>.</p>
            </div>';}
            ?>
        <?php endif; ?>
        
        <div class="account-info">
            <p><strong>Имя:</strong> 
                <span id="user-name">
                    <?= htmlspecialchars(isset($user) ? $user['name'] : $_SESSION['user_name']) ?>
                </span>
            </p>
            
            <?php if (isset($user)): ?>
                <p><strong>Email:</strong> 
                    <span id="user-email"><?= htmlspecialchars($user['email']) ?></span>
                </p>
                <p><strong>Дата регистрации:</strong> 
                    <span id="user-reg-date"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                </p>
            <?php else: ?>
                <p>Для просмотра полной информации требуется авторизация.</p>
                <div class="auth-links">
                    <a href="login.php" class="login-btn">Войти</a>
                    <a href="registration.php" class="register-btn">Зарегистрироваться</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Магазин. Все права защищены.</p>
    </footer>
</body>
</html>