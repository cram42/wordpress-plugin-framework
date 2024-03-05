<?php
/**
 * Registration Form Field Template
 */

defined('ABSPATH') || exit;

?>

<tr>
    <th>
        <label for="<?php echo($field->getID()); ?>"><?php echo($field->getLabel()); ?></label>
    </th>
    <td>
        <input
            type="text"
            disabled="disabled"
            class="regular-text"
            name="<?php echo($field->getID()); ?>"
            id="<?php echo($field->getID()); ?>"
            placeholder="<?php echo($field->getID()); ?>" />
        <p class="description"><?php echo($field->getDescription()); ?></p>
    </td>
</tr>
