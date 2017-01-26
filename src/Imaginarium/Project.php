<?php

namespace Deimos\Imaginarium;

use Deimos\Controller\Exceptions\NotFound as ExceptionNotFound;
use Deimos\Imaginarium\Processor\Error\NotFound;

class Project
{

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * Project constructor.
     *
     * @param $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @return string
     *
     * @throws \Deimos\Controller\Exceptions\NotFound
     * @throws \Deimos\Controller\Exceptions\DisplayNone
     * @throws \Deimos\Controller\Exceptions\RequestNotFound
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $builder   = $this->builder();
        $processor = new Processor\Processor($builder);

        try
        {
            return $processor->execute();
        }
        catch (ExceptionNotFound $notFound)
        {
            $error = new NotFound($builder);

            return $error->execute();
        }
    }

    /**
     * @return Builder
     */
    protected function builder()
    {
        if (!$this->builder)
        {
            $this->builder = new Builder($this->rootDir);
        }

        return $this->builder;
    }

}
