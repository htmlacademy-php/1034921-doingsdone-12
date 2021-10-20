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
# меньше аргумента hours, по условию задания аргумент hours = 24 часа
# переменная hoursBeforeTask = 24 в index передает аргумент функции в main

function checkHours(int $hours, string $date): bool
{
    $currentDate = date_create('now');
    $nextDate = date_create($date);
    $diff = date_diff($currentDate, $nextDate);
    $diffHours = date_interval_format($diff, '%h');
    $diffDays = date_interval_format($diff, '%d');
    #echo '<pre>', print_r($diff), '</pre>';
    return ($diffHours <= $hours) && ($diffDays === '0');
}