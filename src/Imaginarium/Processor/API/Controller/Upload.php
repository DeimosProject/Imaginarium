<?php

namespace Deimos\Imaginarium\Processor\API\Controller;

use Deimos\Imaginarium\Controller;
use Deimos\Imaginarium\Server\Db;
use Deimos\Imaginarium\Server\Server;

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
     * @return bool
     */
    protected function saveImage()
    {
        $path = $this->builder->buildStoragePath(
            $this->user,
            $this->hash
        );

        $this->helper()->file()->saveUploadedFile('file', $path);

        $serverApi = new Server($this->builder);

        if ($serverApi->isImage($path))
        {
            $serverApi->optimizationImage($path);

            return true;
        }

        return false;
    }

    /**
     * @return string|null
     *
     * file path: <user>/<origin|thumbs>/<size_key>/<sub_hash>/<hash>
     */
    protected function actionDefault()
    {
        $db = new Db();

        $user = 'default';

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

            return $hash;
        }

        return null;
    }

}
