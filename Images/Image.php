<?php
// TODO : Dynamic Image Sizes

namespace Undefined\Core\Images;

/**
 * Image Helper
 *
 * @name Image
 * @since 1.0.0
 * @package Undefined\Core\Images
 */
class Image
{
    /**
     * Image Sizes
     * @var array
     */
    protected $_imageSizes = [
        [
            'name' => 'big_thumbnail',
            'width' => 100,
            'height' => 100,
            'crop' => true
        ]
    ];

    public function __construct()
    {
        add_filter('image_resize_dimensions', array(&$this, 'image_crop_dimensions'), 10, 6);

        foreach ($this->_imageSizes as $imageSize) {
            add_image_size( $imageSize['name'], $imageSize['width'], $imageSize['height'], !empty($imageSize['crop']) );
        }
    }

    /**
     * Upscale Images
     *
     * @param $default
     * @param $orig_w
     * @param $orig_h
     * @param $new_w
     * @param $new_h
     * @param $crop
     * @return array|null
     */
    public function image_crop_dimensions($default, $orig_w, $orig_h, $new_w, $new_h, $crop){
        if ( !$crop ) return null; // let the wordpress default function handle this

        $aspect_ratio = $orig_w / $orig_h;
        $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

        $crop_w = round($new_w / $size_ratio);
        $crop_h = round($new_h / $size_ratio);

        $s_x = floor( ($orig_w - $crop_w) / 2 );
        $s_y = floor( ($orig_h - $crop_h) / 2 );

        return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
    }

    public function getImagesSizes(){
        return $this->_imageSizes;
    }
}