<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static TeamSpeakServerAvailable()
 * @method static static TeamSpeakServerName()
 * @method static static TeamSpeakServerPassword()
 */
final class Setting extends Enum
{
    const TeamSpeakServerAvailable = 'teamspeak_server_available';
    const TeamSpeakServerName = 'teamspeak_server_name';
    const TeamSpeakServerPassword = 'teamspeak_server_password';
}
