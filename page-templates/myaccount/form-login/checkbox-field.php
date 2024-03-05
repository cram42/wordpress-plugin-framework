<?php
/**
 * Registration Form Field Template
 */

defined('ABSPATH') || exit;

?>

<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="<?php echo($field->getID()); ?>"><?php echo($field->getLabel()); ?>
        <?php if ($field->getRequired()) : ?>
        <span class="required"> *</span>
        <?php endif ?>
    </label>
    <input
        type="checkbox"
        class="woocommerce-Input woocommerce-Input--checkbox input-checkbox"
        name="<?php echo($field->getID()); ?>"
        id="<?php echo($field->getID()); ?>"
        value="true"
        <?php echo($field->getDisabled() ? 'disabled="disabled"' : ''); ?>
        />
    <span><?php echo($field->getText()); ?></span>
</p>
