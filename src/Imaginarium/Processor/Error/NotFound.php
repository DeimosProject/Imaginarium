<?php

namespace Deimos\Imaginarium\Processor\Error;

use Deimos\Imaginarium\Controller;

class NotFound extends Controller
{

    protected $attribute = 'notFound';

    protected function actionDefault()
    {
        return '<h1>Page not found</h1>';
    }

}