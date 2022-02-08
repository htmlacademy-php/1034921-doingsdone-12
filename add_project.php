<?php

session_start();

require_once 'db.php';
require_once 'functions.php';
require_once 'helpers.php';

// если пользователь не аутентифицирован переадресуем его на guest
if (!isset($_SESSION['userId'])) {
    header("Location: guest.php");
    exit();
}

$userId = $_SESSION['userId'];
$userName = getNameByUser($connect, $userId);
$projects = getProjectsByUser($connect, $userId);
$tasksAll = getTasksByUser($connect, $userId);
$tasks = getTasksByUser($connect, $userId);

if (!isset($_POST)) {
    $newProject = array();
}
$newProject = filter_input_array(INPUT_POST,
    [
        'name' => FILTER_DEFAULT,
    ]);

$requiredFields = ['name'];

$taskError = [
    'isProjectExist' => 'Такое наименование проекта уже есть',
];

$rules = [
    'name' => function():string {
        return  validateFilled($_POST['name']) ?? '';
    }
];

$errors = [];

if (!empty($_POST)) {
    foreach ($newProject as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if (in_array($key, $requiredFields) && isProjectExist($connect, $value, $userId)) {
            $errors[$key] = $taskError['isProjectExist'];
        }
    }
}
$errors = array_filter($errors);

if (count($errors) === 0 && !empty($newProject)) {
    addNewProject($connect, $newProject, $userId);
    header("Location: index.php");
}

// если пользователь не аутентифицирован переадресуем его на guest
if (!isset($_SESSION['userId'])) {
    header("Location: guest.php");
    exit();
}

$pageContent = include_template('add_project.php',
    [
        'projects' => $projects,
        'tasksAll' => $tasksAll, // для расчета задач в меню
        'tasks' => $tasks,
        'errors' => $errors,
        'userName' => $userName
    ]
);

print_r($pageContent);

