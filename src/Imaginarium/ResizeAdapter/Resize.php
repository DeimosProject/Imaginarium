<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;

class Resize extends AbstractDriver
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

        $image->resize($sizes[0], $sizes[1]);

        return $image;
    }

}