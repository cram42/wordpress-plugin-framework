<?php

namespace WPPluginFramework\Users\Fields;

use WPPluginFramework\{
    Logger,
    WPFObject,
};
use WPPluginFramework\Events\ILoadEvent;

use function WPPluginFramework\strsuffix;

abstract class ProfileFieldTable extends WPFObject implements ILoadEvent
{
    #region Protected Properties

    protected ?string $id = null;
    protected ?string $title = null;

    #endregion
    #region Public Methods

    /**
     * Get or generate the table ID.
     * @return string
     */
    public function getID(): string
    {
        if (!$this->id) {
            $temp = get_called_class();                                 // My\Namespace\CoolStuffProfileFieldTable
            $temp = preg_replace('/ProfileFieldTable$/', '', $temp);    // My\Namespace\CoolStuff
            $temp = str_replace('\\', '_', $temp);                      // My_Namespace_CoolStuff
            $temp = strtolower($temp);                                  // my_namespace_coolstuff
            $this->id = $temp;
        }
        return $this->id;
    }

    /**
     * Get or generate the table label.
     * @return string
     */
    public function getTitle(): string
    {
        if (!$this->title) {
            $temp = get_called_class();                                     // My\Namespace\CoolStuffProfileFieldTable
            $temp = strsuffix($temp, '\\');                                 // CoolStuffProfileFieldTable
            $temp = preg_replace('/ProfileFieldTable$/', '', $temp);        // CoolStuff
            $temp = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $temp);    // Cool Stuff
            $this->title = $temp;
        }
        return $this->title;
    }

    /**
     * Draw the table.
     * @param $user The user. Provided by WP.
     * @return void
     */
    public function onProfileDisplay($user): void
    {
        Logger::debug('onProfileDisplay()', get_class(), get_called_class());
        echo('<h2>' . $this->getTitle() . '</h2>');
        echo('<table class="form-table wpf-profiletable-'.$this->getID().'" role="presentation">');
        echo('<tbody>');
        do_action('wpf_profiletable_' . $this->getID(), $user->ID);
        echo('</tbody>');
        echo('</table>');
    }

    #endregion
    #region ILoadEvent Implementation
    public function onLoadEvent(): void
    {
        Logger::debug('onLoadEvent()', get_class(), get_called_class());
        add_action('edit_user_profile', [$this, 'onProfileDisplay']);
        add_action('show_user_profile', [$this, 'onProfileDisplay']);
    }
    #endregion
}
