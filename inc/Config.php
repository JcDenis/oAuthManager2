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
declare(strict_types=1);

namespace plugins\oAuthManager2;

class Config
{
    public static function getPluginId(): string
    {
        return 'oAuthManager2';
    }

    public static function getPluginName(): string
    {
        return __('OAuth2 manager');
    }

    public static function getDotclearMin(): string
    {
        return '2.20';
    }

    public static function getRedirectUri(): string
    {
        return defined('OAUTH2_REDIRECT_URI') ? OAUTH2_REDIRECT_URI : DC_ADMIN_URL;
    }
}
