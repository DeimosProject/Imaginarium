<?php

namespace Deimos\Imaginarium\Processor\API;

use Deimos\Imaginarium\Processor\API\Controller\Upload;

class Processor extends \Deimos\Controller\Processor
{

    /**
     * @return Upload
     *
     * @throws \InvalidArgumentException
     */
    protected function buildUpload()
    {
        return new Upload($this->builder);
    }

}