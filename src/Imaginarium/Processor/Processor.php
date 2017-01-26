<?php

namespace Deimos\Imaginarium\Processor;

class Processor extends \Deimos\Imaginarium\Processor
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

    /**
     * @return UX\Processor
     *
     * @throws \InvalidArgumentException
     */
    protected function buildUx()
    {
        return new UX\Processor($this->builder);
    }

}