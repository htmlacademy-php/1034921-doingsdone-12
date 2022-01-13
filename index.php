<?php

session_start();

require_once 'data.php';
require_once 'functions.php';
require_once 'helpers.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

if (isset($_SESSION['user'])) {

    $user = getUserData($connect, $_SESSION['user']['email']);
    $userId = $user['id'];
    $projects = getProjectsByUser($connect, $userId);
    $tasksAll = getTasksByUser($connect, $userId);
    $urlProjectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

    if (isset($urlProjectId)) {
        $projects = buildMenu($projects, $urlProjectId);
        $tasks = getTasksByProjectId($connect, $urlProjectId);
    } else {
        $tasks = getTasksByUser($connect, $userId);
    }

    if (isset($urlProjectId) && !in_array($urlProjectId, array_column($projects, 'id'))) {
        header("HTTP/1.1 404 Not Found");
        http_response_code(404);
        exit();
    }

    $content = include_template('main.php',
        [
            'projects' => $projects,
            'tasksAll' => $tasksAll, // для расчета задач в меню
            'tasks' => $tasks,
            'show_complete_tasks' => rand(0, 1),
            'hoursBeforeTask' => 24,
            'connect' => $connect,
        ]
    );

    $layoutContent = include_template('layout.php',
        [
            'content' => $content,
            'title' => 'Дела в порядке',
            'user' => $user
        ]
    );

    print_r($layoutContent);

    mysqli_close($connect);
}
else {
    header("Location: guest.php");
}
