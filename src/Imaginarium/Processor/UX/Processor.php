<?php

/**
 * DEMO FILE UPLOAD
 */

namespace Deimos\Imaginarium\Processor\UX;

use Deimos\Imaginarium\Processor\UX\Controller\Upload;

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