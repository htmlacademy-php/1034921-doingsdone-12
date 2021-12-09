<?php
function countTasks(array $tasks, string $projectName): int
{
    $result = 0;
    foreach ($tasks as $task) {
        if ($task['category'] === $projectName)
        {
            $result++;
        }
    }
    return $result;
}

# функция возвращает истину если разница текущего времени и датой задания
# меньше аргумента hours, по условию задания параметр hours = 24 часа
# переменная hoursBeforeTask = 24 в index передает аргумент функции в main

function checkHours(int $hours, string $date): bool
{
    $now = time();
    $taskDate = strtotime($date);
    $diff = floor($taskDate - $now);
    return ($diff > 0) && (($diff / HOUR_SECONDS) <= $hours);
}

function getProjectsByUser(object $connect, int $userId): array
{
    $query = "SELECT id, name FROM project WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

// получить задачи по пользователю
function getTasksByUser(object $connect, int $userId): array
{
    $query = "SELECT t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project as p ON p.user_id = ? INNER JOIN user as u ON u.id = ? WHERE t.project_id = p.id";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

// получить задачи по проекту
function getTasksByProjectId(object $connect, int $projectId): array
{
    $query = "SELECT t.name, t.state as isDone, p.name as category, t.expiration as date, t.file_name from task as t inner JOIN project as p on p.id = t.project_id WHERE p.id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $projectId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}
// создание меню с выбранным проектом
function buildMenu(array $projects, int $requestedProjectId): array
{
    foreach ($projects as $key => $project) {
        $projects[$key]['selected'] = $requestedProjectId === $project['id'];
    }
    return $projects;
}

// возвращает значение поля для value в шаблоне
function getPostVal($field)
{
    //return $_POST[$field] ?? "";
    return filter_input(INPUT_POST, $field);
}

// валидация заполненности поля Наименование задачи
function validateFilled(string $field)
{
    if (empty($_POST[$field])) {
        return "Это поле должно быть заполнено";
    }
}

// проверка на существование проекта
function checkProjectId(int $projectId, array $allowed): bool
{
    return in_array($projectId, $allowed) ?? false;
}

// валидация проекта
function validateProject($id, array $allowed_list)
{
    if (!in_array($id, $allowed_list)) {
        return "Указан несуществующий проект";
    }
    return null;
}

// валидация даты
function validateDate(string $date)
{
    if (is_date_valid($date)) {
        $nowDate = date('d', time());
        $nowYear = date('Y', time());
        $taskDate = date('d', strtotime($date));
        $taskYear = date('Y', strtotime($date));
        $isDate = ($nowDate == $taskDate) && ($nowYear == $taskYear);
        if (!$isDate) {
            return "Укажите в формате ГГГГ-ММ-ДД и ранее сегодняшнего дня";
        }
    }
    return null;
}

// проверка даты, должна быть больше или равна текущей дате и году
function isDateCorrect(string $date): bool
{
    $result = false;
    if (is_date_valid($date)) {
        $nowDate = date('d', time());
        //echo  $nowDate;
        $nowYear = date('Y', time());
        $taskDate = date('d', strtotime($date));
        //echo  $taskDate;
        $taskYear = date('Y', strtotime($date));
        $isDate = ($nowDate <= $taskDate) && ($nowYear == $taskYear);
        $result = $isDate;
        return $result;
    }
    return $result;
}

// добавление новой задачи
function addNewTask(object $connect, array $task)
{
    $query = "INSERT INTO task (name, project_id, expiration, file_name) VALUES (?, ?, ?, ?)";
    $stmt = db_get_prepare_stmt($connect, $query, $task);
    mysqli_stmt_execute($stmt);
}
