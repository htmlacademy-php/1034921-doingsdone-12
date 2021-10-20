<?php
$show_complete_tasks = rand(0, 1);
$projects = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
$tasks = 
    [
        [
            'name' => 'Собеседование в IT компании',
            'date' => '21.10.2021',
            'category' => 'Работа',
            'isDone' => true
        ],
        [
            'name' => 'Выполнить тестовое задание',
            'date' => '25.12.2019',
            'category' => 'Работа',
            'isDone' => false
        ],
        [
            'name' => 'Сделать задание первого раздела',
            'date' => '21.10.2021',
            'category' => 'Учеба',
            'isDone' => true
        ],
        [
            'name' => 'Встреча с другом',
            'date' => '22.12.2019',
            'category' => 'Входящие',
            'isDone' => false
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
