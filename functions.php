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

function getTasksByUser(object $connect, int $userId): array
{
    $query = "SELECT t.name, t.state AS isDone, t.expiration AS date, p.name AS category FROM task as t INNER JOIN project as p ON p.user_id = ? INNER JOIN user as u ON u.id = ? WHERE t.project_id = p.id";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

function getTasksByProjectId(object $connect, int $projectId): array
{
    $query = "SELECT t.name, t.state as isDone, p.name as category, t.expiration as date from task as t inner JOIN project as p on p.id = t.project_id WHERE p.id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $projectId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

function isMenuActive(string $projectId): bool
{
    $urlProjectId = $_GET['project_id'];
    return $projectId == $urlProjectId;
}
