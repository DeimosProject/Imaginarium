<?php

$rootDir = dirname(__DIR__) . '/';

include_once $rootDir . 'vendor/autoload.php';

$app = new \Deimos\Imaginarium\Project($rootDir);
echo $app->execute();
