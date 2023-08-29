<?php

namespace JustFastImages\Library;

/** 
 * Helper methods for managing media in WordPress.
 * 
 * @package JustFastImages\Library
 */
class MediaHelper {

    public function get_image_sizes() {

		$wp_additional_image_sizes = wp_get_additional_image_sizes();

		$image_sizes = array();

		foreach ( get_intermediate_image_sizes() as $size ) {

			$image_sizes[ $size ]['label'] = $size;

			if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {

				$image_sizes[ $size ]['width']  = (int) get_option( $size . '_size_w' );
				$image_sizes[ $size ]['height'] = (int) get_option( $size . '_size_h' );
				$image_sizes[ $size ]['crop']   = ( 'thumbnail' === $size ) ? (bool) get_option( 'thumbnail_crop' ) : false;

			} elseif ( ! empty( $wp_additional_image_sizes ) && ! empty( $wp_additional_image_sizes[ $size ] ) ) {

				$image_sizes[ $size ]['width']  = (int) $wp_additional_image_sizes[ $size ]['width'];
				$image_sizes[ $size ]['height'] = (int) $wp_additional_image_sizes[ $size ]['height'];
				$image_sizes[ $size ]['crop']   = (bool) $wp_additional_image_sizes[ $size ]['crop'];

			}

		}

		return $image_sizes;

	}

}
