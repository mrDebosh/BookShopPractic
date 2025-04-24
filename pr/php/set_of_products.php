<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /php/account.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Обработка действий с корзиной
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($product_id && $action) {
        // Проверяем, что product_id - число
        $product_id = (int)$product_id;
        
        if ($action === 'increase' || $action === 'decrease' || $action === 'remove') {
            $check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $pdo->prepare($check_query);
            if (!$stmt) {
                die("Ошибка подготовки запроса: " . $pdo->error);
            }
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();

            if ($action === 'increase') {
                if ($item) {
                    $new_quantity = $item['quantity'] + 1;
                    $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                } else {
                    $new_quantity = 1;
                    $update_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                }
                $stmt = $pdo->prepare($update_query);
                $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                $stmt->execute();
            }
            elseif ($action === 'decrease' && $item) {
                if ($item['quantity'] > 1) {
                    $new_quantity = $item['quantity'] - 1;
                    $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                    $stmt = $pdo->prepare($update_query);
                    $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                    $stmt->execute();
                } else {
                    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                    $stmt = $pdo->prepare($delete_query);
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                }
            }
            elseif ($action === 'remove') {
                $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = $pdo->prepare($delete_query);
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
            }
        }
        

    }
    elseif ($action === 'checkout') {
            
        // 1. Создаем заказ
        $order_query = "INSERT INTO orders (user_id, order_date, status) VALUES (?, NOW(), 'processing')";
        $stmt = $pdo->prepare($order_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $order_id = $pdo->insert_id;
        
        // 2. Переносим товары
        $transfer_query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                         SELECT ?, product_id, quantity, (SELECT price FROM products WHERE id = product_id)
                         FROM cart WHERE user_id = ?";
        $stmt = $pdo->prepare($transfer_query);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        
        // 3. Очищаем корзину
        $clear_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $pdo->prepare($clear_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Перенаправление
        header("Location: zakaz.php?order_id=".$order_id."&order_success=1");
        exit();
    }
}

// Получаем товары в корзине
$cart_query = "SELECT p.id, p.name, p.price, p.image, c.quantity 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt = $pdo->prepare($cart_query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $pdo->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>


<!-- Остальная часть HTML остается без изменений -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="../css/stylesbox.css">
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
                <li><a href="set_of_products.php">Корзина</a></li>
                <li><a href="zakaz.php">Мои заказы</a></li>
                <li><a href="../html/account.html">Личный кабинет</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Ваша корзина</h1>
        
        <div class="cart-items">
            <?php if (empty($cart_items)): ?>
                <p>Ваша корзина пуста</p>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="../images/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="cart-item-info">
                            <h2><?= htmlspecialchars($item['name']) ?></h2>
                            <p>Цена: <?= htmlspecialchars($item['price']) ?> руб.</p>
                            <p>Количество: <?= htmlspecialchars($item['quantity']) ?></p>
                        </div>
                        <div class="cart-item-actions">
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit">−</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit">+</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit">Удалить</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-summary">
                <p><strong>Итого к оплате:</strong> <?= $total_price ?> руб.</p>
                <form method="POST" >
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="checkout-button">Оформить заказ</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Магазин. Все права защищены.</p>
    </footer>

</body>
</html>