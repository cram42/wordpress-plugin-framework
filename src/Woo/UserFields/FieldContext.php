<?php

namespace WPPluginFramework\Woo\UserFields;

enum FieldContext
{
    case UNKNOWN;
    case PROFILE;
    case MY_ACCOUNT;
    case REGISTRATION;
}
