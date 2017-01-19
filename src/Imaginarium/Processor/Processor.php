<?php

namespace Deimos\Imaginarium\Processor;

class Processor extends \Deimos\Controller\Processor
{

    protected $attribute = 'runner';

    /**
     * @return API\Processor
     *
     * @throws \InvalidArgumentException
     */
    protected function buildApi()
    {
        return new API\Processor($this->builder);
    }

}