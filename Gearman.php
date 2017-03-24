<?php

if(empty($argv[1]))
{
    echo 'please, use' . PHP_EOL . 'php Gearman.php default' . PHP_EOL . 'default - user name with config' . PHP_EOL;
    die;
}

require_once __DIR__ . '/vendor/autoload.php';

$builder = new \Deimos\Imaginarium\Builder(__DIR__);

$gearman = new \Deimos\Imaginarium\Server\Server($builder, $argv[1]);

$gearman->run($argv[1]);
