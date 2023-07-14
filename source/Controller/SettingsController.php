<?php

namespace JustFastImages\Controller;

/**
 * Responsible for handling the settings page for the plugin.
 * 
 * @package JustFastImages\Controller
 */
class SettingsController {

    /**
     * The plugin settings options, set by set_settings_config().  
     * @var array $settings
     */
    protected $settings = [];

    public function __construct() {
        
        add_action( 'admin_init', [ $this, 'set_settings_config' ], -1 );
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings_section' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );

    }

    /**
     * Set the configuration used to generate the options presented on the settings page.
     * 
     * @return void
     */
    public function set_settings_config() {

        $this->settings = [
            [
                'setting' => 'webp_quality',
                'type'    => 'number',
                'min'     => 0,
                'max'     => 100,
                'default' => 80,
                'label'   => __( 'WEBP Quality', 'just-fast-images' ),
                'desc'    => __( 'Quality of WEBP conversion if enabled (0=worst, 100=best).', 'just-fast-images' ),
            ],
            [
                'setting' => 'full_image_limit',
                'type'    => 'select',
                'options' => [
                    ''          => 'no change',
                    'large'     => 'large',
                    'medium'    => 'medium',
                    'thumbnail' => 'thumbnail',
                ],
                'label'   => __( 'Limit full image size', 'just-fast-images' ),
                'desc'    => __( 'Convert full sized images to this size.', 'just-fast-images' ),
            ],
            [
                'setting' => 'featured_image_limit',
                'type'    => 'select',
                'options' => [
                    ''          => 'no change',
                    'large'     => 'large',
                    'medium'    => 'medium',
                    'thumbnail' => 'thumbnail',
                ],
                'label'   => __( 'Limit featued image size', 'just-fast-images' ),
                'desc'    => __( 'Convert featured images to this size.', 'just-fast-images' ),
            ],
        ];

    }

    /**
     * Register the settings page in wp-admin.
     * 
     * @return void
     */
    public function register_settings_page() {

        add_submenu_page(
            'options-general.php',
            __( 'Just Fast Images', 'just-fast-images' ),
            __( 'Just Fast Images', 'just-fast-images' ),
            'manage_options',
            'just_fast_images',
            [ $this, 'render_settings_page' ]
        );

    }
    
    /**
     * Render the settings page.
     * 
     * @return void
     */
    public function render_settings_page() {

        // Ensure current user has access.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Show a notice when settings are saved.
        if ( isset( $_POST['update'] ) ) {

            add_settings_error(
                'just_fast_images_messages',
                'just_fast_images_message',
                __( 'Settings Saved', 'just-fast-images' ),
                'updated'
            );

        }

        // Render the settings page.

        $template_path = sprintf(
            '%s/templates/settings/page.php',
            JUST_FAST_IMAGES_PLUGIN_ABSPATH
        );

        include $template_path;

    }

    /**
     * Register the settings section within the setting page.
     * 
     * @return void
     */
    public function register_settings_section() {
        
        add_settings_section(
            'just_fast_images_section',
            '',
            [ $this, 'render_settings_section' ],
            'just_fast_images'
        );

    }

    /**
     * Render the settings section within the setting page.
     * 
     * @return void
     */
    public function render_settings_section( $args ) {

        $template_path = sprintf(
            '%s/templates/settings/section.php',
            JUST_FAST_IMAGES_PLUGIN_ABSPATH
        );

        include $template_path;

    }

    /**
     * Register each of the settings/options within the settings section.
     * 
     * @return void
     */
    public function register_settings() {

        $model = new \JustFastImages\Model\SettingsModel();

        foreach ( $this->settings as $setting ) {

            $option_name = $model->get_option_name( $setting['setting'] );

            register_setting( 'just_fast_images', $option_name );

            add_settings_field(
                $option_name, 
                $setting['label'],
                [ $this, 'render_settings_field' ],
                'just_fast_images',
                'just_fast_images_section',
                $setting
            );

        }
        
    }

    /**
     * Render the given setting/option within the settings section.
     * 
     * @return void
     */
    public function render_settings_field( $args ) {
        
        $template_path = sprintf(
            '%s/templates/settings/field/%s.php',
            JUST_FAST_IMAGES_PLUGIN_ABSPATH,
            $args['type']
        );

        include $template_path;

    }

}
