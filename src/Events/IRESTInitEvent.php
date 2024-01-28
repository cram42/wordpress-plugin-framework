<?php

namespace WPPluginFramework\Events;

interface IRESTInitEvent extends IEvent
{
    public function onRESTInitEvent(): void;
}
