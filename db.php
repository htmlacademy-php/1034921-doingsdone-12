<?php

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

// проверка соединения с БД
if (!$connect) {
    die("Соединение не установлено: " . mysqli_connect_error());
}
