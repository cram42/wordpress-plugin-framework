<?php

namespace WPPluginFramework\Events;

interface IDisableEvent extends IEvent
{
    public function onDisableEvent(): void;
}
