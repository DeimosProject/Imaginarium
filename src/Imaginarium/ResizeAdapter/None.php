<?php

namespace Deimos\Imaginarium\ResizeAdapter;

use Intervention\Image\Image;

class None extends AbstractDriver
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

        list($width, $height) = $sizes;

        $widthFit  = $image->width() >= $image->height() ? $width : null;
        $heightFit = $image->width() <= $image->height() ? $height : null;

        if ($widthFit === null)
        {
            // vertical image
            $image->rotate(90)
                ->fit($heightFit, $widthFit)
                ->rotate(-90);
        }
        else
        {
            // horizontal image
            $image->fit($widthFit, $heightFit);
        }

        $pixel = new \ImagickPixel($color);

        $fill = $this->manager()
            ->canvas($width, $height, $pixel);

        $image->resizeCanvas($width, $height, 'center', false, $pixel);

        $sizes = $this->getFillSizes($fill, $image);

        $fill->fill($image, $sizes[0], $sizes[1]);

        return $fill;
    }

    protected function getFillSizes(Image $fill, Image $image)
    {
        $x = ($fill->height() - $image->width()) / 2;
        $y = ($fill->height() - $image->height()) / 2;

        return [
            (int)$x,
            (int)$y
        ];
    }

}
