<?php
/**
 * Plugin Name: Just Anonymise Attachments
 * Version:     1.0.0
 * Author:      Zac Scott
 * Author URI:  https://zacscott.net
 * Description: Hide attachment paths from the public.
 * Text Domain: just-anonymise-attachments
 */

require dirname( __FILE__ ) . '/vendor/autoload.php';

define( 'JUST_ANONYMISE_ATTACHMENTS_PLUGIN_ABSPATH', dirname( __FILE__ ) );
define( 'JUST_ANONYMISE_ATTACHMENTS_PLUGIN_ABSURL', plugin_dir_url( __FILE__ )  );

// Boot each of the plugin logic controllers.
new \AnonymiseAttachments\Controller\AnonymiseAttachmentsController();
new \AnonymiseAttachments\Controller\AssetRouteController();
