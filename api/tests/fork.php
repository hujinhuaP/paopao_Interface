<?php 

require '../vendor/autoload.php';

use duncan3dc\Helpers\Fork;

$oFork = new Fork();
$oFork->call(function () {
    for ($i = 1; $i < 3; $i++) {
        echo "Process A - " . $i . "\n";
    }
});

$oFork->call(function () {
    sleep(10);
    for ($i = 1; $i < 3; $i++) {
        echo "Process B - " . $i . "\n";
    }
});

$oFork->call(function () {
    for ($i = 1; $i < 3; $i++) {
        echo "Process C - " . $i . "\n";
    }
});

$oFork->wait();