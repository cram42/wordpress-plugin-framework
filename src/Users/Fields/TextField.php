<?php

namespace WPPluginFramework\Users\Fields;

use WPPluginFramework\{
    Logger, LogLevel,
};

Logger::setLevel(__NAMESPACE__ . '\TextField', LogLevel::DEBUG);

abstract class TextField extends Field
{
    #region Protected Properties

    protected ?string $placeholder = null;

    #endregion
    #region Public Methods

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

        echo('<tr>');
        echo('<th><label for="' . $this->getID() . '">' . $this->getLabel() . '</label></th>');
        echo('<td>');
        echo('<input 
                type="text" 
                id="' . $this->getID() . '" 
                name="' . $this->getID() . '" 
                class="regular-text" 
                placeholder="' . $this->placeholder . '" 
                value="' . $value . '" 
                ' . ($this->disabled ? 'disabled="disabled"' : '') . '
            ></input>');
        if ($this->show_description) {
            echo('<p class="description">' . $this->getDescription() . '</p>');
        }
        echo('</td>');
        echo('</tr>');
    }

    #endregion
    #region Protected Methods

    /**
     * Sanitize value for storage.
     * @return mixed
     */
    #[Override]
    protected function sanitizeValue(mixed $value): mixed
    {
        return esc_attr($value);
    }

    #endregion
}
