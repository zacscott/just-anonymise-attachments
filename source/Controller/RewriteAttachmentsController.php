<?php

namespace JustFastImages\Controller;

use JustFastImages\Library\MediaHelper;

class RewriteAttachmentsController {

    /**
     * Lock to prevent infinite recursion when filtering the default image.
     * @var bool
     */
    protected $lock = false;

    /**
     * Whether the controller is enabled.
     * @var bool
     */
    protected static $enabled = true;

    /**
     * Disable the controller.
     */
    public static function disable() {

        self::$enabled = false;

    }

    public function __construct() {

        add_filter( 'wp_get_attachment_url', [ $this, 'wp_get_attachment_url' ], PHP_INT_MAX, 2 );
        add_filter( 'wp_get_attachment_image_src', [ $this, 'wp_get_attachment_image_src' ], PHP_INT_MAX, 4 );
        add_filter( 'wp_get_attachment_metadata', [ $this, 'wp_get_attachment_metadata' ], PHP_INT_MAX, 2 );
        add_filter( 'wp_get_attachment_image_attributes', [ $this, 'wp_get_attachment_image_attributes' ], PHP_INT_MAX, 3 );
        add_filter( 'wp_content_img_tag', [ $this, 'wp_content_img_tag' ], PHP_INT_MAX, 3 );

    }

    public function wp_get_attachment_url( $url, $attachment_id ) {

        if ( ! self::$enabled ) {
            return $url;
        }

        if ( ! $this->lock ) {
            $this->lock = true;

            $url = $this->get_attachment_url( $attachment_id );

            $this->lock = false;
        }

        return $url;

    }

    public function wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {

        if ( ! self::$enabled ) {
            return $image;
        }

        if ( is_array( $size ) ) {
            $size = array_shift( $size );
        }

        if ( ! $this->lock ) {
            $this->lock = true;

            $url = $this->get_attachment_url( $attachment_id, $size );
            $image[0] = $url;

            $this->lock = false;
        }

        return $image;

    }

    public function wp_get_attachment_metadata( $data, $attachment_id ) {

        if ( ! self::$enabled ) {
            return $data;
        }

        $data['file'] = "$attachment_id";

        $media_helper = new MediaHelper();
        $image_sizes = $media_helper->get_image_sizes();

        foreach ( $image_sizes as $size => $size_data ) {
            $data['sizes'][$size]['file'] = "$size/$attachment_id";
        }

        return $data;

    }

    public function wp_get_attachment_image_attributes( $attrs, $attachment, $size ) {

        $max_width = 0;

        $media_helper = new MediaHelper();
        $image_sizes = $media_helper->get_image_sizes();

        $srcsets = [];
        foreach ( $image_sizes as $size => $size_data ) {

            if ( $size_data && $size_data['width'] ) {

                $image = wp_get_attachment_image_src( $attachment->ID, $size );
                if ( $image ) {

                    $srcsets[] = sprintf(
                        '%s %dw',
                        $image[0],
                        $size_data['width']
                    );

                    if ( $size_data['width'] > $max_width ) {
                        $max_width = $size_data['width'];
                    }

                }

            }

        }

        $attrs['srcset'] = implode( ', ', $srcsets );

        $attrs['sizes']  = sprintf(
            '(max-width: %dpx) 100vw, %dpx',
            $max_width,
            $max_width
        );

        return $attrs;

    }

    public function wp_content_img_tag($html, $context, $attachment_id) {

        if ( 'the_content' === $context ) {
            $html = wp_get_attachment_image(
                $attachment_id,
                $this->get_setting_content_image_size()
            );
        }
    
        return $html;
    
    }

    protected function get_setting_content_image_size() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'content_image_size' );

        return $option_value;

    }

    protected function get_attachment_url( $attachment_id, $size = '' ) {

        if ( $size ) {

            $attachment_path = sprintf(
                'asset/%s/%d',
                $size,
                $attachment_id
            );

        } else {

            $attachment_path = sprintf(
                'asset/%d',
                $attachment_id
            );

        }

        $url = home_url( $attachment_path );

        return $url;

    }

}
