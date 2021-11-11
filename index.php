<?php

require 'data.php';
require 'functions.php';
require 'helpers.php';
require 'db.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

if (!$connect) {
    print_r('error: ', mysqli_connect_error());
}
$userId = 1;
$sqlUserProjects = "SELECT name FROM project WHERE user_id = ?";
$sqlUserTasks = "SELECT t.name, t.state AS isDone, t.expiration AS date, p.name AS category FROM task as t JOIN project as p ON p.user_id = ? JOIN user as u ON u.id = ? WHERE t.project_id = p.id";

$stmt = mysqli_prepare($connect, $sqlUserProjects);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$resultSqlUserProjects = mysqli_stmt_get_result($stmt);
$projects = mysqli_fetch_all($resultSqlUserProjects, MYSQLI_ASSOC);

$stmt = mysqli_prepare($connect, $sqlUserTasks);
mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
mysqli_stmt_execute($stmt);
$resultSqlUserTasks = mysqli_stmt_get_result($stmt);
$tasks = mysqli_fetch_all($resultSqlUserTasks, MYSQLI_ASSOC);

mysqli_close($connect);

$content = include_template('main.php',
    [
        'projects' => array_column($projects, 'name'),
        'tasks' => $tasks,
        'show_complete_tasks' => rand(0,1),
        'hoursBeforeTask' => 24
    ]
);

$layoutContent = include_template('layout.php',
    [
        'content' => $content,
        'title' => 'Дела в порядке'
    ]
);

print_r($layoutContent);