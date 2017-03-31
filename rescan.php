<?php

include_once __DIR__ . '/vendor/autoload.php';

$builder = new \Deimos\Imaginarium\Builder(__DIR__);
$db      = new \Deimos\Imaginarium\Server\Database($builder);

$users = scandir(__DIR__ . '/storage');
$users = array_diff($users, ['.', '..']);

foreach ($users as $user)
{
    $directory = new \RecursiveDirectoryIterator(__DIR__ . '/storage/' . $user, \RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator  = new \RecursiveIteratorIterator($directory);

    foreach ($iterator as $data)
    {
        if ($data->isDir())
        {
            continue;
        }

        $hash = basename($data);

        if (!$db->imageExist($user, $hash))
        {
            $db->imageSaveToDb($user, $hash);
        }
    }

}
