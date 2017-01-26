<?php

namespace Deimos\Imaginarium;

use Deimos\Config\Config;
use Deimos\Flow\Configure;
use Deimos\Flow\DefaultConfig;
use Deimos\Flow\Flow;
use Deimos\Helper\Helper;
use Deimos\Request\Request;
use Deimos\Router\Router;

/**
 * Class Builder
 *
 * @package Deimos\Micro
 *
 * @method
 */
class Builder extends \Deimos\Builder\Builder
{

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Builder constructor.
     *
     * @param $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/') . '/';
    }

    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @return Helper
     *
     * @throws \InvalidArgumentException
     */
    public function helper()
    {
        return $this->once(function ()
        {
            return new Helper($this);
        }, __METHOD__);
    }

    /**
     * @return Config
     */
    public function config()
    {
        return $this->once(function ()
        {
            $rootDir = $this->rootDir;

            return new Config($rootDir . 'assets/config/', $this);
        });
    }

    /**
     * @return Router
     */
    protected function router()
    {
        return $this->instance('router');
    }

    /**
     * @return Router
     *
     * @throws \InvalidArgumentException
     */
    protected function buildRouter()
    {
        $resolver = $this->config()->get('resolver')->get();

        $router = new Router();
        $router->setRoutes($resolver);

        return $router;
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->instance('request');
    }

    public function buildStoragePath($user, $hash, $key = null)
    {
        $_origin = (null === $key) ? '/origin/' : ('/thumbs/' . $key . '/');

        $subpath = 'storage/' . $user . $_origin .
            $this->helper()->str()->sub($hash, 0, 2) . '/' .
            $this->helper()->str()->sub($hash, 2, 2);

        return $this->getRootDir() . $subpath . '/' . $hash;
    }

    /**
     * @return Request
     *
     * @throws \InvalidArgumentException
     */
    protected function buildRequest()
    {
        $request = new Request($this->helper());
        $request->setRouter($this->router());

        return $request;
    }

    /**
     * @return Flow
     *
     * @throws \InvalidArgumentException
     */
    public function flow()
    {
        return $this->once(function ()
        {
            $configure     = new Configure();
            $defaultConfig = new DefaultConfig();

            $configure->compile($this->getRootDir() . 'assets/compile');
            $configure->template($this->getRootDir() . 'assets/view');

            $configure->di(new Container($defaultConfig, $this->helper()));

            return new Flow($configure);
        });
    }

}