<?php

namespace JustFastImages\Controller;

class LimitImageSizeController {

    /**
     * Lock to prevent infinite recursion when filtering the default image.
     * @var bool
     */
    protected $lock = false;

    const LIMIT_FULL_SIZE_TO     = 'large';
    const LIMIT_FEATURED_SIZE_TO = 'medium';

    public function __construct() {
    
        add_filter( 'wp_get_attachment_image_src', [ $this, 'wp_get_attachment_image_src' ], -1, 3 );
        add_filter( 'image_get_intermediate_size', [ $this, 'image_get_intermediate_size' ], -1, 3 );

    }

    public function wp_get_attachment_image_src( $image, $attachment_id, $size ) {

        if ( ! $this->lock ) {
            $this->lock = true;

            if ( 'full' === $size ) {
                $image = wp_get_attachment_image_src( $attachment_id, self::LIMIT_FULL_SIZE_TO );
            }

            if ( 'post-thumbnail' === $size ) {
                $image = wp_get_attachment_image_src( $attachment_id, self::LIMIT_FEATURED_SIZE_TO );
            }

            $this->lock = false;
        }

        return $image;

    }

    public function image_get_intermediate_size( $image, $attachment_id, $size ) {

        if ( ! $this->lock ) {
            $this->lock = true;

            if ( 'full' === $size ) {
                $image = image_get_intermediate_size( $attachment_id, self::LIMIT_FULL_SIZE_TO );
            }

            if ( 'post-thumbnail' === $size ) {
                $image = image_get_intermediate_size( $attachment_id, self::LIMIT_FEATURED_SIZE_TO );
            }

            $this->lock = false;
        }

        return $image;

    }

}
