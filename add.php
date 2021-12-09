<?php

require_once 'functions.php';
require_once 'helpers.php';

$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

$userId = 1;

$projects = getProjectsByUser($connect, $userId);
$allowedPojects = array_column($projects, 'id');
$tasksAll = getTasksByUser($connect, $userId);
$tasks = getTasksByUser($connect, $userId);

// определяем пустой массив, который будем заполнять ошибками валидации
$errors = [];

// если кнопка нажата необходимо проверить поля на заполнение и валидацию
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // обязательные поля для заполнения
    $requiredFields = ['name', 'project_id', 'date'];

    // Применение функций проверки ко всем значениям
    $rules = [
        'name' => function() {
            validateFilled('name');
        },
        'project_id' => function($value) use ($allowedPojects) {
            return validateProject($value, $allowedPojects);
        },
        'date' => function() {
            validateDate('date');
        }
    ];

    // Получим в массив поля из формы. Если какого то поля не будет в форме, то в массиве его значением будет NULL
    $newTask = filter_input_array(INPUT_POST,
        [
            'name' => FILTER_DEFAULT,
            'project_id' => FILTER_DEFAULT,
            'date' => FILTER_DEFAULT,
            'file' => FILTER_DEFAULT
        ],
        true);

    // доабавлеям файл
    if (!empty($_FILES['file'])) {
        $fileName = $_FILES['file']['name'];
        $filePath = __DIR__ . '/uploads/';
        $fileUrl = '/uploads/' . $fileName;
        move_uploaded_file($_FILES['file']['tmp_name'], $filePath . $fileName);
        // присвоим наименование файла новой задаче для передачи параметра функии addNewTask
        $newTask['file'] = $fileUrl;
    }

    // описание ошибок
    $taskError = [
        'name' => 'Поле  наименование задачи надо заполнить',
        'date' => 'Дата должна быть больше или равна текущей в формате ГГГГ-ММ-ДД',
        'file' => 'Вы не загрузили файл'
    ];

    // Применяем функции валидации ко всем полям формы.
    // Результат работы функций записывается в массив ошибок $errors.
    // Данный массив мы в итоге отфильтровываем,
    // чтобы удалить от туда пустые значения и оставить только сообщения об ошибках.
    // В этом же цикле мы проверяем заполненность обязательных к заполнению полей
    foreach ($newTask as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = $taskError[$key];
        }
        if (($key == 'date' && !isDateCorrect($value))) {
            $errors[$key] = $taskError[$key];
        }
    }
    $errors = array_filter($errors);



    // проверяем на наличие ошибок, если ошибок нет, то добоавляем задачу
    if (count($errors) === 0) {
        // добавление задачи в БД
        addNewTask($connect, $newTask);
        // переадресация на главную страницу
        header("Location: index.php");
    }
}

$pageContent = include_template('add_task.php',
    [
        'projects' => $projects,
        'tasksAll' => $tasksAll, // для расчета задач в меню
        'tasks' => $tasks,
        'errors' => $errors,
        'allowedPojects' => $allowedPojects, // для проверки существования проекта
    ]
);

print_r($pageContent);
