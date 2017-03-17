<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

use Deimos\Imaginarium\Builder;

$builder = new Builder(dirname(__DIR__));
$request = $builder->request();

header("HTTP/1.0 404 Not Found");

if ($_SERVER['USER'] === 'www-data')
{

    $route = [
        'type' => 'pattern',
        'path' => 'storage/<user>/<type>(/<config>)/<ha>/<sh>/<hash>'
    ];

    $router = new \Deimos\Router\Router();

    $router->setRoutes([$route]);
    $router->setMethod();

    $route = $router->getCurrentRoute($request->query('q'));

    $attributes = $route->attributes();
    $sdk = new Deimos\ImaginariumSDK\SDK();
    
    $sdk->setBasedir(dirname(__DIR__) . '/storage');
    $sdk->setUserName($attributes['user']);

    if (file_exists($sdk->getOriginalPath( $attributes['hash'] )))
    {
        $configObject = $builder->config()->get('gearman');

        $gearman = new \GearmanClient();
        $gearman->addServer(
            $configObject->get('host', '127.0.0.1'),
            $configObject->get('port', 4730)
        );

        $gearman->doBackground('resize', json_encode([
            'hash'  => $attributes['hash'],
            'user'  => $attributes['user'],
            'data'  => $request->data(),
            'query' => $request->query()
        ]));

        $db = new \Deimos\Imaginarium\Server\Database($builder);
        $db->imageSaveToDb($attributes['user'], $attributes['hash']);
    }

}

?>
<html>
<head><title>404 Not Found</title></head>
<body bgcolor="white">
<center><h1>404 Not Found</h1></center>
<hr>
<center>nginx/1.10.0 (Ubuntu)</center>
</body>
</html>
<!-- a padding to disable MSIE and Chrome friendly error page -->
<!-- a padding to disable MSIE and Chrome friendly error page -->
<!-- a padding to disable MSIE and Chrome friendly error page -->
<!-- a padding to disable MSIE and Chrome friendly error page -->
<!-- a padding to disable MSIE and Chrome friendly error page -->
<!-- a padding to disable MSIE and Chrome friendly error page -->
