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

        }

        if ( 'post-thumbnail' === $image_size ) {
                
            $featured_image_limit = $this->get_setting_featured_image_limit();
            if ( $featured_image_limit ) {
                $image_size = $featured_image_limit;
            }

        }

        // As a fallback, serve the original image.
        $file_path = get_attached_file( $attachment_id );

        // Get the image size file path if it exists.
        $image = image_get_intermediate_size( $attachment_id, $image_size );
        if ( $image && isset( $image['path'] ) ) {

            $upload_dir = wp_upload_dir();

            $file_path = sprintf(
                '%s/%s',
                $upload_dir['basedir'],
                $image['path']
            );

            $file_path = realpath( $file_path );
        }

        $mime_type = get_post_mime_type( $attachment_id );

        $this->serve_file( $file_path, $mime_type );

    }

    protected function serve_file( $file_path, $mime_type ) {

        if ( ! $file_path ) {
            status_header( 404 );
            exit;
        }

        $is_image = $this->is_image_mime( $mime_type );

        // Change mime type if converting images to webp.
        if ( $is_image && $this->get_setting_webp_convert() ) {
            $mime_type = 'image/webp';
        }

        // Set the headers.

        header(
            sprintf(
                'Content-Type: %s',
                $mime_type
            )
        );

        header(
            sprintf(
                'Cache-Control: max-age=%d',
                60 * 24 * 60 * 60 // 60 days.
            )
        );

        // Serve the file.

        if ( $is_image && $this->get_setting_webp_convert() ) {
            // If the file is an image, convert it to webp.

            $file_content =  file_get_contents( $file_path );

            $image = imagecreatefromstring( $file_content );
            imagewebp( $image, null, $this->get_setting_webp_quality() );

        } else {
            // Otherwise, serve the file directly.

            readfile( $file_path );

        }

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

    protected function get_setting_webp_convert() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'webp_convert' );

        return $option_value;

    }

    protected function get_setting_webp_quality() {
        
        $model = new \JustFastImages\Model\SettingsModel();

        $option_value = $model->get_value( 'webp_quality' );

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
