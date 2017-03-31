<?php

include_once __DIR__ . '/vendor/autoload.php';

$builder = new \Deimos\Imaginarium\Builder(__DIR__);
$db      = new \Deimos\Imaginarium\Server\Database($builder);

$users = scandir(__DIR__ . '/storage');
$users = array_diff($users, ['.', '..']);

$sdk = new Deimos\ImaginariumSDK\SDK();

$sdk->setBasedir(dirname(__DIR__) . '/storage');

foreach ($users as $user)
{
    $sdk->setUserName($user);

    $directory = new \RecursiveDirectoryIterator(__DIR__ . '/storage/' . $user, \RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator  = new \RecursiveIteratorIterator($directory);

    foreach ($iterator as $data)
    {
        if ($data->isDir())
        {
            continue;
        }

        $hash = basename($data);

        if (file_exists($sdk->getOriginalPath($hash)))
        {
            $configObject = $builder->config()->get('gearman');

            $gearman = new \GearmanClient();
            $gearman->addServer(
                $configObject->get('host', '127.0.0.1'),
                $configObject->get('port', 4730)
            );

            $gearman->doBackground('resize' . $user, json_encode([
                'hash'  => $hash,
                'user'  => $user,
                'data'  => [],
                'query' => []
            ]));

            if (!$db->imageExist($user, $hash))
            {
                $db->imageSaveToDb($user, $hash);
            }
        }
    }

}
