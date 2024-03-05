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
        type="text"
        disabled="disabled"
        class="woocommerce-Input woocommerce-Input--text input-text form-control"
        name="<?php echo($field->getID()); ?>"
        id="<?php echo($field->getID()); ?>"
        placeholder="<?php echo($field->getID()); ?>" />
</p>
