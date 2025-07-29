<?php

/**
 * Helpers para generación de campos de formulario
 * 
 * @package VolunteerForm
 */

namespace VolunteerForm;

class Fields
{
    /**
     * Campo de texto genérico
     */
    public static function text($name, $label, $value = '', $required = false, $type = 'text')
    {
?>
        <div class="mb-3">
            <label for="<?php echo esc_attr($name); ?>" class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <input type="<?php echo esc_attr($type); ?>"
                class="form-control"
                id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                value="<?php echo esc_attr($value); ?>"
                <?php if ($required) echo 'required'; ?>>
        </div>
    <?php
    }

    /**
     * Campo de email (extensión de text)
     */
    public static function email($name, $label, $value = '', $required = false)
    {
        self::text($name, $label, $value, $required, 'email');
    }

    /**
     * Campo de teléfono (extensión de text)
     */
    public static function tel($name, $label, $value = '', $required = false)
    {
        self::text($name, $label, $value, $required, 'tel');
    }

    /**
     * Campo de fecha
     */
    public static function date($name, $label, $value = '', $required = false)
    {
    ?>
        <div class="mb-3">
            <label for="<?php echo esc_attr($name); ?>" class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <input type="date"
                class="form-control"
                id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                value="<?php echo esc_attr($value); ?>"
                <?php if ($required) echo 'required'; ?>>
        </div>
    <?php
    }

    /**
     * Campo de textarea
     */
    public static function textarea($name, $label, $value = '', $required = false, $rows = 3)
    {
    ?>
        <div class="mb-3">
            <label for="<?php echo esc_attr($name); ?>" class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <textarea class="form-control"
                id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                rows="<?php echo absint($rows); ?>"
                <?php if ($required) echo 'required'; ?>><?php
                                                            echo esc_textarea($value);
                                                            ?></textarea>
        </div>
    <?php
    }

    /**
     * Campo de select dropdown
     */
    public static function select($name, $label, $options, $selected = '', $required = false)
    {
    ?>
        <div class="mb-3">
            <label for="<?php echo esc_attr($name); ?>" class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <select class="form-select" id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                <?php if ($required) echo 'required'; ?>>
                <option value="">Seleccionar</option>
                <?php foreach ($options as $value => $text): ?>
                    <option value="<?php echo esc_attr($value); ?>"
                        <?php selected($value, $selected); ?>>
                        <?php echo esc_html($text); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php
    }

    /**
     * Grupo de radio buttons
     */
    public static function radio($name, $label, $options, $selected = '', $required = false)
    {
    ?>
        <div class="mb-3">
            <label class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <?php foreach ($options as $value => $text): ?>
                <div class="form-check">
                    <input class="form-check-input"
                        type="radio"
                        name="<?php echo esc_attr($name); ?>"
                        id="<?php echo esc_attr($name . '_' . $value); ?>"
                        value="<?php echo esc_attr($value); ?>"
                        <?php checked($value, $selected); ?>
                        <?php if ($required) echo 'required'; ?>>
                    <label class="form-check-label" for="<?php echo esc_attr($name . '_' . $value); ?>">
                        <?php echo esc_html($text); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php
    }

    /**
     * Grupo de checkboxes
     */
    public static function checkbox_group($name, $label, $options, $selected = array(), $required = false, $extra_classes = '')
    {
    ?>
        <div class="mb-3">
            <label class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <div class="row">
                <?php foreach ($options as $value => $text): ?>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input <?php echo esc_attr($extra_classes); ?>"
                                type="checkbox"
                                name="<?php echo esc_attr($name); ?>[]"
                                id="<?php echo esc_attr($name . '_' . sanitize_title($value)); ?>"
                                value="<?php echo esc_attr($value); ?>"
                                <?php if (is_array($selected) && in_array($value, $selected)) echo 'checked'; ?>>
                            <label class="form-check-label" for="<?php echo esc_attr($name . '_' . sanitize_title($value)); ?>">
                                <?php echo esc_html($text); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }

    /**
     * Campo de subida de archivo
     */
    public static function file($name, $label, $required = false, $accept = '', $help_text = '')
    {
    ?>
        <div class="mb-3">
            <label for="<?php echo esc_attr($name); ?>" class="form-label">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <input type="file"
                class="form-control"
                id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                <?php if ($accept) echo 'accept="' . esc_attr($accept) . '"'; ?>
                <?php if ($required) echo 'required'; ?>>
            <?php if ($help_text): ?>
                <div class="form-text"><?php echo esc_html($help_text); ?></div>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Checkbox simple
     */
    public static function checkbox($name, $label, $checked = false, $required = false)
    {
    ?>
        <div class="form-check mb-3">
            <input type="checkbox"
                class="form-check-input"
                id="<?php echo esc_attr($name); ?>"
                name="<?php echo esc_attr($name); ?>"
                <?php checked($checked); ?>
                <?php if ($required) echo 'required'; ?>>
            <label class="form-check-label" for="<?php echo esc_attr($name); ?>">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
        </div>
<?php
    }
}
