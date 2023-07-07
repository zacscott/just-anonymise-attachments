<?php

namespace AnonymiseAttachments\Controller;

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

        readfile( $file_path );

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
