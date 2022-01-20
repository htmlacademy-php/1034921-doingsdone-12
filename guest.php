<?php

require_once 'helpers.php';


$pageContent = include_template('guest.php',
    [
    ]
);

print_r($pageContent);
