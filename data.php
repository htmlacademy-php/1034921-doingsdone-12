<?php
const HOUR_SECONDS = 3600; // для расчета часов при использовании timestamp
const MIN_PASS_LENGTH = 6;
$show_complete_tasks = rand(0, 1);
$projects = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
$tasks =
    [
        [
            'name' => 'Собеседование в IT компании',
            'date' => '27.10.2021',
            'category' => 'Работа',
            'isDone' => false
        ],
        [
            'name' => 'Выполнить тестовое задание',
            'date' => '23.12.2019',
            'category' => 'Работа',
            'isDone' => false
        ],
        [
            'name' => 'Сделать задание первого раздела',
            'date' => '26.10.2021',
            'category' => 'Учеба',
            'isDone' => true
        ],
        [
            'name' => 'Встреча с другом',
            'date' => '23.11.2021',
            'category' => 'Входящие',
            'isDone' => true
        ],
        [
            'name' => 'Купить корм для кота',
            'date' => null,
            'category' => 'Домашние дела',
            'isDone' => false
        ],
        [
            'name' => 'Заказать пиццу',
            'date' => null,
            'category' => 'Домашние дела',
            'isDone' => false
        ]
    ];
