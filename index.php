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
// иначе продолжаем
$userId = $_SESSION['userId'];
$userName = getNameByUser($connect, $userId);
$projects = getProjectsByUser($connect, $userId);
$tasksAll = getTasksByUser($connect, $userId);
$urlProjectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
// для JS который обрабатывает GET параметр
// $showCompletedTasks = 0;
$showCompletedTasks = isset($_GET['show_completed']) ? intval($_GET['show_completed']) : 0;

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

// возвращаем значение из поисковой строки
$searchText = trim(filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING));
if ($searchText) {
    // передача в шаблон нового списка задач по запросу
    $tasks = getFromQuery($connect, $userId, $searchText);
}

// изменяем состояние задачи с выполнено на не выполнено, и обратно
if (isset($_GET['task_id'])) {
    changeTaskState($connect, intval($_GET['task_id']));
    header("Location: index.php");
}

// фильтруем задачи по - Все задачи, показывает все задачи в выбранном проекте
if (isset($_GET['tasks_all'])) {
    $tasks = getTasksByProjectId($connect, $urlProjectId);
}

// фильтруем задачи по - Повестка дня, показывает все задачи на сегодня
if (isset($_GET['tasks_today'])) {
    $tasks = getTasksByDay($connect, $userId, '0');
}

// фильтруем задачи по - Завтра, показывает все задачи на завтра
if (isset($_GET['tasks_tomorrow'])) {
    $tasks = getTasksByDay($connect, $userId,'1');
}

// фильтруем задачи по - Просроченные, показывает все задачи, которые не были выполнены и у которых истёк срок
if (isset($_GET['tasks_expired'])) {
    $tasks = getExpiredTasks($connect, $userId);
}

$content = include_template('main.php',
    [
        'projects' => $projects,
        'tasksAll' => $tasksAll, // для расчета задач в меню
        'tasks' => $tasks,
        'show_complete_tasks' => $showCompletedTasks,
        'hoursBeforeTask' => 24,
        'connect' => $connect
    ]
);

$layoutContent = include_template('layout.php',
    [
        'content' => $content,
        'title' => 'Дела в порядке',
        'userName' => $userName
    ]
);

print_r($layoutContent);

mysqli_close($connect);

