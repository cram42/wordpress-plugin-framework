<?php

namespace WPPluginFramework;

define('WPF_INTERFACE_EVENT',           __NAMESPACE__ . '\Events\IEvent');

define('WPF_INTERFACE_ENABLE_EVENT',    __NAMESPACE__ . '\Events\IEnableEvent');
define('WPF_INTERFACE_DISABLE_EVENT',   __NAMESPACE__ . '\Events\IDisableEvent');
define('WPF_INTERFACE_UNINSTALL_EVENT', __NAMESPACE__ . '\Events\IUninstallEvent');

define('WPF_INTERFACE_LOAD_EVENT',      __NAMESPACE__ . '\Events\ILoadEvent');
define('WPF_INTERFACE_REST_EVENT',      __NAMESPACE__ . '\Events\IRESTInitEvent');

define('WPF_REQUIRES_PREFIX', 'wpf_requires_');