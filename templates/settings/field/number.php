<?php

$model = new \JustFastImages\Model\SettingsModel();

$option_name = $model->get_option_name( $args['setting'] );

$option_value = $model->get_value( $args['setting'] );
if ( '' === $option_value ) {
    $option_value = $args['default'];
}

?>

<p>
    <div>
        <input 
            id="<?php echo esc_attr( $option_name ); ?>"
            name="<?php echo esc_attr( $option_name ); ?>"
            type="number"
            value="<?php echo esc_attr( $option_value ); ?>"
            min="<?php echo esc_html( $args['min'] ?? 0 ) ?>"
            max="<?php echo esc_html( $args['max'] ?? 0 ) ?>">
    </div>
    <div>
        <label for="<?php echo esc_attr( $option_name ); ?>">
            <?php echo $args['desc']; ?>
        </label>
    </div>
</p>
