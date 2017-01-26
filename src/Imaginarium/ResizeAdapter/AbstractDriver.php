<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

abstract class AbstractDriver
{

    protected $driver = 'gd';

    /**
     * @var ImageManager
     */
    private $manager;

    /**
     * @param $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $path
     *
     * @return Image
     */
    protected function make($path)
    {
        return $this->manager()->make($path);
    }

    /**
     * @return ImageManager
     */
    protected function manager()
    {
        if (!$this->manager)
        {
            $this->manager = new ImageManager(['driver' => $this->driver]);
        }

        return $this->manager;
    }

    /**
     * @param Image $image
     * @param array $config [0 => 'width', 1 => 'height']
     * @param bool  $minimal
     *
     * @return array
     */
    protected function coverHelper(Image $image, array $config, $minimal = true)
    {
        list($width, $height) = $config;

        $sw = 0;
        $sh = 0;

        $_width  = $width;
        $_height = $height;

        if ($image->height() < $image->width())
        {
            $_height = $image->height() * $width / $image->width();

            $sh = ($_height - $height) / 2;
        }
        else
        {
            $_width = $image->width() * $height / $image->height();

            $sw = ($_width - $width) / 2;
        }

        if ($minimal ^ $_width > $width)
        {
            $_height = $_height * $width / $_width;
            $_width  = $width;
        }

        if ($minimal ^ $_height > $height)
        {
            $_width  = $_width * $height / $_height;
            $_height = $height;
        }

        return [
            $config[0],
            $config[1],
            'width'  => (int)$_width,
            'height' => (int)$_height,
            'shift'  => [
                'width'  => (int)$sw,
                'height' => (int)$sh,
            ]
        ];
    }

    /**
     * @param Image         $image
     * @param array         $sizes
     * @param \ImagickPixel $color
     *
     * @return Image
     */
    protected function _resize(Image $image, array $sizes, \ImagickPixel $color)
    {
        $image
            ->resize($sizes['width'], $sizes['height'])
            ->resizeCanvas($sizes[0], $sizes[1], 'center', false, $color);

        $fill = $this->manager()
            ->canvas($sizes[0], $sizes[1], $color);

        $fill->fill($image, $sizes['shift']['width'], $sizes['shift']['height']);

        return $fill;
    }

}
