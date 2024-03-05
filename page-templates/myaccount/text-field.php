<?php
/**
 * My Account Text Field Template
 */

defined('ABSPATH') || exit;

[$context, $value, $user_id] = $field->getContext();

?>

<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="<?php echo($field->getID()); ?>"><?php echo($field->getLabel()); ?>
        <?php if ($field->getRequired()) : ?>
        <span class="required"> *</span>
        <?php endif ?>
    </label>
    <input
        type="text"
        class="woocommerce-Input woocommerce-Input--text input-text form-control"
        name="<?php echo($field->getID()); ?>"
        id="<?php echo($field->getID()); ?>"
        placeholder="<?php echo($field->getPlaceholder()); ?>"
        value="<?php echo($value); ?>"
        <?php echo($field->getDisabled() ? 'disabled="disabled"' : ''); ?> />
</p>
