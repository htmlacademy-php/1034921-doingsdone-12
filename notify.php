<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require_once 'vendor/autoload.php';
require_once 'db.php';
require_once 'functions.php';

// объявляем параметры подключения в почтовому серверу
$dsn = 'smtp://9d35a72bb9ae55:a85c2d3843bc05@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

// возвращаем массив задач
$allExpiredTasks = getAllExpiredTasksByToday($connect);

// возвращаем массив с одной датой для каждого уведомления
$deadLine = array_unique(array_values(array_column($allExpiredTasks, 'deadline')));

// объявляем массив с задачами по ключу email
$emailTaskMapping = [];
foreach ($allExpiredTasks as $task) {
    $emailTaskMapping[$task['email']][] = $task['name'];
}

// отправляем уведомления
foreach ($emailTaskMapping as $email => $tasks) {
    $text = sprintf('Уважаемый, %s. У вас запланирована задача %s на %s', $email, implode(', ', $tasks), $deadLine[0]);
    $emailMessage = (new Email())
        ->from('keks@phpdemo.ru')
        ->to($email)
        ->priority(Email::PRIORITY_HIGHEST)
        ->subject('Уведомление от сервиса \"Дела в порядке\"')
        ->text($text);
    $mailer->send($emailMessage);
}
