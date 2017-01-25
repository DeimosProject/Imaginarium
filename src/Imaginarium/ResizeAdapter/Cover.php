<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;

class Cover extends AbstractDriver
{

    /**
     * @param string $path
     * @param array  $sizes [0 => 'width', 1 => 'height']
     *
     * @return Image
     */
    public function execute($path, array $sizes)
    {
        $image = $this->make($path);

        $sizes = $this->coverHelper($image, $sizes);

        return $this->_resize($image, $sizes, new \ImagickPixel('rgba(0,0,0,0)'));
    }

}
