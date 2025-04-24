<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /php/account.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

// Получаем список заказов пользователя
$orders_query = "SELECT o.id, o.order_date, o.status, SUM(oi.quantity * oi.price) as total
                 FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = ?
                 GROUP BY o.id
                 ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($orders_query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $pdo->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Если передан order_id, получаем детали конкретного заказа
$order_details = [];
$order_total = 0;
if ($order_id) {
    // Проверяем, что заказ принадлежит пользователю
    $check_order_query = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($check_order_query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        die("Заказ не найден или не принадлежит вам");
    }

    $details_query = "SELECT p.name, oi.quantity, oi.price, (oi.quantity * oi.price) as subtotal
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
    $stmt = $pdo->prepare($details_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Считаем общую сумму заказа
    foreach ($order_details as $item) {
        $order_total += $item['subtotal'];
    }
}
?>

<!-- Остальная часть HTML остается без изменений -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы</title>
    <link rel="stylesheet" href="../css/styleszakaz.css">
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
        <h1>Мои заказы</h1>
        
        <?php if ($order_id && isset($_GET['order_success'])): ?>
            <div class="success-message">
                Ваш заказ #<?= $order_id ?> успешно оформлен!
            </div>
        <?php endif; ?>

        <div class="orders-container">
            <div class="orders-list">
                <h2>История заказов</h2>
                <?php if (empty($orders)): ?>
                    <p>У вас пока нет заказов</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($orders as $order): ?>
                            <li class="<?= $order['id'] == $order_id ? 'active' : '' ?>">
                                <a href="zakaz.php?order_id=<?= $order['id'] ?>">
                                    Заказ #<?= $order['id'] ?> - 
                                    <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?> - 
                                    <?= $order['total'] ?> руб.
                                    <span class="status-badge <?= $order['status'] ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php if ($order_id && !empty($order_details)): ?>
                <div class="order-details">
                    <h2>Детали заказа #<?= $order_id ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Количество</th>
                                <th>Цена</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_details as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= $item['price'] ?> руб.</td>
                                    <td><?= $item['subtotal'] ?> руб.</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="3"><strong>Итого:</strong></td>
                                <td><strong><?= array_sum(array_column($order_details, 'subtotal')) ?> руб.</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Магазин. Все права защищены.</p>
    </footer>
</body>
</html>