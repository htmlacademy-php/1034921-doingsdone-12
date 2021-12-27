<?php

require_once 'functions.php';
require_once 'helpers.php';
require_once 'data.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $newUser = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT,
            'name' => FILTER_DEFAULT
        ],
        true);

    $errorDescription = [
        'email' => 'Укажите E-mail',
        'isEmailExist' => 'Пользователь с этим E-mail уже зарегистрирован',
        'password' => 'Укажите пароль',
        'name' => 'Укажите имя'
    ];

    $requiredFields = ['email', 'password', 'name'];

    $rules = [
        'email' => function() {
            return validateEmail('email');
        },
        'password' => function() {
            return validatePass('password');
        },
        'name' => function() {
            return validateFilled('name');
        }
    ];

    foreach ($newUser as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        // валидация пустых полей
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = $errorDescription[$key];
        }
        // проверка пользователя в БД
        if (($key === 'email') && isUserExist($connect, $value)) {
            $errors[$key] = $errorDescription['isEmailExist'];
        }
    }
    // очищаем массив ошибок от значений NULL, иначе NULL тоже считается в массиве
    $errors = array_filter($errors);

    // проверяем на наличие ошибок, если ошибок нет, то добавляем пользователя
    if (count($errors) === 0) {
        userInsert($connect, $newUser);
        header("Location: /index.php");
        exit();
    }

}

$pageContent = include_template('register.php',
    [
        'errors' => $errors
    ]
);

print_r($pageContent);
