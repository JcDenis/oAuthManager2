<?php
/**
 * @brief oAuthManager2, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'oAuthManager2',
    'Manage OAuth2 services',
    'Jean-Christian Denis and contributors',
    '0.1.0',
    [
        'requires'    => [['core', '2.20']],
        'permissions' => null,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/oAuthManager2',
        'details'     => 'https://github.com/JcDenis/oAuthManager2',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/oAuthManager2/master/dcstore.xml',
    ]
);
