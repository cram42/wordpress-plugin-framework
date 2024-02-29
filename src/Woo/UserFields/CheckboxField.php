<?php

namespace WPPluginFramework\Woo\UserFields;

use WPPluginFramework\Logger;

use function WPPluginFramework\strsuffix;

abstract class CheckboxField extends Field
{
    #region Protected Properties

    protected ?string $text = null;
    protected string $true_value = 'true';
    protected string $false_value = 'false';

    #endregion
    #region Public Methods

    /**
     * Get or generate the checkbox text.
     * @return string
     */
    public function getText(): string
    {
        if (!$this->text) {
            $temp = get_called_class();                                     // My\Namespace\CoolCheckboxField
            $temp = strsuffix($temp, '\\');                                 // CoolCheckboxField
            $temp = preg_replace('/Field$/', '', $temp);                    // CoolCheckbox
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Checkbox
            $this->text = $temp;
        }
        return $this->text;
    }

    /**
     * Draw the field row for profile table.
     * @param $user_id The user's id.
     * @return void
     */
    #[Override]
    public function onProfileTable($user_id): void
    {
        Logger::debug('onProfileTable()', get_class(), get_called_class());
        
        $value = get_user_meta($user_id, $this->getID(), true);
        $checked = $value == $this->true_value;

        echo('<tr>');
        echo('<th><label for="' . $this->getID() . '">' . $this->getLabel() . '</label></th>');
        echo('<td>');
        echo('<label for="' . $this->getID() . '">');
        echo('<input 
                type="checkbox" 
                id="' . $this->getID() . '" 
                name="' . $this->getID() . '" 
                value="true" 
                ' . ($checked ? 'checked' : '') . '
                ' . ($this->disabled ? 'disabled="disabled"' : '') . '
            ></input>');
        echo(' ' . $this->getText());
        echo('</label>');
        if ($this->show_description) {
            echo('<p class="description">' . $this->getDescription() . '</p>');
        }
        echo('</td>');
        echo('</tr>');
    }

    /**
     * Run when profile is updated. Save the data here.
     * @param $user_id The user's id. Provided by WP.
     * @return void
     */
    #[Override]
    public function onProfileUpdate($user_id): void
    {
        $value = $this->false_value;
        if (array_key_exists($this->getID(), $_POST)) {
            $checked = $_POST[$this->getID()];
            $value = ($checked == 'true') ? $this->true_value : $this->false_value;
        }
        update_user_meta($user_id, $this->getID(), $value);
    }

    #endregion
}
