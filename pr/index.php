<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <div class="logo">
            <img src="logo.png" alt="Логотип магазина">
        </div>
        <nav>
            <ul>
                <li><a href="./php/registration.php">Регистрация</a></li>
                <li><a href="./php/login.php">Авторизация</a></li>
                <li><a href="./php/account.php">Личный кабинет</a></li>
                <li><a href="#about">О нас</a></li>
                <li><a href="./php/products.php">Товары</a></li>
                <li><a href="./php/set_of_products.php">Корзина</a></li>
                <li><a href="./php/zakaz.php">Заказы</a></li>
                <li><a href="#search">Поиск</a></li>
                <li><a href="#contacts">Контакты</a></li>
            </ul>
        </nav>
    </header>

    <!-- Слайдер популярных товаров -->
    <section class="slider">
        <h2>Популярные товары</h2>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php
                require 'db.php';
                
                // Получаем 8 самых популярных товаров (можно изменить запрос)
                $stmt = $pdo->prepare('SELECT id, name, description, price, image FROM products ORDER BY RAND() LIMIT 8');
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($product = $result->fetch_assoc()) {
                    echo '<div class="swiper-slide">';
                    
                    // Выводим изображение товара
                    if (!empty($product['image'])) {
                        $imageInfo = getimagesizefromstring($product['image']);
                        $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/jpeg';
                        echo '<img src="data:' . $mimeType . ';base64,' . base64_encode($product['image']) . '" 
                             alt="' . htmlspecialchars($product['name']) . '">';
                    } else {
                        echo '<img src="./images/no-image.jpg" alt="Изображение отсутствует">';
                    }
                    
                    echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                    echo '<p class="price">' . htmlspecialchars($product['price']) . ' руб.</p>';
                    echo '<p>' . htmlspecialchars(mb_strimwidth($product['description'], 0, 50, "...")) . '</p>';
                    echo '<a href="./php/product_detail.php?id=' . $product['id'] . '" class="btn">Подробнее</a>';
                    echo '</div>';
                }
                
                $stmt->close();
                ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <!-- Секция авторизации -->
    <section id="auth">
        <h2>Авторизация</h2>
        <form>
            <input type="email" placeholder="Email" required>
            <input type="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            <a href="#">Забыли пароль?</a>
        </form>
    </section>

    <!-- Секция "О нас" -->
    <section id="about">
        <h2>О нас</h2>
        <p>Текст о компании...</p>
        <img src="about.jpg" alt="О нас">
    </section>

    <!-- Поле поиска -->
    <section id="search">
        <input type="text" placeholder="Поиск товара">
        <button>Найти</button>
    </section>

    <!-- Секция отзывов -->
    <section id="reviews">
        <h2>Отзывы</h2>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="user1.jpg" alt="Пользователь 1">
                    <h3>Имя пользователя 1</h3>
                    <p>Текст отзыва...</p>
                    <span>Дата отзыва</span>
                </div>
                <!-- Добавьте больше отзывов -->
            </div>
        </div>
    </section>

    <!-- Подвал -->
    <footer id="contacts">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Контакты</h3>
                <p>Адрес: ул. Примерная, 123, г. Примерный</p>
                <p>Телефон: +xxxxxxxxxxxxx</p>
                <p>Email: info@example.com</p>
            </div>
            <div class="footer-section">
                <h3>Социальные сети</h3>
                <ul>
                    <li><a href="#">xxxx</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Подписка на новости</h3>
                <form>
                    <input type="email" placeholder="Ваш email" required>
                    <button type="submit">Подписаться</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Название магазина. Все права защищены.</p>
        </div>
    </footer>

    <!-- Подключение скриптов -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.swiper-container', {
                loop: true,
                slidesPerView: 3,
                spaceBetween: 20,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 10
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                }
            });
        });
    </script>
</body>
</html>