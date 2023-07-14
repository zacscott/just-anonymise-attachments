<?php

namespace JustFastImages\Controller;

class DisableCoreImageResizingController {

    public function __construct() {

        add_filter( 'intermediate_image_sizes_advanced', [ $this, 'intermediate_image_sizes_advanced' ] );

    }

    public function intermediate_image_sizes_advanced( $new_sizes ) {

        return [];

    }

}
