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
     * Draw the meta box field.
     * @param $user_id The user's id.
     * @return void
     */
    #[Override]
    public function drawField($user_id): void
    {
        Logger::debug('drawField()', get_class(), get_called_class());
        $value = get_user_meta($user_id, $this->getID(), true);
        echo('<input 
                type="text" 
                id="' . $this->getID() . '" 
                name="' . $this->getID() . '" 
                class="regular-text" 
                placeholder="' . $this->placeholder . '" 
                value="' . $value . '" 
            ></input>');
        echo('<p class="description">' . $this->getDescription() . '</p>');
    }

    /**
     * Save the data here.
     * @param $user_id The user's id.
     * @return void
     */
    #[Override]
    public function saveField($user_id): void
    {
        Logger::debug('saveField()', get_class(), get_called_class());
        $value = $_POST[$this->getID()];
        $value = $this->sanitizeValue($value);
        update_user_meta($user_id, $this->getID(), $value);
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
