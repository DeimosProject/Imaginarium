<?php

namespace Deimos\Imaginarium\Server;

use Deimos\Config\ConfigObject;
use Deimos\Helper\Exceptions\ExceptionEmpty;
use Deimos\Imaginarium\Builder;
use Deimos\Imaginarium\ResizeAdapter\Contain;
use Deimos\Imaginarium\ResizeAdapter\Cover;
use Deimos\Imaginarium\ResizeAdapter\None;
use Deimos\Imaginarium\ResizeAdapter\Resize;

class Server
{

    /**
     * @var Builder
     */
    protected $builder;
    /**
     * @var string
     */
    protected $driver;
    /**
     * @var string
     */
    protected $hash;
    /**
     * @var string
     */
    protected $user;
    /**
     * @var \GearmanWorker
     */
    protected $worker;
    /**
     * @var ConfigObject
     */
    protected $config;

    /**
     * @param $builder Builder
     */
    public function __construct(Builder $builder)
    {
        $this->worker = new \GearmanWorker();
        $this->worker->addServer();
        $this->builder = $builder;
    }

    /**
     * @param string $subName
     */
    public function run($subName = '')
    {
        $this->worker->addFunction('resize' . $subName, function (\GearmanJob $job)
        {

            $this->driver = $this->builder->config()->get('image_driver')->get('driver');

            /**
             * @var array
             */
            $params = (array)json_decode($job->workload());

            $this->user = $params['user'];
            $this->hash = $params['hash'];

            /**
             * @var ConfigObject
             */
            $this->config = $this->builder->config()->get('resizer');

            foreach ($this->config as $key => $value) {

                $toFile = $this->buildPath($key);

                $this->resize($key, $value, $toFile);
            }
        });

        while ($this->worker->work()) {}
    }

    /**
     * @param string $key
     * @param array $config
     * @param string $toFile
     *
     * @return bool|null
     *
     * @link https://www.w3.org/TR/css3-images/img_scale.png
     *
     * @throws ExceptionEmpty
     * @throws \InvalidArgumentException
     */
    protected function resize($key, $config, $toFile)
    {

        $file = $this->buildPath($key, true);

        $this->builder->helper()->dir()->make(dirname($file));

        if (is_file($file) &&
            strpos(mime_content_type($file), 'image/') === 0
        ) {

            var_dump($key);

            switch ($key) { // todo: $config[type]
                case 'resize':
                case 'fill':
                    $fill = new Resize();
                    $fill->setDriver($this->driver);
                    $image = $fill->execute($file,[
                        $config['width'],
                        $config['height'],
                    ]);
                    break;
                case 'none':
                    $fill = new None();
                    $fill->setDriver($this->driver);
                    $image = $fill->execute($file,
                        [
                            $config['width'],
                            $config['height'],
                        ],
                        $config['color']
                    );
                    break;
                case 'contain':
                    $fill = new Contain();
                    $fill->setDriver($this->driver);
                    $image = $fill->execute($file,
                        [
                            $config['width'],
                            $config['height'],
                        ],
                        $config['color']
                    );
                    break;
                case 'cover':
                    $fill = new Cover();
                    $fill->setDriver($this->driver);
                    $image = $fill->execute($file,
                        [
                            $config['width'],
                            $config['height'],
                        ]
                    );
                    break;
                default:
                    return false;
            }

            if($this->builder->helper()->dir()->make(dirname($toFile)))
            {

                $image->save($toFile . '_' . $key,
                    isset($config['quality']) ? $config['quality'] : null
                );

                return true;
            }
        }

        return null;
    }

    /**
     * build file path: <user>/<origin|thumbs>/<size_key>/<sub_hash>/<hash>
     *
     * @param string $key
     * @param bool $origin
     *
     * @return null|string
     */
    protected function buildPath($key, $origin = false)
    {
        $_origin = $origin ? '/origin/' : ('/thumbs/' . $key . '/');

        $subpath = 'storage/' . $this->user . $_origin .
            $this->builder->helper()->str()->sub($this->hash, 0, 2) . '/' .
            $this->builder->helper()->str()->sub($this->hash, 2, 2);

        return $this->builder->getRootDir() . $subpath . '/' . $this->hash;
    }

}
