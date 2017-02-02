<?php

namespace Deimos\Imaginarium\Server;

use Deimos\Config\ConfigObject;
use Deimos\Helper\Exceptions\ExceptionEmpty;
use Deimos\Imaginarium\Builder;
use Deimos\Imaginarium\ResizeAdapter\Contain;
use Deimos\Imaginarium\ResizeAdapter\Cover;
use Deimos\Imaginarium\ResizeAdapter\None;
use Deimos\Imaginarium\ResizeAdapter\Resize;
use ImageOptimizer\OptimizerFactory;
use Deimos\ImaginariumSDK\SDK;

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
     * @var SDK
     */
    protected $sdk;

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

            if(!$this->sdk) {

                $this->sdk = new SDK();
            }

            $this->sdk->setBasedir($this->builder->getRootDir() . 'storage/');
            $this->sdk->setUserName($this->user);
            $this->sdk->setServer('localhost');

            foreach ($this->config as $key => $value)
            {
                $toFile = $this->sdk->getThumbsPath($key, $this->hash);

                $this->resize($value, $toFile);
            }
        });

        while ($this->worker->work())
        {
        }
    }

    /**
     * @param array  $config
     * @param string $toFile
     *
     * @return bool|null
     *
     * @link https://www.w3.org/TR/css3-images/img_scale.png
     * @link https://github.com/psliwa/image-optimizer
     *
     * @throws ExceptionEmpty
     * @throws \InvalidArgumentException
     */
    protected function resize($config, $toFile)
    {
        $file = $this->builder->buildStoragePath($this->user, $this->hash);
        $file = $this->sdk->getOriginalPath($this->hash);

        $this->builder->helper()->dir()->make(dirname($file));

        if ($this->isImage($file))
        {

            switch ($config['type'])
            {
                case 'resize':
                case 'fill':
                    $fill = new Resize();
                    $fill->setDriver($this->driver);
                    $image = $fill->execute($file, [
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

            if ($this->builder->helper()->dir()->make(dirname($toFile)))
            {
                $image->save($toFile,
                    isset($config['quality']) ?
                        $config['quality'] :
                        null
                );

                if (!isset($config['optimization']['enable']) || $config['optimization']['enable'])
                {
                    $this->optimizationImage($toFile,
                        isset($options['optimization']['options']) ?
                            $options['optimization']['options'] :
                            []
                    );
                }

                return true;
            }
        }

        return null;
    }

    public function isImage($path)
    {
        return is_file($path) && (strpos(mime_content_type($path), 'image/') === 0);
    }

    public function optimizationImage($path, $options = [])
    {
        $factory   = new OptimizerFactory($options);
        $optimizer = $factory->get();

        $optimizer->optimize($path);
    }

}
