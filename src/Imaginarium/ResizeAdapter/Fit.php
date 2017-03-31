<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;

class Fit extends AbstractDriver
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

        $width  = $sizes[0] >= $sizes[1] ? $sizes[1] : null;
        $height = $sizes[0] > $sizes[1] ? $sizes[0] : null;

        $image->resize($width, $height, function ($constraint)
        {
            $constraint->aspectRatio();
        });

        return $image;
    }

}