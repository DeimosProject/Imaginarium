<?php

namespace Deimos\Imaginarium\Processor\API\Controller;

use Deimos\Imaginarium\Controller;
use Deimos\Imaginarium\Server\Database;
use Deimos\Imaginarium\Server\Server;
use Deimos\ImaginariumSDK\SDK;

class Upload extends Controller
{

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @return string|null
     *
     * file path: <user>/<origin|thumbs>/<size_key>/<sub_hash>/<hash>
     */
    protected function actionDefault()
    {
        
        // Permitted types of request
        header('Access-Control-Allow-Methods: POST, OPTIONS');

        // Describe custom headers
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type');

        // A comma-separated list of domains
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

        // Allow cookie
        header('Access-Control-Allow-Credentials: true');
        
        if( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' )
        {
            exit();
        }

        $user = $this->request()->attributeRequired('user');

        $db = new Database($this->builder);

        do
        {
            $hash = $this->helper()->str()->random(6);

            $count = $db->imageExist($user, $hash);
        }
        while ($count);

        $this->user = $user;
        $this->hash = $hash;

        if ($this->saveImage())
        {
            $configObject = $this->builder->config()->get('gearman');

            $gearman = new \GearmanClient();
            $gearman->addServer(
                $configObject->get('host', '127.0.0.1'),
                $configObject->get('port', 4730)
            );

            $gearman->doBackground('resize', json_encode([
                'hash'  => $hash,
                'user'  => $user,
                'data'  => $this->request()->data(),
                'query' => $this->request()->query()
            ]));

            $db->imageSaveToDb($user, $hash);

            return [
                'hash' => $hash
            ];
        }

        return [
            'error' => 'Not save image...'
        ];
    }

    /**
     * @return bool
     */
    protected function saveImage()
    {
        $sdk = new SDK();
        $sdk->setUserName($this->user);
        $sdk->setBasedir($this->builder->getRootDir() . 'storage');
        $path = $sdk->getOriginalPath($this->hash);

        $this->helper()->dir()->make(dirname($path));

        $result = $this->helper()->uploads()->simple('filedata')->save($path);

        if ($result)
        {
            $serverApi = new Server($this->builder, $this->user);

            if ($serverApi->isImage($path))
            {
                $serverApi->optimizationImage($path);

                return true;
            }
        }

        return false;
    }

}
