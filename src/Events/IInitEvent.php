<?php

namespace WPPluginFramework\Events;

interface IInitEvent extends IEvent
{
    public function onInitEvent(): void;
}
