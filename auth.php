<?php

session_start();

require_once 'db.php';
require_once 'functions.php';
require_once 'helpers.php';

if (!isset($_POST)) {
    header('Location: guest.php');
    exit();
}
$authUser = filter_input_array(INPUT_POST,
    [
        'email' => FILTER_DEFAULT,
        'password' => FILTER_DEFAULT,
    ]);

$errorDescription = [
    'email' => 'Укажите E-mail',
    'isEmailNotExist' => 'Пользователь с этим E-mail не найден',
    'password' => 'Укажите пароль',
    'wrongPass' => 'Неверный пароль'
];

$requiredFields = ['email', 'password'];

$rules = [
    'email' => function (): string {
        return validateEmail($_POST['email']) ?? '';
    },
    'password' => function (): string {
        return validatePass($_POST['password']) ?? '';
    }
];

$errors = [];

if (!empty($authUser)) {
    foreach ($authUser as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        // валидация пустых полей
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = $errorDescription[$key];
        }
        // проверка пользователя в БД
        if (($key === 'email') && !isUserExist($connect, $value) && !empty($value)) {
            $errors[$key] = $errorDescription['isEmailNotExist'];
        }
        // проверка пароля пользователя
        if (($key === 'password') && !isUserPassCorrect($connect, $_POST['email'], $value) && !empty($value)) {
            $errors[$key] = $errorDescription['wrongPass'];
        }
    }
}

$errors = array_filter($errors);

// проверяем наличие ошибок и пользователя
$isUserExist = isset($_POST['email']) ? isUserExist($connect, $_POST['email']) : false;
if (count($errors) === 0 && $isUserExist) {
    $user = getUserData($connect, $_POST['email']);
    $_SESSION['userId'] = $user['id'];
    header('Location: /index.php');
}

$pageContent = include_template('auth.php',
    [
        'errors' => $errors
    ]
);

print_r($pageContent);
