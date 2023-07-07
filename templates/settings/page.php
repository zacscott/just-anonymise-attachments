<?php

// Render the settings template.
settings_errors( 'just_fast_images_messages' );
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
        <?php settings_fields( 'just_fast_images' ); ?>
        <?php do_settings_sections( 'just_fast_images' ); ?>
        <hr><br>
        <?php submit_button( 'Save' ); ?>
    </form>
</div>
