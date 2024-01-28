<?php

namespace WPPluginFramework\Woo\Fields;

use WPPluginFramework\Logger;

abstract class TextField extends Field
{
    #region Protected Properties

    protected ?string $placeholder = null;

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

    /**
     * Draw the meta box field.
     * @return void
     */
    #[Override]
    protected function drawMetaBox(): void
    {
        woocommerce_wp_text_input([
            'id'            => $this->getId(),
            'label'         => $this->getLabel(),
            'description'   => $this->getDescription(),
            'desc_tip'      => $this->description_as_tip,
            'placeholder'   => $this->placeholder,
            'class'         => $this->class,
            'wrapper_class' => $this->wrapper_class,
            'style'         => $this->style,
        ]);
    }

    #endregion
}
