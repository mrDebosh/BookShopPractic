<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/account.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    
    // Проверяем, есть ли уже товар в корзине
    $check_stmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
    $check_stmt->bind_param('ii', $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Увеличиваем количество, если товар уже есть
        $update_stmt = $pdo->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
        $update_stmt->bind_param('ii', $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // Добавляем новый товар
        $insert_stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)');
        $insert_stmt->bind_param('ii', $user_id, $product_id);
        $insert_stmt->execute();
    }
    
    header('Location: products.php');
    exit();
}
?>