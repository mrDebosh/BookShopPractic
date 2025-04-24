<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $pdo->real_escape_string(trim($_POST['name']));
    $email = $pdo->real_escape_string(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);

    $errors = [];
    
    // Валидация
    if (empty($name)) $errors['name'] = 'Имя обязательно';
    if (empty($email)) $errors['email'] = 'Email обязателен';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный email';
    if (empty($password)) $errors['password'] = 'Пароль обязателен';
    elseif (strlen($password) < 6) $errors['password'] = 'Пароль должен быть не менее 6 символов';
    elseif ($password !== $confirm_password) $errors['confirm-password'] = 'Пароли не совпадают';

    // Проверка уникальности email
    if (!isset($errors['email'])) {
        $result = $pdo->query("SELECT id FROM users WHERE email = '$email'");
        if ($result->num_rows > 0) {
            $errors['email'] = 'Пользователь с таким email уже существует';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
        
        if ($pdo->query($sql)) {
            $_SESSION['success_message'] = "Регистрация прошла успешно!";
            header('Location: login.php');
            exit;
        } else {
            $errors['database'] = "Ошибка регистрации: " . $pdo->error;
        }
    }
}
?>

<!-- HTML-форма остается без изменений -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../css/stylesreg.css">
</head>
<body>
    <div class="registration-container">
        <h1>Регистрация</h1>
        
        <!-- Вывод сообщений об ошибках -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Вывод сообщения об успехе -->
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form id="registration-form" method="POST" action="registration.php">
            <!-- Поле для имени (теперь первое) -->
            <div class="form-group">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                <span class="error-message">
                    <?php echo isset($errors['name']) ? $errors['name'] : ''; ?>
                </span>
            </div>

            <!-- Поле для email (теперь второе) -->
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <span class="error-message">
                    <?php echo isset($errors['email']) ? $errors['email'] : ''; ?>
                </span>
            </div>

            <!-- Остальные поля без изменений -->
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
                <span class="error-message">
                    <?php echo isset($errors['password']) ? $errors['password'] : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label for="confirm-password">Повторите пароль:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                <span class="error-message">
                    <?php echo isset($errors['confirm-password']) ? $errors['confirm-password'] : ''; ?>
                </span>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="agree" name="agree" required
                           <?php echo isset($_POST['agree']) ? 'checked' : ''; ?>>
                    Я согласен на обработку персональных данных
                </label>
                <span class="error-message">
                    <?php echo isset($errors['agree']) ? $errors['agree'] : ''; ?>
                </span>
            </div>

            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</body>
</html>