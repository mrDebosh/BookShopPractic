<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товары</title>
    <link rel="stylesheet" href="../css/stylesprod.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/logo.png" alt="Логотип магазина">
        </div>
        <nav>
            <ul>
                <li><a href="../index.html">Главная</a></li>
                <li><a href="products.php">Товары</a></li>
                <li><a href="set_of_products.php">Корзина</a></li>
                <li><a href="../html/account.html">Личный кабинет</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Наши товары</h1>
        <div class="product-grid">
            <?php
            require '../db.php';
            
            $stmt = $pdo->prepare('SELECT id, name, description, price, quantity, category_id, image FROM products');
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($product = $result->fetch_assoc()) {
                echo '<div class="product-card">';
                
                // Выводим изображение товара из BLOB-данных
                if (!empty($product['image'])) {
                    $imageInfo = getimagesizefromstring($product['image']);
                    $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/jpeg';
                    
                    echo '<img src="data:' . $mimeType . ';base64,' . base64_encode($product['image']) . '" 
                         alt="' . htmlspecialchars($product['name']) . '">';
                } else {
                    echo '<img src="../images/no-image.jpg" alt="Изображение отсутствует">';
                }
                
                echo '<h2>' . htmlspecialchars($product['name']) . '</h2>';
                echo '<p>' . htmlspecialchars($product['description']) . '</p>';
                echo '<p class="price">' . htmlspecialchars($product['price']) . ' руб.</p>';
                echo '<p>Остаток: ' . htmlspecialchars($product['quantity']) . ' шт.</p>';
                
                // Форма для добавления в корзину
                echo '<form action="add_to_cart.php" method="post" class="add-to-cart-form">';
                echo '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
                echo '<button type="submit"' . ($product['quantity'] <= 0 ? ' disabled' : '') . '>';
                echo $product['quantity'] > 0 ? 'В корзину' : 'Нет в наличии';
                echo '</button>';
                echo '</form>';
                
                echo '</div>';
            }
            
            $stmt->close();
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; ' . date('Y') . ' Магазин. Все права защищены.</p>
    </footer>
</body>
</html>