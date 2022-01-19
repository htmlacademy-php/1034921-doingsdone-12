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
    $query = "SELECT t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project as p ON p.user_id = ? INNER JOIN user as u ON u.id = ? WHERE t.project_id = p.id";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

function getTasksByProjectId(object $connect, int $projectId): array
{
    $query = "SELECT t.name, t.state as isDone, p.name as category, t.expiration as date, t.file_name from task as t inner JOIN project as p on p.id = t.project_id WHERE p.id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $projectId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

function buildMenu(array $projects, int $requestedProjectId): array
{
    foreach ($projects as $key => $project) {
        $projects[$key]['selected'] = $requestedProjectId === $project['id'];
    }
    return $projects;
}

// возвращает значение поля для value в шаблоне
function getPostVal(string $field): ?string
{
    return filter_input(INPUT_POST, $field);
}

// валидация заполненности поля Наименование задачи
function validateFilled(string $field): ?string
{
    if (empty($field)) {
        return 'Это поле должно быть заполнено';
    }
    return null;
}

// валидация проекта
function validateProject(int $id, array $allowed_list): ?string
{
    if (!in_array($id, $allowed_list)) {
        return 'Указан несуществующий проект';
    }
    return null;
}

// валидация даты
function validateDate(string $date): ?string
{
    if (!is_date_valid($date)) {
            return 'Укажите в формате ГГГГ-ММ-ДД и ранее сегодняшнего дня';
        }
    return null;
}

// проверка даты, должна быть больше или равна текущей дате
function isDateCorrect(string $date): bool
{
    $now = time();
    $taskDate = strtotime($date);
    $diff = (floor($taskDate - $now) / HOUR_SECONDS / 24);
    return $diff >= -1;
}

// добавление новой задачи
function addNewTask(object $connect, array $task): void
{
    $query = "INSERT INTO task (name, project_id, expiration, file_name) VALUES (?, ?, ?, ?)";
    $stmt = db_get_prepare_stmt($connect, $query, $task);
    mysqli_stmt_execute($stmt);
}

// валидация вложенного файла
function validateFile(array $file): bool
{
    $fileName = $file['name'];
    $filePath = __DIR__ . '/uploads/';
    $tmpFile = $file['tmp_name'];
    $fileMaxSize = 1024000;
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($fileInfo, $tmpFile);
    $isFileAllow = in_array($fileType, ["application/pdf", "application/msword"]);
    return $isFileAllow && $file['size'] <= $fileMaxSize ? move_uploaded_file($tmpFile, $filePath . $fileName) : false;
}

// валидация email
function validateEmail(string $email): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "E-mail введён некорректно";
    }
    return null;
}

// валидация пароля
function validatePass(string $pass): ?string
{
    if (strlen($pass) < MIN_PASS_LENGTH) {
        $msgLength = 'Пароль должен быть не менее %s символов';
        return sprintf($msgLength, MIN_PASS_LENGTH);
    }
    preg_match('/(?=.*[a-z])(?=.*[A-Z]).*/', $pass, $matches);
    if (count($matches) === 0) {
        $msgPattern = 'Пароль должен быть не менее %s символов: a-z, A-Z, 0-9';
        return sprintf($msgPattern, MIN_PASS_LENGTH);
    }
    return null;
}

// проверка пользователя в БД
function isUserExist(object $connect, string $userEmail): bool
{
    $query = "SELECT id FROM user WHERE email = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultSql) ? true : false;
}

// добавление пользователя в БД
function userInsert(object $connect, array $form): void
{
    $password = password_hash($form['password'], PASSWORD_DEFAULT);
    $query = "INSERT INTO user (registration, email, name, password) VALUES (NOW(), ?, ?, ?)";
    $stmt = db_get_prepare_stmt($connect, $query,
        [
            $form['email'],
            $form['name'],
            $password
        ]);
    mysqli_stmt_execute($stmt);
}

// возвращает все поля пользователя
function getUserData(object $connect, string $userEmail): array
{
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultSql);
}

// проверка пароля пользователя
function isUserPassCorrect(object $connect, string $userEmail, string $formPass): bool
{
    $query = "SELECT password FROM user WHERE email = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($resultSql);
    return password_verify($formPass, $user['password']);
}

// получение имени пользователя, для отображения в шаблоне
function getNameByUser(object $connect, int $userId): string
{
    $query = "SELECT name FROM user WHERE id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($resultSql);
    return $user['name'];
}

