<?php

namespace Deimos\Imaginarium;

use Deimos\Flow\Flow;

class Controller extends \Deimos\Controller\Controller
{

    /**
     * @var \Deimos\Imaginarium\Builder
     */
    protected $builder;

    /**
     * @var Flow
     */
    protected $view;

    /**
     * Controller constructor.
     *
     * @param Builder $builder
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Builder $builder)
    {
        /**
         * @var Builder $builder
         */
        parent::__construct($builder);

        $this->view = $builder->flow();
    }

    /**
     * @return void
     */
    protected function configure()
    {

    }

    /**
     * @return void
     */
    protected function before()
    {

    }

    /**
     * @param mixed $data
     *
     * @return void
     */
    protected function after($data)
    {

    }

}