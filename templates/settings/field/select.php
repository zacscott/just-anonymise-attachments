<?php

$model = new \JustFastImages\Model\SettingsModel();

$option_name = $model->get_option_name( $args['setting'] );

$option_value = $model->get_value( $args['setting'] );

?>

<p>
    <div>
        <select 
            id="<?php echo esc_attr( $option_name ); ?>"
            name="<?php echo esc_attr( $option_name ); ?>"
            type="number">

            <?php foreach ( $args['options'] as $value => $label ) : ?>
                <option 
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php if ( $value === $option_value ) : ?>
                        selected="selected"
                    <?php endif; ?>
                >
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>

        </select>
    </div>
    <div>
        <label for="<?php echo esc_attr( $option_name ); ?>">
            <?php echo $args['desc']; ?>
        </label>
    </div>
</p>
