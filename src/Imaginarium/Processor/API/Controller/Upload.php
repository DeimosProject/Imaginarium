<?php

namespace Deimos\Imaginarium\Processor\API\Controller;

use Deimos\Imaginarium\Controller;
use Deimos\Imaginarium\Server\Database;
use Deimos\Imaginarium\Server\Server;
use Deimos\ImaginariumSDK\SDK;
use Deimos\Request\Request;

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
        $user = $this->request()->queryRequired('user');

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
            $gearman = new \GearmanClient();
            $gearman->addServer();

            $gearman->doBackground('resize', json_encode([
                'hash' => $hash,
                'user' => $user,
            ]));

            $db->imageSaveToDb($user, $hash);

            return $hash;
        }

        return null;
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
