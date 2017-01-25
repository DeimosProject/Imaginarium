<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\ImageManager;
use Intervention\Image\Image;

abstract class AbstractDriver
{
    protected $driver = 'gd';
    /**
     * @var ImageManager
     */
    private $manager;

    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    protected function manager()
    {
        if(!$this->manager) {

            $this->manager = new ImageManager(['driver' => $this->driver]);
        }

        return $this->manager;
    }

    protected function make($path)
    {
        return $this->manager()->make($path);
    }

    /**
     * @param Image $image
     * @param array $config [0 => 'width', 1 => 'height']
     * @param bool $minimal
     * @return array
     */
    protected function coverHelper(Image $image, array $config, $minimal = true)
    {
        list($width, $height) = $config;

        if ($image->height() < $image->width())
        {
            $_width = $width;
            $_height = $image->height() * $width / $image->width();

            $sw = 0;
            $sh = ($_height - $height) / 2;
        }
        else
        {
            $_width = $image->width() * $height / $image->height();
            $_height = $height;

            $sw = ($_width - $width) / 2;
            $sh = 0;
        }

        // todo: code refactor
        if(!$minimal)
        {
            if((int)$_width > $width)
            {
                $_height = $_height * $width / $_width;
                $_width = $width;
            }

            if((int)$_height > $height)
            {
                $_width = $_width * $height / $_height;
                $_height = $height;
            }
        }
        else
        {
            if((int)$_width < $width)
            {
                $_height = $_height * $width / $_width;
                $_width = $width;
            }

            if((int)$_height < $height)
            {
                $_width = $_width * $height / $_height;
                $_height = $height;
            }
        }

        return [
            $config[0],
            $config[1],
            'width' => (int)$_width,
            'height' => (int)$_height,
            'shift' => [
                'width' => (int)$sw,
                'height' => (int)$sh,
            ]
        ];
    }

    /**
     * @param Image $image
     * @param array $sizes
     * @param \ImagickPixel $color
     * @return Image
     */
    protected function _resize(Image $image, array $sizes, \ImagickPixel $color)
    {
        $image->resize($sizes['width'], $sizes['height'])
            ->resizeCanvas($sizes[0], $sizes[1], 'center', false, $color);

        $fill = $this->manager()
            ->canvas($sizes[0], $sizes[1], $color);

        $fill->fill($image, $sizes['shift']['width'], $sizes['shift']['height']);

        return $fill;
    }

}
