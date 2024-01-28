<?php

namespace WPPluginFramework\Events;

interface IEnableEvent extends IEvent
{
    public function onEnableEvent(): void;
}
