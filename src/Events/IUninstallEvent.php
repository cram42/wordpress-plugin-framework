<?php

namespace WPPluginFramework\Events;

interface IUninstallEvent extends IEvent
{
    public function onUninstallEvent(): void;
}
