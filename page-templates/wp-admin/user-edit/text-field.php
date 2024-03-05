<?php
/**
 * Profile Text Field Template
 */

defined('ABSPATH') || exit;

[$context, $value, $user_id] = $field->getContext();

?>

<tr>
    <th>
        <label for="<?php echo($field->getID()); ?>"><?php echo($field->getLabel()); ?></label>
    </th>
    <td>
        <input
            type="text"
            class="regular-text"
            name="<?php echo($field->getID()); ?>"
            id="<?php echo($field->getID()); ?>"
            placeholder="<?php echo($field->getPlaceholder()); ?>"
            value="<?php echo($value); ?>"
            <?php echo($field->getDisabled() ? 'disabled="disabled"' : ''); ?> />
        <p class="description"><?php echo($field->getDescription()); ?></p>
    </td>
</tr>
