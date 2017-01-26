<?php

namespace Deimos\Imaginarium\Processor\UX\Controller;

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

        $minimal = true;
        $width   = 300;
        $_width  = 301;

        if (!$minimal)
        {
            if ($minimal ^ $_width > $width)
            {
                var_dump(222);
            }

        }
        else
        {

            if ((int)$_width < $width)
            {
                var_dump(111);
            }
        }
        die;

        return $this->view->render('main');

        //

        $db = new Db();

        $user = 'default';

        do
        {

            $hash = $this->builder->helper()->str()->random(6);

            $count = $db->imageExist($user, $hash);
        }
        while ($count);

        $gearman = new \GearmanClient();
        $gearman->addServer();

//        $gearman->doBackground('resize', json_encode([
//            'hash' => $hash,
//            'user' => $user,
//        ]));

        return '<h1>' . $hash . '</h1>';
    }

}
