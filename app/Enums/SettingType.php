<?php

namespace App\Enums;

enum SettingType: string
{
    case Integer = 'integer';
    case String = 'string';
    case Boolean = 'boolean';
}
