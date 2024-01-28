<?php

namespace WPPluginFramework\Events;

interface ILoadEvent extends IEvent
{
    public function onLoadEvent(): void;
}
