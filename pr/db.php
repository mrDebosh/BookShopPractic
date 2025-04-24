<?php
$pdo = new mysqli('localhost', 'root', '', 'practic_bookshop');
if ($pdo->connect_error) {
    die('Ошибка: невозможно подключиться: ' . $pdo->connect_error);
}
$pdo->set_charset("utf8");
?>