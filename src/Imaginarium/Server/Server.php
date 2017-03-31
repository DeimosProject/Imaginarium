<?php

namespace Deimos\Imaginarium\Server;

use Deimos\Config\ConfigObject;
use Deimos\Helper\Exceptions\ExceptionEmpty;
use Deimos\Imaginarium\Builder;
use Deimos\Imaginarium\ResizeAdapter\Contain;
use Deimos\Imaginarium\ResizeAdapter\Cover;
use Deimos\Imaginarium\ResizeAdapter\Fit;
use Deimos\Imaginarium\ResizeAdapter\None;
use Deimos\Imaginarium\ResizeAdapter\Resize;
use Deimos\ImaginariumSDK\SDK;
use ImageOptimizer\OptimizerFactory;
use Intervention\Image\ImageManager;

class Server
{

    const STATUS_OK    = 0;
    const STATUS_ERROR = 1;

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
     * @param        $builder Builder
     * @param string $user
     */
    public function __construct(Builder $builder, $user)
    {
        $this->worker  = new \GearmanWorker();
        $this->builder = $builder;
        $this->user    = $user;

        $configObject = $builder->config()->get('gearman');

        $this->worker->addServer(
            $configObject->get('host', '127.0.0.1'),
            $configObject->get('port', 4730)
        );
    }

    /**
     * @param string $subName
     */
    public function run($subName)
    {
        $this->worker->addFunction('resize' . $subName, function (\GearmanJob $job)
        {

            $this->driver = $this->builder->config()
                ->get('imageDriver:driver');

            /**
             * @var array
             */
            $params = (array)json_decode($job->workload());

            $this->hash = $params['hash'];

            $config = $this->builder->config()
                ->get('resizer')
                ->get($this->user);

            /**
             * @var ConfigObject
             */
            $this->config = $config;

            if (!$this->sdk)
            {
                $this->sdk = new SDK();
            }

            $this->sdk->setBasedir($this->builder->getRootDir() . 'storage/');
            $this->sdk->setUserName($this->user);
            $this->sdk->setServer('localhost');

            $callback = $config['callback'] ?? [];

            try
            {
                foreach ($this->config as $key => $value)
                {
                    if ($key === 'callback')
                    {
                        continue;
                    }

                    $toFile = $this->sdk->getThumbsPath($key, $this->hash);
                    $this->resize($value, $toFile);
                }
            }
            catch (\Exception $e)
            {
                $this->sendCallback(self::STATUS_ERROR, $callback, $params);

                return false;
            }

            $this->sendCallback(self::STATUS_OK, $callback, $params);

            return true;
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
        $file = $this->sdk->getOriginalPath($this->hash);

        $this->builder->helper()->dir()->make(dirname($file));

        if ($this->isImage($file))
        {
            switch ($config['type'])
            {
                case 'inscribe':
                case 'fit':
                    $adapter = new Fit();
                    $adapter->setDriver($this->driver);
                    $image = $adapter->execute($file, [
                        $config['width'],
                        $config['height'],
                    ]);
                    break;

                case 'resize':
                case 'fill':
                    $adapter = new Resize();
                    $adapter->setDriver($this->driver);
                    $image = $adapter->execute($file, [
                        $config['width'],
                        $config['height'],
                    ]);
                    break;

                case 'none':
                    $adapter = new None();
                    $adapter->setDriver($this->driver);
                    $image = $adapter->execute($file,
                        [
                            $config['width'],
                            $config['height'],
                        ],
                        $config['color'] ?? '#ffffff'
                    );
                    break;

                case 'contain':
                    $adapter = new Contain();
                    $adapter->setDriver($this->driver);
                    $image = $adapter->execute($file,
                        [
                            $config['width'],
                            $config['height'],
                        ],
                        $config['color'] ?? '#ffffff'
                    );
                    break;

                case 'cover':
                    $adapter = new Cover();
                    $adapter->setDriver($this->driver);
                    $image = $adapter->execute($file, [
                        $config['width'],
                        $config['height'],
                    ]);
                    break;

                default:
                    throw new \InvalidArgumentException('Type `' . $config['type'] . '` not found');

            }

            if ($this->builder->helper()->dir()->make(dirname($toFile)))
            {
                // Imagick hack
                $image->save($toFile . '.png', $config['quality'] ?? null);

                rename($toFile . '.png', $toFile);

                if (!isset($config['optimization']['enable']) || $config['optimization']['enable'])
                {
                    $this->optimizationImage($toFile,
                        isset($options['optimization']['options']) ?
                            $options['optimization']['options'] : []
                    );
                }

                return true;
            }
        }

        return null;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function isImage($path)
    {
        return is_file($path) && (strpos(mime_content_type($path), 'image/') === 0);
    }

    /**
     * @param string $path
     *
     * @param array  $options
     */
    public function optimizationImage($path, array $options = [])
    {
        $factory   = new OptimizerFactory($options);
        $optimizer = $factory->get();

        $optimizer->optimize($path);
    }

    /**
     * @param int   $status
     * @param array $callbackConfig
     * @param array $params
     */
    protected function sendCallback($status, array $callbackConfig, array $params)
    {
        if (empty($callbackConfig))
        {
            return;
        }

        if ($status === self::STATUS_ERROR)
        {
            $result = [
                'status' => 'error'
            ];
        }
        else if ($status === self::STATUS_OK)
        {
            $file = $this->sdk->getOriginalPath($this->hash);

            $this->driver = $this->builder->config()
                ->get('imageDriver:driver');

            $manager = new ImageManager(['driver' => $this->driver]);
            $image   = $manager->make($file);

            $result = array_merge([
                'status'   => 'ok',
                'fileSize' => $this->builder->helper()->file()->size($file),
                'sizes'    => [
                    'width'  => $image->width(),
                    'height' => $image->height(),
                ],
                'mime'     => $image->mime(),
            ], $params);
        }

        if (isset($result))
        {
            try
            {
                $this->builder->helper()
                    ->send()
                    ->data($result)
                    ->method('POST')
                    ->to($callbackConfig['url'] . '?hash=' . $callbackConfig['secret'])
                    ->exec();
            }
            catch (\Throwable $e)
            {
                echo $e->getMessage(), PHP_EOL;
            }
        }
    }

}
