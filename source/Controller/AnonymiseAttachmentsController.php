<?php

namespace AnonymiseAttachments\Controller;

class AnonymiseAttachmentsController {

    /**
     * Lock to prevent infinite recursion when filtering the default image.
     * @var bool
     */
    protected $lock = false;

    public function __construct() {
    
        add_filter( 'wp_get_attachment_url', [ $this, 'rewrite_attachment_urls' ], PHP_INT_MAX, 2 );
        add_filter( 'wp_get_attachment_image_src', [ $this, 'rewrite_image_urls' ], PHP_INT_MAX, 4 );

    }

    public function rewrite_attachment_urls( $url, $attachment_id ) {

        if ( ! $this->lock ) {
            $this->lock = true;

            $url = $this->get_attachment_url( $attachment_id );

            $this->lock = false;
        }

        return $url;

    }

    public function rewrite_image_urls( $image, $attachment_id, $size, $icon ) {

        if ( ! $this->lock ) {
            $this->lock = true;

            $url = $this->get_attachment_url( $attachment_id, $size );
            $image[0] = $url;

            $this->lock = false;
        }

        return $image;

    }

    protected function get_attachment_url( $attachment_id, $size = '' ) {

        if ( $size ) {

            $attachment_path = sprintf(
                'asset/%d/%s',
                $attachment_id,
                $size
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
