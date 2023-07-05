<?php

namespace AnonymiseAttachments\Controller;

class AssetRouteController {

    public function __construct() {

        add_action( 'init', [ $this, 'add_asset_routes' ] );

    }

    public function add_asset_routes() {

        $this->add_route( '^asset/([0-9]+)$', [ $this, 'handle_asset_route' ] );
        $this->add_route( '^asset/([0-9]+)/([-_a-zA-Z0-9]+)$', [ $this, 'handle_asset_route' ] );

    }

    public function handle_asset_route( $attachment_id, $image_size = '' ) {

        // Get the attachment file path.

        $file_path = get_attached_file( $attachment_id );

        if ( $image_size ) {
            
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

        }

        // Get the attachment mime type.

        $mime_type = get_post_mime_type( $attachment_id );

        // Serve the file directly.

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
                'Cache-Control: max-age=%d',
                30 * 24 * 60 * 60 // 30 days.
            )
        );

        readfile( $file_path );

    }

    protected function add_route( $regex, $callback ) {

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $request_uri = trim( $request_uri, '/' );

        $matched = preg_match( '#' . $regex . '#', $request_uri, $matches );
        if ( $matched ) {
            call_user_func_array( $callback, array_slice( $matches, 1 ) );
            exit;
        }

    }

}
