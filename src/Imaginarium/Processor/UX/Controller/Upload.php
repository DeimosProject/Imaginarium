<?php

namespace Deimos\Imaginarium\Processor\UX\Controller;

use Deimos\Imaginarium\Controller;
use Deimos\Imaginarium\Server\Database;

class Upload extends Controller
{

    /**
     * @return string
     *
     * file path: <user>/<origin|thumbs>/<size_key>/<sub_hash>/<hash>
     */
    protected function actionDefault()
    {

        return $this->view->render('main');
    }

}
