<?php

require_once __DIR__ . '/vendor/autoload.php';

$builder = new \Deimos\Imaginarium\Builder(__DIR__);

$gearman = new \Deimos\Imaginarium\Server\Server($builder);

$gearman->run();
