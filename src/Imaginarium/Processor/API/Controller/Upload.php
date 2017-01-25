<?php

namespace Deimos\Imaginarium\Processor\API\Controller;

use Deimos\Imaginarium\Controller;
use Deimos\Imaginarium\Server\Db;

class Upload extends Controller
{

    /**
     * @return string
     *
     * file path: <user>/<origin|thumbs>/<size_key>/<sub_hash>/<hash>
     */
    protected function actionDefault()
    {
        $db = new Db();

        $user = 'default';

        do {

            $hash = $this->builder->helper()->str()->random(6);

            $count = $db->imageExist($user, $hash);
        } while($count);

//        $gearman = new \GearmanClient();
//        $gearman->addServer();
//
//        foreach ($this->builder->confid()->get('resizer') as $key => $value)
//        {
//            $gearman->addTaskBackground('resize_' . $key, json_encode([
//                'hash' => $hash,
//                'user' => $user,
//                'key' => $key
//            ]));
//        }

        return '<h1>' . $hash . '</h1>';
    }

}
