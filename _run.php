<?php

include_once __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Image;

function coverHelper(Image &$image, $config)
{
    $width = $config['width'];
    $height = $config['height'];

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

    return [
        'width' => (int)$_width,
        'height' => (int)$_height,
        'shift' => [
            'width' => (int)$sw,
            'height' => (int)$sh,
        ]
    ];
}

$cover = [300, 50];

$manager = new ImageManager(array('driver' => 'imagick'));

$image = $manager
    ->make('storage/1');

$sizes = coverHelper($image, ['width' => $cover[0], 'height' => $cover[1]]);

$image->resize($sizes['width'], $sizes['height'])
    ->resizeCanvas($cover[0], $cover[1]);

$fill = $manager
    ->canvas($cover[0], $cover[1], '#ffffff');

$fill->fill($image, $sizes['shift']['width'], $sizes['shift']['height']);

$image->save('1_w_i.png', 75);
$fill->save('1_w.png', 75);
