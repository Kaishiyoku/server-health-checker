<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static String()
 * @method static static Bool()
 */
final class SettingType extends Enum
{
    const String = 'string';
    const Bool = 'bool';
}
