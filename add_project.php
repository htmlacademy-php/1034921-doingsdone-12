<?php

session_start();

require_once 'db.php';
require_once 'functions.php';
require_once 'helpers.php';
require_once 'data.php';

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $userName = getNameByUser($connect, $userId);
    $projects = getProjectsByUser($connect, $userId);
    $allowedPojects = array_column($projects, 'id');
    $tasksAll = getTasksByUser($connect, $userId);
    $tasks = getTasksByUser($connect, $userId);

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $newProject = filter_input_array(INPUT_POST,
            [
                'name' => FILTER_DEFAULT,
            ],
            true);

        $requiredFields = ['name'];

        $taskError = [
            'isProjectExist' => 'Такое наименование проекта уже есть',
        ];

        $rules = [
            'name' => function() {
                return  validateFilled($_POST['name']) ?? '';
            }
        ];

        foreach ($newProject as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
            if (in_array($key, $requiredFields) && isProjectExist($connect, $value, $userId)) {
                $errors[$key] = $taskError['isProjectExist'];
            }
        }

        $errors = array_filter($errors);

        if (count($errors) === 0) {
            addNewProject($connect, $newProject, $userId);
            header("Location: index.php");
        }
    }
}

$pageContent = include_template('add_project.php',
    [
        'projects' => $projects,
        'tasksAll' => $tasksAll, // для расчета задач в меню
        'tasks' => $tasks,
        'errors' => $errors,
        'allowedPojects' => $allowedPojects, // для проверки существования проекта
        'userName' => $userName
    ]
);

print_r($pageContent);

