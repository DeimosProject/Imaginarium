<?php

$client = new GearmanClient();
$client->addServer();

$gearman = new \GearmanClient();
$gearman->addServer();

foreach ([
    'f1af23ca',
    'f1af23cb',
    'f1af23cc',
    'f1af23cd',
    'f1af23ce',
    'f1af23cf',
] as $i)
{
    $client->doLowBackground('resize', json_encode([
        'user' => 'default',
        'hash' => $i,
    ]));
}
