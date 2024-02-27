<?php

namespace WPPluginFramework\Users\Fields;

use WPPluginFramework\{
    Logger, LogLevel,
    WPFParentObject,
};
use WPPluginFramework\Events\ILoadEvent;

use function WPPluginFramework\strsuffix;

Logger::setLevel(__NAMESPACE__ . '\FieldTable', LogLevel::DEBUG);

abstract class FieldTable extends WPFParentObject implements ILoadEvent
{
    #region Protected Properties

    protected ?string $title = null;

    /**
     * Array of fully-qualified class names of Field to load on startup.
     * @var array<string>
     */
    protected array $fields = [];

    #endregion
    #region Constructor

    public function __construct()
    {
        parent::__construct();
        $this->loadFields();
    }

    #endregion
    #region Public Methods

    /**
     * Get or generate the field label.
     * @return string
     */
    public function getTitle(): string
    {
        if (!$this->title) {
            $temp = get_called_class();                                     // My\Namespace\CoolStuffFieldTable
            $temp = strsuffix($temp, '\\');                                 // CoolStuffFieldTable
            $temp = preg_replace('/FieldTable$/', '', $temp);               // CoolStuff
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Stuff
            $this->title = $temp;
        }
        return $this->title;
    }

    /**
     * Draw the table with fields.
     * @param $user The user. Provided by WP.
     * @return void
     */
    public function onProfileDisplay($user): void
    {
        Logger::debug('onProfileDisplay()', get_class(), get_called_class());
        echo('<h2>' . $this->getTitle() . '</h2>');
        echo('<table class="form-table" role="presentation">');
        echo('<tbody>');

        foreach ($this->getWPFChildren() as $field) {

            if (!is_subclass_of($field, __NAMESPACE__ . '\Field')) {
                Logger::warning(
                    sprintf('Child class "%s" is not a subclass of Field', get_class($field)),
                    get_class(),
                    get_called_class()
                );
                continue;
            }

            echo('<tr>');
            echo('<th><label for="' . $field->getID() . '">' . $field->getLabel() . '</label></th>');
            echo('<td>');
            $field->drawField($user->ID);
            echo('</td>');
            echo('</tr>');

        }

        echo('</tbody>');
        echo('</table>');
    }

    /**
     * Run when profile is updated. Save the data here.
     * @param $user_id The user's id. Provided by WP.
     * @return void
     */
    public function onProfileUpdate($user_id): void
    {
        Logger::debug('onProfileUpdate()', get_class(), get_called_class());
        foreach ($this->getWPFChildren() as $field) {

            if (!is_subclass_of($field, __NAMESPACE__ . '\Field')) {
                Logger::warning(
                    sprintf('Child class "%s" is not a subclass of Field', get_class($field)),
                    get_class(),
                    get_called_class()
                );
                continue;
            }

            $field->saveField($user_id);
        }
    }

    #endregion
    #region Private Methods

    /**
     * Load resource classes by name into WPF.
     * @return void
     */
    private function loadFields(): void
    {
        foreach ($this->fields as $field) {
            // Ensure child class exists
            if (!class_exists($field, true)) {
                $error = sprintf('Field class does not exist: "%s"', $field);
                Logger::error($error, get_class(), get_called_class());
                wp_die(Logger::error($error, get_class(), get_called_class()));
            }

            // Get running instance and add to WPF
            $instance = $field::getInstance();
            if (is_subclass_of($instance, __NAMESPACE__ . '\Field')) {
                $this->addWPFChild($instance);
            } else {
                Logger::warning(
                    sprintf('Child class "%s" is not a subclass of Field', get_class($instance)),
                    get_class(),
                    get_called_class()
                );
            }
        }
    }

    #endregion
    #region ILoadEvent Implementation
    public function onLoadEvent(): void
    {
        Logger::debug('onLoadEvent()', get_class(), get_called_class());
        add_action('edit_user_profile', [$this, 'onProfileDisplay']);
        add_action('show_user_profile', [$this, 'onProfileDisplay']);
        add_action('personal_options_update', [$this, 'onProfileUpdate']);
        add_action('edit_user_profile_update', [$this, 'onProfileUpdate']);
    }
    #endregion
}
