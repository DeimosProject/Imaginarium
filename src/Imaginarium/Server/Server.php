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
        $this->worker = new \GearmanWorker();
        $this->worker->addServer();
        $this->builder = $builder;
        $this->user    = $user;
    }

    /**
     * @param string $subName
     */
    public function run($subName = '')
    {
        $this->worker->addFunction('resize' . $subName, function (\GearmanJob $job)
        {
            $this->driver = $this->builder->config()
                ->get('image_driver')->get('driver');

            /**
             * @var array
             */
            $params = (array)json_decode($job->workload());

            $this->hash = $params['hash'];

            $config = $this->builder
                ->config()
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

            $callback = isset($config['callback']) ? $config['callback'] : [];

            try
            {
                foreach ($this->config as $key => $value)
                {
                    if($key !== 'callback')
                    {
                        $toFile = $this->sdk->getThumbsPath($key, $this->hash);

                        $this->resize($value, $toFile);
                    }
                }
            }
            catch (\Exception $e)
            {
                $this->sendCallback(self::STATUS_ERROR, $callback);

                return false;
            }

            $this->sendCallback(self::STATUS_OK, $callback);

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
                $image->save($toFile . '.png',
                    isset($config['quality']) ?
                        $config['quality'] :
                        null
                );

                rename($toFile.'.png', $toFile);

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
     */
    protected function sendCallback($status, array $callbackConfig)
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
        // todo: слать 100500 раз, пока нам не пришлютЪ ОК
        if ($status === self::STATUS_OK)
        {
            $result = [
                'status' => 'ok',
                'sizes' => [
                    'width' => 0,
                    'height' => 0,
                ],
                //'' // more data
            ];
        }

        if (isset($result))
        {
            try {

                $this->builder->helper()
                    ->send()
                    ->data($result)
                    ->method('POST')
                    ->to($callbackConfig['url'] . '?hash=' . $callbackConfig['secret'])
                    ->exec();
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }
    }

}
