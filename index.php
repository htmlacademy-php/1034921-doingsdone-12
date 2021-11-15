<?php

require 'data.php';
require 'functions.php';
require 'helpers.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');
  
$userId = 1;

$projects = getProjectsByUser($connect, $userId);
$tasks = getTasksByUser($connect, $userId);

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