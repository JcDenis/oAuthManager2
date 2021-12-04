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

namespace OAuth2\Provider;

use OAuth2\Provider;

class Instagram extends Provider
{
    // todo: move this to Client
    protected $scope = ['user_profile', 'user_media'];

    public static function getId(): string
    {
        return 'instagram';
    }

    public static function getName(): string
    {
        return 'Instagram';
    }

    public static function getDescription(): string
    {
        return 'Interact with your Instagram.';
    }

    // https://developers.facebook.com/docs/instagram-basic-display-api/guides/getting-access-tokens-and-permissions
    public static function getConsoleUrl(): string
    {
        return 'https://developers.facebook.com/apps';
    }

    public function getAuthorizeUri(): string
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    public function getAccessTokenUri(): string
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    public function getRequestUri(): string
    {
        return 'https://graph.instagram.com/';
    }
}
