<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;

class Contain extends AbstractDriver
{
    /**
     * @param string $path
     * @param array  $sizes [0 => 'width', 1 => 'height']
     * @param string $color
     *
     * @return Image
     */
    public function execute($path, array $sizes, $color)
    {
        $image = $this->make($path);

        $sizes = $this->coverHelper($image, $sizes, false);

        return $this->_resize($image, $sizes, new \ImagickPixel($color));
    }

}
