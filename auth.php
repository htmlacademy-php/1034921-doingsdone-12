<?php
session_start();

require_once 'functions.php';
require_once 'helpers.php';
require_once 'data.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $authUser = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT,
        ],
        true);

    $errorDescription = [
        'email' => 'Укажите E-mail',
        'isEmailNotExist' => 'Пользователь с этим E-mail не найден',
        'password' => 'Укажите пароль',
        'wrongPass' => 'Неверный пароль'
    ];

    $requiredFields = ['email', 'password'];

    $rules = [
        'email' => function() {
            return validateEmail($_POST['email']) ?? '';
        },
        'password' => function() {
            return validatePass($_POST['password']) ?? '';
        }
    ];

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
        if (($key === 'email') && !isUserExist($connect, $value) && !empty($value) ) {
            $errors[$key] = $errorDescription['isEmailNotExist'];
        }
        // проверка пароля пользователя
        if (($key === 'password') && !isUserPassCorrect($connect, $_POST['email'],  $value) && !empty($value)) {
            $errors[$key] = $errorDescription['wrongPass'];
        }
    }

    $errors = array_filter($errors);

    if (!count($errors) and isUserExist($connect, $form['email'])) {
        $user = getUserData($connect, $form['email']);
        if (password_verify($form['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: /index.php");
        }
    }

}

$pageContent = include_template('auth.php',
    [
        'errors' => $errors
    ]
);

print_r($pageContent);
