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

        $this->serve_file( $file_path, $mime_type );

    }

    public function handle_image_route( $image_size, $attachment_id ) {

        // Limit the image sizes if configured.

        if ( 'full' === $image_size ) {
            $image_size = 'large'; // TODO setting.
        }

        if ( 'post-thumbnail' === $image_size ) {
            $image_size = 'medium'; // TODO setting.
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

        $is_image = preg_match( '#^image/#', $mime_type );

        // Change mime type if converting images to webp.
        if ( $is_image ) {
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
                'Content-Length: %s',
                filesize( $file_path )
            )
        );

        header(
            sprintf(
                'Cache-Control: max-age=%d',
                60 * 24 * 60 * 60 // 60 days.
            )
        );

        // Serve the file.

        if ( $is_image ) {
            // If the file is an image, convert it to webp.

            $file_content =  file_get_contents( $file_path );

            $image = imagecreatefromstring( $file_content );
            imagewebp( $image, null, 80 ); // TODO setting

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
            AnonymiseAttachmentsController::disable();

            call_user_func_array( $callback, array_slice( $matches, 1 ) );
            exit;
        }

    }

}
