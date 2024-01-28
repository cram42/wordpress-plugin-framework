<?php

namespace WPPluginFramework\Woo\Fields;

class SelectField extends Field
{
    #region Protected Properties

    protected array $options = [];
    protected bool $allow_blank = true;

    #endregion
    #region Protected Methods

    /**
     * Get the options, injecting a blank if allowed.
     * @return array
     */
    protected function getOptions(): array
    {
        if ($this->allow_blank) {
            if (!array_key_exists('', $this->options)) {
                $this->options = array_merge(
                    ['' => ''],
                    $this->options
                );
            }
        }
        return $this->options;
    }

    /**
     * Draw the meta box field.
     * @return void
     */
    #[Override]
    protected function drawMetaBox() : void
    {
        woocommerce_wp_select([
            'id'            => $this->getId(),
            'label'         => $this->getLabel(),
            'description'   => $this->getDescription(),
            'desc_tip'      => $this->description_as_tip,
            'options'       => $this->getOptions(),
            'class'         => $this->class,
            'wrapper_class' => $this->wrapper_class,
            'style'         => $this->style,
        ]);
    }

    #endregion
}
