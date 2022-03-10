<?php

const HOUR_SECONDS = 3600; // для расчета часов при использовании timestamp
const MIN_PASS_LENGTH = 6;

/**
 * Расчет количества задач
 * @param string $projectName Проект
 * @param array $tasks Задачи
 * @return int количество задач
 */
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
/**
 * Проверка текущего времени и даты задачи
 * @param int $hours Количество часов до даты задачи
 * @param string $date Дата задач
 * @return bool true
 */
function checkHours(int $hours, string $date): bool
{
    $now = time();
    $taskDate = strtotime($date);
    $diff = floor($taskDate - $now);
    return ($diff > 0) && (($diff / HOUR_SECONDS) <= $hours);
}

/**
 * Возвращает проекты по id пользователя
 * @param object $connect Параметры БД
 * @param int $userId id пользователя
 * @return array Проекты
 */
function getProjectsByUser(object $connect, int $userId): array
{
    $query = 'SELECT id, name FROM project WHERE user_id = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Возвращает задачи по id пользователя
 * @param object $connect Параметры БД
 * @param int $userId id пользователя
 * @return array Задачи
 */
function getTasksByUser(object $connect, int $userId): array
{
    $query = 'SELECT t.id, t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project as p ON p.user_id = ? INNER JOIN user as u ON u.id = ? WHERE t.project_id = p.id';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Возвращает задачи по id проекта
 * @param object $connect Параметры БД
 * @param int $projectId id проекта
 * @return array Задачи
 */
function getTasksByProjectId(object $connect, int $projectId): array
{
    $query = 'SELECT t.id, t.name, t.state as isDone, p.name as category, t.expiration as date, t.file_name from task as t inner JOIN project as p on p.id = t.project_id WHERE p.id = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $projectId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Возвращает задачи по id проекта
 * @param object $connect Соединение с БД
 * @param int $projectId id проекта
 * @return array Задачи
 */
function buildMenu(array $projects, int $requestedProjectId): array
{
    foreach ($projects as $key => $project) {
        $projects[$key]['selected'] = $requestedProjectId === $project['id'];
    }
    return $projects;
}

/**
 * Возвращает значение поля из формы
 * @param string $field Строка
 * @return null|string Строка
 */
function getPostVal(string $field): ?string
{
    return filter_input(INPUT_POST, $field);
}

/**
 * Валидация заполненности поля из формы
 * @param string $field Строка
 * @return null|string Строка
 */
function validateFilled(string $field): ?string
{
    if (empty($field)) {
        return 'Это поле должно быть заполнено';
    }
    return null;
}

/**
 * Валидация существования проекта
 * @param int $id id проекта
 * @param array $allowedList Индексный массив
 * @return null|string Строка
 */
function validateProject(int $id, array $allowedList): ?string
{
    if (!in_array($id, $allowedList)) {
        return 'Указан несуществующий проект';
    }
    return null;
}

/**
 * Валидация формата заполнения даты
 * @param string $date Строка
 * @return null|string Строка
 */
function validateDate(string $date): ?string
{

    if (!is_date_valid($date)) {
            return 'Укажите в формате ГГГГ-ММ-ДД и не ранее сегодняшнего дня';
        }
    return null;
}

/**
 * Проверка даты, должна быть больше или равна текущей дате
 * @param string $date Строка
 * @return bool true/false
 */
function isDateCorrect(string $date): bool
{
    $now = time();
    $taskDate = strtotime($date);
    $diff = (floor($taskDate - $now) / HOUR_SECONDS / 24);
    return $diff >= -1;
}

/**
 * Добавление новой задачи
 * @param object $connect Соединение с БД
 * @param array $task Задача
 * @return void Добавление задачи
 */
function addNewTask(object $connect, array $task): void
{
    $query = 'INSERT INTO task (name, project_id, expiration, file_name) VALUES (?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($connect, $query, $task);
    mysqli_stmt_execute($stmt);
}

/**
 * Валидация вложенного файла
 * @param array $file
 * @return bool true/false
 */
function validateFile(array $file): bool
{
    $fileName = $file['name'];
    $filePath = __DIR__ . '/uploads/';
    $tmpFile = $file['tmp_name'];
    $fileMaxSize = 1024000;
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($fileInfo, $tmpFile);
    $isFileAllow = in_array($fileType, ['application/pdf', 'application/msword']);
    return $isFileAllow && $file['size'] <= $fileMaxSize ? move_uploaded_file($tmpFile, $filePath . $fileName) : false;
}

/**
 * Валидация email
 * @param string $email
 * @return null|string null|Строка
 */
function validateEmail(string $email): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'E-mail введён некорректно';
    }
    return null;
}

/**
 * Валидация пароля
 * @param string $pass
 * @return null|string null|Строка
 */
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

/**
 * Добавление новой задачи
 * @param object $connect Соединение с БД
 * @param string $userEmail email
 * @return bool true/false
 */
function isUserExist(object $connect, string $userEmail): bool
{
    $query = 'SELECT id FROM user WHERE email = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultSql) ? true : false;
}

/**
 * Добавление пользователя в БД
 * @param object $connect Соединение с БД
 * @param array $form Массив
 * @return void Добавление
 */
function userInsert(object $connect, array $form): void
{
    $password = password_hash($form['password'], PASSWORD_DEFAULT);
    $query = 'INSERT INTO user (registration, email, name, password) VALUES (NOW(), ?, ?, ?)';
    $stmt = db_get_prepare_stmt($connect, $query,
        [
            $form['email'],
            $form['name'],
            $password
        ]);
    mysqli_stmt_execute($stmt);
}

/**
 * Возвращает все поля пользователя
 * @param object $connect Соединение с БД
 * @param string $userEmail email
 * @return array Массив
 */
function getUserData(object $connect, string $userEmail): array
{
    $query = 'SELECT * FROM user WHERE email = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultSql);
}

/**
 * Проверка пароля пользователя
 * @param object $connect Соединение с БД
 * @param string $userEmail email
 * @param string $formPass пароль
 * @return array Массив
 */
function isUserPassCorrect(object $connect, string $userEmail, string $formPass): bool
{
    $query = 'SELECT password FROM user WHERE email = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $userEmail);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($resultSql);
    return password_verify($formPass, $user['password']);
}

/**
 * Возвращает имя пользователя
 * @param object $connect Соединение с БД
 * @param int $userId id пользователя
 * @return string Строка
 */
function getNameByUser(object $connect, int $userId): string
{
    $query = 'SELECT name FROM user WHERE id = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($resultSql);
    return $user['name'];
}

/**
 * Возвращает задачи при поиске
 * @param object $connect Соединение с БД
 * @param int $userId id пользователя
 * @param string $queryText Запрашиваемая строка
 * @return string Строка
 */
function getFromQuery(object $connect, int $userId, string $queryText): array
{
    $query = 'SELECT t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project as p ON p.user_id = ? INNER JOIN user as u ON u.id = ? WHERE (t.project_id = p.id) AND (MATCH(t.name) AGAINST(?))';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $userId, $userId, $queryText);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Добавление проекта
 * @param object $connect Соединение с БД
 * @param array $form Форма
 * @param int $userId id пользователя
 * @return string Строка
 */
function addNewProject(object $connect, array $form, int $userId): void
{
    $query = 'INSERT INTO project (name, user_id) VALUES (?, ?)';
    $stmt = db_get_prepare_stmt($connect, $query);
    mysqli_stmt_bind_param($stmt, 'si', $form['name'], $userId);
    //$stmt = db_get_prepare_stmt($connect, $query, [ $project['name'], $userId ]);
    mysqli_stmt_execute($stmt);
}

/**
 * Проверка проекта на дублирование
 * @param object $connect Соединение с БД
 * @param string $project Проект
 * @param int $userId id пользователя
 * @return string Строка
 */
function isProjectExist(object $connect, string $project, int $userId): bool
{
    $query = 'SELECT id FROM project WHERE name = ? AND user_id = ?';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'si', $project, $userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultSql) ? true : false;
}

/**
 * Изменяет состояние задачи с выполнено -> не выполнено, и наоборот
 * @param object $connect Соединение с БД
 * @param int $taskId id задачи
 * @return void Изменение
 */
function changeTaskState(object $connect, int $taskId): void
{
    $query = 'UPDATE task SET state = ABS(state - 1) WHERE id = ?';
    $stmt = db_get_prepare_stmt($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $taskId);
    mysqli_stmt_execute($stmt);
}

/**
 * Возвращает задачи для фильтра - Повестка дня и Завтра
 * @param object $connect Соединение с БД
 * @param int $userId id пользователя
 * @param string $plusDays Количество дней
 * @return array Задачи
 */
function getTasksByDay(object $connect, int $userId, string $plusDays): array
{
    $query = 'SELECT t.id, p.id AS project_id, t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project AS p ON p.id = t.project_id WHERE p.user_id = ? AND t.expiration = DATE_ADD(CURDATE(), INTERVAL ? DAY)';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'is',$userId,$plusDays);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Возвращает задачи для фильтра: Просроченные — показывает все задачи, которые не были выполнены и у которых истёк срок
 * @param object $connect Соединение с БД
 * @param int $userId id пользователя
 * @return array Задачи
 */
function getExpiredTasks(object $connect, int $userId): array
{
    $query = 'SELECT t.id, p.id AS project_id, t.name, t.state AS isDone, t.expiration AS date, p.name AS category, t.file_name FROM task as t INNER JOIN project AS p ON p.id = t.project_id WHERE p.user_id = ? AND t.state = 0 AND t.expiration < CURDATE()';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i',$userId);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}

/**
 * Возвращает задачи у которых срок равен текущему дню
 * @param object $connect Соединение с БД
 * @return array Задачи
 */
function getAllExpiredTasksByToday(object $connect): array
{
    $query = 'SELECT u.email, t.name FROM task AS t INNER JOIN project AS p ON p.id = t.project_id INNER JOIN user AS u ON u.id = p.user_id WHERE t.state = 0 AND t.expiration = CURDATE()';
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_execute($stmt);
    $resultSql = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($resultSql, MYSQLI_ASSOC);
}
