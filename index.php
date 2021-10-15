<?php

require 'data.php';
require 'functions.php';
require 'helpers.php';

$content = include_template('main.php',
    [
        'projects' => $projects,
        'tasks' => $tasks,
        'show_complete_tasks' => rand(0,1)
    ]
);

$layoutContent = include_template('layout.php',
    [
        'content' => $content,
        'title' => 'Дела в порядке'
    ]
);

print_r($layoutContent);