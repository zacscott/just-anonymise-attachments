<?php

namespace JustFastImages\Controller;

class AssetRouteController {

    public function __construct() {

        add_action( 'init', [ $this, 'add_asset_routes' ] );

    }

    public function add_asset_routes() {

        $this->add_route( '^asset/([0-9]+)$', [ $this, 'handle_attachment_route' ] );
        $this->add_route( '^asset/([-_a-zA-Z0-9]+)/([0-9]+)$', [ $this, 'handle_image_route' ] );

    }

    public function handle_attachment_route( $attachment_id ) {

        $file_path = get_attached_file( $attachment_id );
        $mime_type = get_post_mime_type( $attachment_id );

        if ( $this->is_image_mime( $mime_type ) ) {
            // If is image, pass it along to the image route handler.

            $this->handle_image_route( 'full', $attachment_id );

        } else {
            // Otherwise serve file directly.

            $this->serve_file( $file_path, $mime_type );

        }

    }

    public function handle_image_route( $image_size, $attachment_id ) {

        // Limit the image sizes if configured.
        if ( 'full' === $image_size ) {

            $full_image_limit = $this->get_setting_full_image_limit();
            if ( $full_image_limit ) {
                $image_size = $full_image_limit;
            }

        } else if ( 'post-thumbnail' === $image_size ) {
                
            $featured_image_limit = $this->get_setting_featured_image_limit();
            if ( $featured_image_limit ) {
                $image_size = $featured_image_limit;
            }

        }

        $file_path = get_attached_file( $attachment_id );
        $mime_type = get_post_mime_type( $attachment_id );

        $this->serve_file( $file_path, $mime_type );

    }

    protected function serve_file( $file_path, $mime_type ) {

        if ( ! $file_path ) {
            status_header( 404 );
            exit;
        }

        if ( $this->is_image_mime( $mime_type ) ) {
            // If the file is an image, convert & resize it dynamically.

            $this->serve_image_file( $file_path, $mime_type );

        } else {
            // Otherwise, serve the file directly.

            $this->serve_raw_file( $file_path, $mime_type );

        }

    }

    protected function serve_image_file( $file_path, $mime_type ) {

        $image = wp_get_image_editor( $file_path );
        if ( ! is_wp_error( $image ) ) {
            // Image loaded by WP, convert & resize it.

            // $image->resize( 500, NULL, false );
            $image->set_quality( $this->get_setting_webp_quality() );

            $this->set_cache_headers();
            $image->stream( 'image/webp' );

        } else {
            // If the image could not be loaded, serve the original file as a fallback.

            $this->serve_raw_file( $file_path, $mime_type );

        }

    }

    protected function serve_raw_file( $file_path, $mime_type ) {

        header(
            sprintf(
                'Content-Type: %s',
                $mime_type
            )
        );

        $this->set_cache_headers();

        readfile( $file_path );

    }

    protected function set_cache_headers() {

        $cache_expires = 60 * 60 * 24 * 365; // 1 year.
        $cache_expires = apply_filters( 'just_fast_images_cache_expires', $cache_expires );

        header(
            sprintf(
                'Cache-Control: public, max-age=%d',
                $cache_expires
            )
        );

    }

    protected function add_route( $regex, $callback ) {

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $request_uri = trim( $request_uri, '/' );

        $matched = preg_match( '#' . $regex . '#', $request_uri, $matches );
        if ( $matched ) {

            // Disable the attachment rewriting so that we get the original file path.
            RewriteAttachmentsController::disable();

            call_user_func_array( $callback, array_slice( $matches, 1 ) );
            exit;
        }

    }

    protected function get_setting_webp_quality() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'webp_quality', 80 );

        return $option_value;

    }

    protected function get_setting_full_image_limit() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'full_image_limit' );

        return $option_value;

    }

    protected function get_setting_featured_image_limit() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'featured_image_limit' );

        return $option_value;

    }

    protected function is_image_mime( $mime_type ) {

        $is_image = false;

        if ( preg_match( '#^image/#', $mime_type ) ) {
            $is_image = true;
        }

        return $is_image;

    }

}
