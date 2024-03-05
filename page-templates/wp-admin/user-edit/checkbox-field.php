<?php
/**
 * Registration Form Field Template
 */

defined('ABSPATH') || exit;

[$context, $value, $user_id] = $field->getContext();

?>

<tr>
    <th>
        <label for="<?php echo($field->getID()); ?>"><?php echo($field->getLabel()); ?></label>
    </th>
    <td>
        <label for="<?php echo($field->getID()); ?>">
            <input
                type="checkbox"
                name="<?php echo($field->getID()); ?>"
                id="<?php echo($field->getID()); ?>"
                value="true"
                <?php echo($field->getDisabled() ? 'disabled="disabled"' : ''); ?>
                <?php echo($value ? 'checked' : '') ?> />
            <?php echo($field->getText()); ?>
        </label>
        <p class="description"><?php echo($field->getDescription()); ?></p>
    </td>
</tr>
