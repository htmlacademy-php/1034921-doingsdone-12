<?php

session_start();

require_once 'db.php';
require_once 'functions.php';
require_once 'helpers.php';

// если пользователь не аутентифицирован переадресуем его на guest
if (!isset($_SESSION['userId'])) {
    header('Location: guest.php');
    exit();
}

$userId = $_SESSION['userId'];
$userName = getNameByUser($connect, $userId);
$projects = getProjectsByUser($connect, $userId);
$allowedProjects = array_column($projects, 'id');
$tasksAll = getTasksByUser($connect, $userId);
$tasks = getTasksByUser($connect, $userId);

// если проекта/ов нет, выдаем 404 ошибку
if (empty($projects)) {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    exit();
}

// форма не отправлена, то переадресация на index
if (!isset($_POST)) {
    header('Location: index.php');
    exit();
}
// Получим в массив поля из формы. Если какого то поля не будет в форме, то в массиве его значением будет NULL
$newTask = filter_input_array(INPUT_POST,
    [
        'name' => FILTER_DEFAULT,
        'project_id' => FILTER_DEFAULT,
        'date' => FILTER_DEFAULT,
        'file' => FILTER_DEFAULT
    ]);

// описание ошибок
$taskError = [
    'name' => 'Поле наименование задачи надо заполнить',
    'date' => 'Дата должна быть больше или равна текущей в формате ГГГГ-ММ-ДД',
    'bigFile' => 'Загрузите документ не более 1 Мб в формате PDF или Word',
    'loadFile' => 'Загрузите файл'
];

// обязательные поля для заполнения
$requiredFields = ['name', 'project_id', 'date'];

// Применение функций проверки ко всем значениям
$rules = [
    'name' => function():string {
        return  validateFilled($_POST['name']) ?? '';
    },
    'project_id' => function($value) use ($allowedProjects):string {
        return validateProject($value, $allowedProjects) ?? '';
    },
    'date' => function():string {
        return validateDate($_POST['date']) ?? '';
    }
];

$errors = [];

// Применяем функции валидации ко всем полям формы.
// Результат работы функций записывается в массив ошибок $errors.
// Данный массив мы в итоге отфильтровываем,
// чтобы удалить от туда пустые значения и оставить только сообщения об ошибках.
// В этом же цикле мы проверяем заполненность обязательных к заполнению полей
if (!empty($_POST)) {
    foreach ($newTask as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = $taskError[$key];
        }
        if (($key === 'date' && !isDateCorrect($value))) {
            $errors[$key] = $taskError[$key];
        }
    }
}

$errors = array_filter($errors);

// передаем в шаблон готовый массив с выбранным проектом
if (!empty($_POST)) {
    $selectedProjectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);
    $selectProjects = buildMenu($projects, $selectedProjectId);
}
// если проект не выбран, то по умолчанию выбранный проект будет первый
$selectedProjectId = $projects[0]['id'];
$selectProjects = buildMenu($projects, $selectedProjectId);

// добавляем файл
$file = isset($_FILES['file']) ? $_FILES['file'] : [];
if (isset($file['size']) && $file['size'] === 0) {
    $errors['file'] = 'Загрузите файл';
}
// если нет наименования файла, и файл имеет размер более 0 кБ и он не прошел валидацию
if (isset($file['name']) && $file['size'] > 0  && !validateFile($file)) {
    $errors['file'] = 'Загрузите документ не более 1 Мб в формате PDF или Word';
}
// возвращаем наименование файла для добавления в БД
if (!empty($file) && $file['size'] > 0) {
    $newTask['file'] = $file['name'];
}

// проверяем на наличие ошибок, если ошибок нет, то добавляем задачу
if (count($errors) === 0 && !empty($newTask)) {
    // добавление задачи в БД
    addNewTask($connect, $newTask);
    // переадресация на главную страницу
    header('Location: index.php');
    exit();
}

$pageContent = include_template('add_task.php',
    [
        'projects' => $projects,
        'tasksAll' => $tasksAll, // для расчета задач в меню
        'tasks' => $tasks,
        'errors' => $errors,
        'userName' => $userName,
        'selectProjects' => $selectProjects // передаем готовый массив проектов, с выбранным текущим проектом
    ]
);

print_r($pageContent);
